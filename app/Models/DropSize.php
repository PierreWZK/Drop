<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DropSize extends Model
{
    protected $fillable = ['drop_id', 'label', 'stock'];

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }

    public function available(): int
    {
        return max(0, $this->stock - $this->reserved - $this->sold);
    }
}
