<?php

use App\Http\Controllers\API\MidtransController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//Homepage
Route::get('/', function () {
return redirect()->route('admin-dashboard');
});

//Dashboard
Route::prefix('dashboard')
->middleware(['auth:sanctum','admin'])
->group(function() {
    Route::get('/',[DashboardController::class, 'index'])->name('admin-dashboard');
});

/**Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
**/

Route::get('midtrans/success', [MidtransController::class,'success']);
Route::get('midtrans/unfinish', [MidtransController::class,'unfinish']);
Route::get('midtrans/error', [MidtransController::class,'error']);

