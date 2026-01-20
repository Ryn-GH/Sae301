<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointMesure extends Model
{
    use HasFactory;

    // Sur Linux/Render, la casse est importante. Utilisez le nom EXACT de la table TiDB.
    protected $table = 'point_mesures'; 
    protected $primaryKey = 'PM_id';
    public $timestamps = false; // Pas de created_at/updated_at

    protected $fillable = [
        'latitude',
        'longitude',
        'dateMesure',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'dateMesure' => 'date',
    ];

    public function salinite()
    {
        // Assurez-vous que la clé de liaison est correcte (PM_id)
        return $this->hasOne(Salinite::class, 'PM_id');
    }

    public function temperature()
    {
        // Assurez-vous que la clé de liaison est correcte (Point_id ou PM_id)
        // D'après votre schéma, c'est 'Point_id' dans la table 'temperature'.
        return $this->hasOne(Temperature::class, 'Point_id');
    }
}