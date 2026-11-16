<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    public const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    protected $table = 'programmes';

    protected $fillable = [
        'day',
        'start',
        'end',
        'title',
        'description',
    ];
}