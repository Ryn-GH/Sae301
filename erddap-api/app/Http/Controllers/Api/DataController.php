<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Models\PointMesure;
use App\Models\Salinite;
use App\Models\Temperature;
use Carbon\Carbon;
use App\Enums\ZoneMaritime;

class DataController extends Controller
{
    /**
     * Liste des datasets autorisés et configuration des variables.
     */
    protected $allowedDatasets = [
        'noaacwBLENDEDsstDNDaily' => [
            'service' => 'griddap', 
            'variable' => 'analysed_sst', 
            'dimensions' => ['time', 'latitude', 'longitude'],
            'model' => 'temperature',
            'bdd_field' => 'temperature',
            'unit' => 'degree_C',
        ],
        'noaacwSMOSsssDaily' => [
            'service' => 'griddap', 
            'variable' => 'sss',
            'dimensions' => ['time', 'altitude', 'latitude', 'longitude'],
            'model' => 'salinite',
            'bdd_field' => 'sss',
            'unit' => 'PSU',
        ],
    ];

    /**
     * Tente de récupérer les données depuis la base de données (cache).
     * @param string $time La date d'observation demandée par l'utilisateur (ou par défaut)
     * @return array|null 
     */
    protected function getFromCache(string $datasetId, float $lat, float $lon, string $time)
    {
        $datasetConfig = $this->allowedDatasets[$datasetId];
        $roundedLat = round($lat, 2);
        $roundedLon = round($lon, 2);
        $searchTime = Carbon::parse($time)->startOfDay()->toDateString();
        $relationName = $datasetConfig['model'];

        // --- TENTATIVE 1 : Recherche d'une correspondance EXACTE (Date + Coordonnées) ---
        $pointMesure = PointMesure::where('latitude', $roundedLat)
                                    ->where('longitude', $roundedLon)
                                    ->whereDate('dateMesure', $searchTime)
                                    ->first();

        if ($pointMesure) {
            $measure = $pointMesure->$relationName()->first();
            if ($measure) {
                return [
                    'source' => 'MySQL Cache',
                    'status' => 'Cache Hit (Exact Match)',
                    'time' => $pointMesure->dateMesure,
                    'latitude' => $pointMesure->latitude,
                    'longitude' => $pointMesure->longitude,
                    'variable_name' => $datasetConfig['variable'],
                    'value' => $measure->{$datasetConfig['bdd_field']},
                    'unit' => $datasetConfig['unit'],
                ];
            }
        }
        
        // Si l'utilisateur n'a PAS spécifié de date dans l'URL (il utilise la date par défaut) :
        // --- TENTATIVE 2 : Recherche de la mesure la PLUS RÉCENTE pour ces coordonnées ---
        // On considère qu'une date est spécifiée si elle est dans l'URL. Si elle ne l'est pas, 
        // l'utilisateur veut la dernière donnée disponible.
        $isTimeExplicitlyRequested = request()->has('time');

        if (!$isTimeExplicitlyRequested) {
            $pointMesure = PointMesure::where('latitude', $roundedLat)
                                    ->where('longitude', $roundedLon)
                                    ->latest('dateMesure') // Le plus récent d'abord
                                    ->first();

            if ($pointMesure) {
                $measure = $pointMesure->$relationName()->first(); 
                if ($measure) {
                    return [
                        'source' => 'MySQL Cache',
                        'status' => 'Cache Hit (Latest Available)',
                        'time' => $pointMesure->dateMesure,
                        'latitude' => $pointMesure->latitude,
                        'longitude' => $pointMesure->longitude,
                        'variable_name' => $datasetConfig['variable'],
                        'value' => $measure->{$datasetConfig['bdd_field']},
                        'unit' => $datasetConfig['unit'],
                    ];
                }
            }
        }
        
        return null; // Cache Miss total
    }

