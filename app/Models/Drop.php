<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        // Un drop tiré avant l'ajout des photos reprend la photo du catalogue.
        $path = $this->image_path ?? $this->catalogProduct?->image_path;

        if ($path === null) {
            return null;
        }

        return str_starts_with($path, 'catalog/')
            ? asset($path)
            : asset('storage/'.$path);
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class);
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

    /** État léger du drop (JSON de /state, compte à rebours et stock des pages). */
    public function toState(): array
    {
        $this->loadMissing('sizes');

        return [
            'status' => $this->status(),
            'server_now' => now()->toIso8601String(),
            'opens_at' => $this->opens_at->toIso8601String(),
            'closes_at' => $this->closes_at->toIso8601String(),
            'stock_total' => $this->stockTotal(),
            'sold' => $this->soldCount(),
            'available' => $this->available(),
            'sizes' => $this->sizes->map(fn ($size) => [
                'id' => $size->id,
                'label' => $size->label,
                'available' => $size->available(),
            ])->values(),
        ];
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
