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
    Route::get('/datasets/{datasetId}', [DataController::class, 'getDatasetData']);
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

Route::middleware('api')->group(function () {
    // Route pour un point spécifique (Appel NOAA + Cache)
    Route::get('/datasets/{datasetId}', [DataController::class, 'getDatasetData']);

    // NOUVELLE ROUTE : Pour récupérer tous les points stockés en BDD
    Route::get('/map-points', [DataController::class, 'getAllStoredPoints']);
});