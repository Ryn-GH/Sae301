<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataController;
use App\Enums\ZoneMaritime;
//use App\Models\PointMesure;

Route::middleware('api')->group(function () {
    // Route principale pour récupérer les données des datasets
    // Utilise l'ID du dataset (SST ou Salinité) comme paramètre.
    // Exemple : /api/datasets/noaacwSMAPSSSDaily?time=...&latMin=...
    // Appel NOAA + Cache
    Route::get('/datasets/{datasetId}', [DataController::class, 'getDatasetData']);
    
    // Route pour récupérer tous les points stockés en BDD (pour la carte)
    Route::get('/map-points', [DataController::class, 'getAllStoredPoints']);

    Route::get('/stats', [DataController::class, 'getStats']);
    Route::get('/zones', function () {
    // On transforme l'Enum en une liste utilisable par le FrontEnd
    $zones = collect(ZoneMaritime::cases())->map(fn($zone) => [
        'name' => $zone->value,
        'slug' => $zone->slug(),
        'bbox' => $zone->boundingBox(),
    ]);
    return response()->json($zones)->header('Access-Control-Allow-Origin', '*');
    });
});