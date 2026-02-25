<?php

namespace App\Models;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['quote_id', 'amount', 'stripe_link', 'status'];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
