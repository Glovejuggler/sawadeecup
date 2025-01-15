<?php

use Carbon\Carbon;
use App\Models\Item;
use Inertia\Inertia;
use App\Models\Costing;
use App\Models\Category;
use App\Models\SoldItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\POS;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);

    // return view('home.index');
    return redirect()->route('dashboard');
})->name('home');

// Route::get('/menu', function() {
//     $menuPath = public_path('menu.json');
//     $menuContents = file_get_contents($menuPath);
//     $menu = json_decode($menuContents);

//     // dd($menu->products);

//     return view('home.menu', [
//         'menu' => $menu->products
//     ]);
// })->name('menu');

// Route::get('/contacts', function() {
//     return view('home.contacts');
// })->name('contacts');

Route::get('/dashboard', function () {
    $finishedOrders = Transaction::onlyTrashed();

    $transactionCount = $finishedOrders->count();
    $soldItemsCount = SoldItem::whereHas('transaction', function ($query) {
        $query->onlyTrashed();
    })->sum('quantity');
    return Inertia::render('Dashboard', [
        'items' => Item::count(),
        'sold' => $soldItemsCount,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{id}/destroy', [UserController::class, 'destroy'])->name('users.destroy');
    
    Route::post('/category', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/category/{id}/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/category/{category}/delete', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/items/{category?}', [ItemController::class, 'index'])->name('items.index');
    Route::get('/item/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/item/store', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{id}/show', [ItemController::class, 'show'])->name('items.show');
    Route::post('/items/{item}/update', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/item/delete/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('/kitchen', function () {
        return inertia('Kitchen', [
            'orders' => Transaction::all()->pluck('id')
        ]);
    })->name('kitchen');

    Route::delete('/order/{id}/cancel', [TransactionController::class, 'raze'])->name('order.cancel');
    Route::delete('/order/{id}/delete', [TransactionController::class, 'destroy'])->name('order.done');

    Route::get('unit_sales/{category?}', [ReportsController::class, 'unitSales'])->name('unit.sales');
    Route::get('inventory_costing/{month?}', [ReportsController::class, 'costing'])->name('inventory.costing');

    Route::get('sales', [TransactionController::class, 'index'])->name('sales');
    Route::get('/pos', [POS::class, 'index'])->name('pos');
    Route::get('receipt/{id}', [TransactionController::class, 'show'])->name('receipt');

    Route::post('/transact', [TransactionController::class, 'store'])->name('transaction.save');
    Route::get('order/{id}', [TransactionController::class, 'show'])->name('order.get');
});


require __DIR__.'/auth.php';
