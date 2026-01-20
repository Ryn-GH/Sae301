<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PointMesure;
use App\Models\Salinite;
use App\Models\Temperature;
use App\Enums\ZoneMaritime;
use Carbon\Carbon;

class DataController extends Controller
{
    protected $allowedDatasets = [
        'noaacwBLENDEDsstDNDaily' => [
            'variable' => 'analysed_sst', 
            'model' => 'temperature',
            'bdd_field' => 'temperature',
            'unit' => 'degree_C',
        ],
        'noaacwSMOSsssDaily' => [
            'variable' => 'sss',
            'model' => 'salinite',
            'bdd_field' => 'sss',
            'unit' => 'PSU',
        ],
    ];

    /**
     * Pour la CARTE : Récupère tous les points avec leurs mesures
     */
    public function getAllStoredPoints()
    {
        try {
            // Test simple pour vérifier si la BDD répond
            \DB::connection()->getPdo();

            // Eager loading pour éviter le problème N+1
            $points = PointMesure::with(['temperature', 'salinite'])->get();

            return response()->json($points);
        } catch (\Throwable $e) {
            // On renvoie l'erreur exacte pour le débogage
            return response()->json(['error' => 'Database Error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Pour NOAA / CACHE : Récupère ou télécharge une donnée précise
     */
    public function getDatasetData(Request $request, string $datasetId)
    {
        if (!isset($this->allowedDatasets[$datasetId])) {
            return response()->json(['error' => 'Dataset non supporté.'], 404);
        }

        $config = $this->allowedDatasets[$datasetId];
        $lat = round((float)$request->get('latMin', 45.0), 2);
        $lon = round((float)$request->get('lonMin', 0.0), 2);
        $time = $request->get('time', now()->subDays(2)->format('Y-m-d\TH:i:s\Z'));

        // 1. Vérifier le cache
        $cached = $this->getFromCache($datasetId, $lat, $lon, $time);
        if ($cached) return response()->json($cached);

        // 2. Appel NOAA (Simplifié pour l'exemple)
        $url = "https://coastwatch.noaa.gov/erddap/griddap/{$datasetId}.json?{$config['variable']}[({$time}):1:({$time})][({$lat}):1:({$lat})][({$lon}):1:({$lon})]";
        
        try {
            $response = Http::timeout(30)->withOptions(['verify' => false])->get($url);
            $response->throw();
            $resData = $response->json();
            
            $rows = $resData['table']['rows'];
            $valeur = $rows ? $rows[0][array_search($config['variable'], $resData['table']['columnNames'])] : null;

            // 3. Sauvegarde
            $status = $this->saveToMysql($datasetId, $lat, $lon, $time, $valeur);

            return response()->json([
                'source' => 'NOAA ERDDAP',
                'data' => ['valeur' => $valeur, 'date' => $time, 'latitude' => $lat, 'longitude' => $lon],
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function getFromCache($datasetId, $lat, $lon, $time)
    {
        $config = $this->allowedDatasets[$datasetId];
        $searchTime = Carbon::parse($time)->toDateString();
        $relation = $config['model'];

        $point = PointMesure::where('latitude', $lat)
                            ->where('longitude', $lon)
                            ->whereDate('dateMesure', $searchTime)
                            ->first();

        if ($point && $point->$relation) {
            return [
                'source' => 'MySQL Cache',
                'data' => [
                    'valeur' => $point->$relation->{$config['bdd_field']},
                    'date' => $point->dateMesure,
                    'latitude' => $point->latitude,
                    'longitude' => $point->longitude,
                ],
                'status' => 'Cache Hit'
            ];
        }
        return null;
    }

    protected function saveToMysql($datasetId, $lat, $lon, $time, $value)
    {
        $config = $this->allowedDatasets[$datasetId];
        $point = PointMesure::firstOrCreate([
            'latitude' => $lat,
            'longitude' => $lon,
            'dateMesure' => Carbon::parse($time)->toDateString(),
        ]);

        if ($config['model'] === 'temperature') {
            Temperature::updateOrCreate(['Point_id' => $point->PM_id], ['temperature' => $value]);
        } else {
            Salinite::updateOrCreate(['PM_id' => $point->PM_id], ['sss' => $value]);
        }
        return "Mise en cache réussie";
    }

    /**
     * Pour les GRAPHIQUES : getStats
     */
    public function getStats(Request $request)
    {
        // ... Gardez votre code getStats ici, il fonctionne très bien pour les mocks
        return response()->json(['message' => 'Stats endpoint active']); 
    }
}