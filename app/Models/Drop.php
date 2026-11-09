<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drop extends Model
{
    protected $fillable = [
        'slug', 'title', 'description', 'price_cents', 'image_path',
        'opens_at', 'closes_at', 'reservation_minutes',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'sold_out_at' => 'datetime',
        ];
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(DropSize::class)->orderBy('id');
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    /** upcoming | live | soldout | closed */
    public function status(?CarbonInterface $now = null): string
    {
        $now ??= now();

        if ($now->lt($this->opens_at)) {
            return 'upcoming';
        }

        if ($now->gte($this->closes_at)) {
            return 'closed';
        }

        return $this->available() === 0 ? 'soldout' : 'live';
    }

    public function stockTotal(): int
    {
        return (int) $this->sizes->sum('stock');
    }

    public function available(): int
    {
        return (int) $this->sizes->sum(fn (DropSize $size) => $size->available());
    }

    public function soldCount(): int
    {
        return (int) $this->sizes->sum('sold');
    }

    /** Durée entre l'ouverture et l'épuisement, au format « 3 min 12 s ». */
    public function soldOutDuration(): ?string
    {
        if ($this->sold_out_at === null) {
            return null;
        }

        $s = (int) $this->opens_at->diffInSeconds($this->sold_out_at);

        return intdiv($s, 60) > 0
            ? intdiv($s, 60).' min '.($s % 60).' s'
            : ($s % 60).' s';
    }
}