    /**
     * Enregistre le point et la mesure dans les tables MySQL.
     * Le reste de cette méthode reste inchangé.
     */
    protected function saveToMysql(string $datasetId, float $lat, float $lon, string $observationTime, ?float $value)
    {
        $datasetConfig = $this->allowedDatasets[$datasetId];
        // On enregistre la date de l'observation tronquée à la journée
        $carbonTime = Carbon::parse($observationTime)->startOfDay()->toDateString();
        $roundedLat = round($lat, 2);
        $roundedLon = round($lon, 2);

        try {
            // 1. Enregistrer ou trouver le PointMesure
            $pointMesure = PointMesure::firstOrCreate(
                [
                    'latitude' => $roundedLat,
                    'longitude' => $roundedLon,
                    'dateMesure' => $carbonTime, // Utilisation de la date tronquée
                ]
            );

            // 2. Enregistrer la mesure spécifique (Température ou Salinité)
            $relationName = $datasetConfig['model'];
            $bddField = $datasetConfig['bdd_field'];

            $finalValue = is_numeric($value) ? (float)$value : null;

            if ($relationName === 'temperature') {
                Temperature::updateOrCreate(
                    ['Point_id' => $pointMesure->PM_id], 
                    ['temperature' => $finalValue]
                );
            } elseif ($relationName === 'salinite') {
                Salinite::updateOrCreate(
                    ['PM_id' => $pointMesure->PM_id], 
                    ['sss' => $finalValue, 'sss_dif' => null] 
                );
            }
            
            return "Cache Miss -> Succès : Données mises en cache (PointMesure #{$pointMesure->PM_id}, {$relationName}).";

        } catch (\Exception $e) {
            logger()->error("MySQL Save Error: " . $e->getMessage()); 
            return "Cache Miss -> Échec d'enregistrement MySQL : " . $e->getMessage();
        }
    }


