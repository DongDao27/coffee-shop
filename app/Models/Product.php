<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    public const DEFAULT_CURRENCY = 'VND';

    public const DEFAULT_IMAGE = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93';

    public function getFormattedPriceAttribute(): string
    {
        return $this->getFormattedTotalAmount();
    }

    public function getFormattedTotalAmount(int $quantity = 1): string
    {
        return number_format((float) $this->price * $quantity).' '.$this->currency;
    }

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'display_image_url',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
