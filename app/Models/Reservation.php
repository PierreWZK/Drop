<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['token', 'drop_id', 'drop_size_id', 'email', 'status', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(DropSize::class, 'drop_size_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'reserved' && $this->expires_at->isFuture();
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
