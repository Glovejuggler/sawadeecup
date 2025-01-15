<?php

namespace App\Http\Controllers;

use App\Events\OrderDone;
use App\Models\SoldCost;
use App\Models\SoldItem;
use App\Events\OrderPlaced;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTransactionRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = Transaction::onlyTrashed()
                        ->filter([
                            'date' => $request->date ? $request->date : today(),
                            'search' => $request->search
                        ])
                        ->get();

        return inertia('Sales', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'date']),
            'total' => [
                'gross' => $transactions->sum('gross'),
                'cost' => $transactions->sum('cost'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        // if ($request->wantsJson()) {
        //     return $request;
        // }
        
        if ($request->wantsJson()) {
            $order = Transaction::create($request->validated());
    
            foreach ($request->items as $item) {
                $soldItem = SoldItem::create([
                    'transaction_id' => $order->id,
                    'name' => $item['name'],
                    'category' => $item['category']['name'],
                    'price' => $item['price'],
                    'quantity' => $item['count']
                ]);

                foreach ($item['costing'] as $cost) {
                    SoldCost::create([
                        'sold_item_id' => $soldItem->id,
                        'name' => $cost['name'],
                        'cost' => $cost['cost'],
                    ]);
                }
            }

            $order->delete();
            
            // OrderPlaced::dispatch($order->id);

            return [
                'message' => 'Order placed',
                'transaction' => $order,
            ];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        if ($request->wantsJson()) {
            return Transaction::findOrFail($id);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        OrderDone::dispatch($id);

        return 'Order done';
    }

    /**
     * Force delete the specified resource
     */
    public function raze($id, Request $request)
    {
        $transaction = Transaction::withTrashed()->findOrFail($id);
        SoldCost::whereIn('sold_item_id', SoldItem::whereBelongsTo($transaction)->pluck('id'))->delete();
        SoldItem::where('transaction_id', $transaction->id)->delete();

        $transaction->forceDelete();

        OrderDone::dispatch($id);

        if ($request->wantsJson()) {
            return 'Order cancelled';
        } else {
            return redirect()->back();
        }
    }
}
