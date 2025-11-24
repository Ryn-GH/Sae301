<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataController;
//use App\Models\PointMesure;

Route::middleware('api')->group(function () {
    // Route principale pour récupérer les données des datasets
    // Utilise l'ID du dataset (SST ou Salinité) comme paramètre.
    // Exemple : /api/datasets/noaacwSMAPSSSDaily?time=...&latMin=...
    Route::get('/datasets/{datasetId}', [DataController::class, 'getDatasetData']);
});
?>