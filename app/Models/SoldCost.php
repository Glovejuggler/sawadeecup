<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoldCost extends Model
{
    use HasFactory;

    protected $fillable = ['sold_item_id', 'name', 'cost'];

    public function sold(): BelongsTo
    {
        return $this->belongsTo(SoldItem::class, 'sold_item_id', 'id');
    }
}
