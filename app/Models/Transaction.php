<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'payment', 'type', 'note'];

    protected $appends = ['number', 'quantity', 'gross', 'cost', 'elapsed'];

    public function items(): HasMany
    {
        return $this->hasMany(SoldItem::class);
    }

    public function getNumberAttribute()
    {
        return $this->id - Transaction::withTrashed()->whereDate('created_at', $this->created_at)->first()->id + 1;
    }

    public function getQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getGrossAttribute()
    {
        return $this->items->sum('gross');
    }

    public function getCostAttribute()
    {
        return $this->items->sum('cost');
    }

    public function getElapsedAttribute()
    {
        return $this->deleted_at?->diffInSeconds($this->created_at);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            });
        })->when($filters['date'] ?? null, function ($query, $date) {
            if ($date == 'alltime') {
                // $query->all();
            } else {
                $query->whereDate('deleted_at', Carbon::parse($date));
            }
        });
    }
}
