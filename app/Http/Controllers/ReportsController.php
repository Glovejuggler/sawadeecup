<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Costing;
use App\Models\Category;
use App\Models\SoldCost;
use App\Models\SoldItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Sales per product report
     */
    public function unitSales(Request $request, Category $category = null)
    {
        // dd(SoldItem::all(), SoldCost::all());
        if ($category) {
            $date = $request->date ? Carbon::parse($request->date) : today();
            return inertia('UnitSales', [
                'items' => Item::where('category_id',$category->id)
                            ->withSum(['sales as total_quantity' => function ($query) use ($date) {
                                $query->whereDate('created_at', $date);
                            }], 'quantity')
                            ->orderBy('total_quantity', 'desc')
                            ->filter($request->only(['search']))
                            ->get(),
                'categories' => Category::all(),
                'filters' => $request->only(['date', 'search']),
                'cat' => $category
            ]);
        } else {
            $category = Category::first();
            if ($category) {
                return redirect()->route('unit.sales', $category);
            } else {
                return inertia('UnitSales', [
                    'categories' => Category::all()
                ]);
            }
        }
    }

    /**
     * Inventory costing report
     */
    public function costing($month = null)
    {
        $m = Carbon::parse($month) ?: today();

        $costing = SoldCost::withSum(['sold as totalSold' => function ($q) use ($m) {
            $q->whereHas('transaction', function ($q) use ($m) {
                $q->onlyTrashed()
                    ->whereNotNull('deleted_at')
                    ->whereMonth('deleted_at', $m->month)
                    ->whereYear('deleted_at', $m->year);
            });
        }],'quantity')->get();

        $merged = $costing->groupBy(function ($g) {
            return strtoupper($g->name);
        })->map(function ($group) {
            return [
                'name' => $group->first()->name,
                'total' => $group->sum(function ($c) {
                    return $c->totalSold * $c->cost;
                }),
            ];
        });

        return inertia('Costing', [
            'costing' => $merged,
            'total' => $merged->sum('total')
        ]);
    }
}
