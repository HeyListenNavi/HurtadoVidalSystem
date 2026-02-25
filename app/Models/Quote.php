<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'quote_number',
        'total_amount',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            $quote->quote_number = (string) Str::uuid();
        });
    }

    /**
     * Get the patient that owns the quote.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the products for the quote.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'quote_product')
                    ->withPivot('price', 'quantity');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Critical for the UI:   How much is left?
    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_amount - $this->payments()->sum('amount');
    }
}
