<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class Vehicule
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'price',
        'type',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
        ];
    }
}
