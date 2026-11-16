<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boite extends Model
{
    protected $fillable = [
        'subject',
        'message',
        'name',
        'contact',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}