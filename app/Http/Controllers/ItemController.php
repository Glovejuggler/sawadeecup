<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Costing;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StoreItemRequest;
use App\Models\SoldItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Category $category = null)
    {
        if ($category) {
            return inertia('Item/Index', [
                'items' => Item::where('category_id', $category->id)->get(),
                'categories' => Category::all(),
                'category' => $category
            ]);
        } else {
            $category = Category::first();
            if ($category) {
                return redirect()->route('items.index', $category);
            } else {
                return inertia('Item/Index', [
                    'categories' => Category::all()
                ]);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Item/Create', [
            'suggestions' => Costing::selectRaw('DISTINCT UPPER(name) as name')->pluck('name'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
        $item = new Item($request->validated());

        if ($request->image) {
            $file = $request->image;
            $name = $file->hashName();
            $path = $file->storeAs('/images', $name, 'public');

            $item->pic = $path;
        }

        $item->save();

        if ($request->breakdown) {
            foreach ($request->breakdown as $costing) {
                Costing::create([
                    'name' => $costing['name'],
                    'cost' => $costing['cost'],
                    'item_id' => $item->id
                ]);
            }
        }

        return redirect()->route('items.index', Category::find($item->category_id));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Item::findOrFail($id);

        return inertia('Item/Show', [
            'item' => $item,
            'categories' => Category::all(),
            'suggestions' => Costing::selectRaw('DISTINCT UPPER(name) as name')->pluck('name'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreItemRequest $request, Item $item)
    {
        // dd($request);
        $item->update($request->validated());

        if ($request->image) {
            $file = $request->image;
            $name = $file->hashName();
            $path = $file->storeAs('/images', $name, 'public');

            $item->pic = $path;
            $item->save();
        }

        if ($request->breakdown) {
            foreach ($request->breakdown as $costing) {
                $foc = Costing::updateOrCreate([
                    'item_id' => $item->id,
                    'name' => $costing['name'],
                ], [
                    'cost' => $costing['cost']
                ]);
                $arr[] = $foc->id;
            }
            Costing::where('item_id', $item->id)->whereNotIn('id', $arr)->delete();
        } else {
            Costing::where('item_id', $item->id)->delete();
        }

        return redirect()->route('items.index', Category::find($item->category_id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        if ($item->pic) {
            Storage::disk('public')->delete($item->pic);
        }

        Costing::where('item_id', $item->id)->delete();
        
        $item->delete();

        return redirect()->route('items.index');
    }
}