    public function getDatasetData(Request $request, string $datasetId)
    {
        // 1. Validation et Préparation des filtres
        if (!isset($this->allowedDatasets[$datasetId])) {
            return response()->json(['error' => 'Dataset ID non valide ou non supporté.'], 404);
        }

        $datasetConfig = $this->allowedDatasets[$datasetId];
        $erddapBaseUrl = 'https://coastwatch.noaa.gov/erddap/';
        
        // Nouvelle date par défaut plus sécurisée (2 jours en arrière pour éviter les 404)
        $defaultTime = now()->subDays(2)->startOfDay()->format('Y-m-d\TH:i:s\Z');
        
        // On utilise la date fournie par l'utilisateur ou la date par défaut
        $time = $request->get('time', $defaultTime); 
        
        // Coordonnées
        $lat = (float)$request->get('latMin', '45.0'); 
        $lon = (float)$request->get('lonMin', '0.0');
        
        $format = '.json'; 
        
        // --- 2. TENTATIVE DE RÉCUPÉRATION DEPUIS LE CACHE (MySQL) ---
        // Le cache essaie d'abord l'exactitude, puis le plus récent si l'utilisateur n'a pas spécifié de date.
        $cachedData = $this->getFromCache($datasetId, $lat, $lon, $time);
        
        if ($cachedData) {
            return response()->json([
                'source' => $cachedData['source'],
                'data' => [
                    'message' => 'Données récupérées du cache MySQL.',
                    'variable' => $cachedData['variable_name'],
                    'valeur' => $cachedData['value'],
                    'unite' => $cachedData['unit'],
                    'date' => $cachedData['time'],
                    'latitude' => $cachedData['latitude'],
                    'longitude' => $cachedData['longitude'],
                ],
                'status' => $cachedData['status']
            ]);
        }

        // --- 3. CACHE MISS : APPEL À L'API NOAA ERDDAP ---
        // ... (Le reste du code reste inchangé car il construit l'URL pour NOAA)

        // Construction de la Requête GRiDDAP
        if ($datasetConfig['service'] === 'griddap') {
            $queryParts = [];
            foreach ($datasetConfig['dimensions'] as $dim) {
                $value = match ($dim) {
                    'time' => $time,
                    'altitude' => '0.0', 
                    'latitude' => $lat,
                    'longitude' => $lon,
                    default => null
                };
                if ($value !== null) {
                    $queryParts[] = '[(' . $value . '):1:(' . $value . ')]';
                }
            }

            $query = $datasetConfig['variable'] . implode('', $queryParts);
            $url = $erddapBaseUrl . 'griddap/' . $datasetId . $format . '?' . $query;
            
        } else {
             return response()->json(['error' => 'Service de dataset non supporté.'], 500);
        }

        // 4. Appel à l'API NOAA ERDDAP
        try {
            $response = Http::timeout(45)
                            ->withOptions(['verify' => false])
                            ->get($url);
            
            $response->throw(); 
            $responseData = $response->json(); 
            
            // Extraction de la valeur et du temps
            $tableData = $responseData['table'];
            $columnNames = $tableData['columnNames'];
            $rows = $tableData['rows'];
            
            $variableName = $datasetConfig['variable'];
            $valueIndex = array_search($variableName, $columnNames);
            $timeIndex = array_search('time', $columnNames);
            
            $value = ($rows && $valueIndex !== false) ? $rows[0][$valueIndex] : null;
            $observationTime = ($rows && $timeIndex !== false) ? $rows[0][$timeIndex] : $time;

            // 5. Enregistrement dans MySQL (Cache Write)
            $mysqlStatus = $this->saveToMysql(
                $datasetId, 
                $lat, 
                $lon, 
                $observationTime,
                $value
            );

            // 6. Retourner les données
            return response()->json([
                'source' => 'NOAA ERDDAP (' . $datasetId . ')',
                'data' => [
                    'message' => 'Données récupérées en ligne et mises en cache.',
                    'variable' => $variableName,
                    'valeur' => $value,
                    'unite' => $datasetConfig['unit'],
                    'date' => $observationTime, 
                    'latitude' => $lat,
                    'longitude' => $lon,
                ],
                'status' => $mysqlStatus, 
            ]);

        } catch (RequestException $e) {
            // Le 404 est géré ici.
            return response()->json([
                'error' => 'Erreur lors de la récupération des données de NOAA (Cache Miss).',
                'details' => $e->getMessage(),
                'status' => $e->response?->status(),
                'erddap_query' => $url
            ], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur inattendue.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère les statistiques pour les graphiques.
     */
    public function getStats(Request $request)
    {
        $zoneSlug = $request->query('zone');
        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');

        // 1. Validation des paramètres
        if (!$zoneSlug || !$dateDebut || !$dateFin) {
            return response()->json(['error' => 'Paramètres manquants'], 400)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // 2. Retrouver la zone via l'Enum
        $zone = null;
        foreach (ZoneMaritime::cases() as $z) {
            if ($z->slug() === $zoneSlug) {
                $zone = $z;
                break;
            }
        }

        if (!$zone) {
            return response()->json(['error' => 'Zone introuvable'], 404)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // 3. Génération de données (MOCK) pour tester l'affichage
        // TODO: Remplacer ceci par l'appel réel à ERDDAP ou MySQL
        $dates = [];
        $temperature = [];
        $salinite = [];
        $chlorophylle = [];

        try {
            $start = new \DateTime($dateDebut);
            $end = new \DateTime($dateFin);
            
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                $temperature[] = 15 + (sin($start->getTimestamp() / 100000) * 5) + (rand(-10, 10) / 10);
                $salinite[] = 35 + (rand(-5, 5) / 10);
                $chlorophylle[] = 2 + (rand(-10, 10) / 10);
                $start->modify('+1 day');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur de date'], 400);
        }

        return response()->json([
            'zone' => $zone->value,
            'bbox' => $zone->boundingBox(),
            'dates' => $dates,
            'temperature' => $temperature,
            'salinite' => $salinite,
            'chlorophylle' => $chlorophylle
        ])->header('Access-Control-Allow-Origin', '*');
    }

    public function getAllStoredPoints()
    {
        try {
            // On récupère tous les points avec leurs mesures associées
            $points = PointMesure::with(['temperature', 'salinite'])->get();

            return response()->json([
                'status' => 'success',
                'count' => $points->count(),
                'points' => $points
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}