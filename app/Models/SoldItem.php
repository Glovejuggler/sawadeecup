<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoldItem extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'category', 'name', 'price', 'quantity'];

    protected $appends = ['gross', 'cost'];

    public function costing(): HasMany
    {
        return $this->hasMany(SoldCost::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getGrossAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getCostAttribute()
    {
        return $this->costing->sum('cost') * $this->quantity;
    }
}
