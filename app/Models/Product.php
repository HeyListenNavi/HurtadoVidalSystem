<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
    ];

    /**
     * Get the quotes for the product.
     */
    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class);
    }
}
