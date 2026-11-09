<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drop extends Model
{
    protected $fillable = [
        'catalog_product_id', 'slug', 'title', 'description', 'price_cents', 'image_path',
        'opens_at', 'closes_at', 'reservation_minutes',
    ];

    /** Photos du catalogue : public/catalog/... ; photos envoyées depuis l'admin : storage public. */
    public function imageUrl(): ?string
    {
        if ($this->image_path === null) {
            return null;
        }

        return str_starts_with($this->image_path, 'catalog/')
            ? asset($this->image_path)
            : asset('storage/'.$this->image_path);
    }

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
