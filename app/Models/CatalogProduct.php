<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProduct extends Model
{
    protected $fillable = ['name', 'description', 'price_cents', 'sizes', 'image_path', 'drawn_at', 'times_drawn'];

    protected function casts(): array
    {
        return [
            'sizes' => 'array',
            'drawn_at' => 'datetime',
        ];
    }
}
