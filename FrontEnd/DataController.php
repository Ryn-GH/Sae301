<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\ZoneMaritime;

class DataController extends Controller
{
    /**
     * Récupère les données brutes d'un dataset (placeholder).
     */
    public function getDatasetData($datasetId)
    {
        return response()->json([
            'message' => "Données pour le dataset $datasetId",
        ])->header('Access-Control-Allow-Origin', '*');
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
        // TODO: Remplacer ceci par l'appel réel à ERDDAP
        $dates = [];
        $temperature = [];
        $salinite = [];
        $chlorophylle = [];

        try {
            $start = new \DateTime($dateDebut);
            $end = new \DateTime($dateFin);
            
            while ($start <= $end) {
                $dates[] = $start->format('Y-m-d');
                // Génère des valeurs aléatoires réalistes
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
}