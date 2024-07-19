<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ClientController;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\AuthenticateSuperAdmin;
use App\Models\Client;
use App\Models\Technology;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard',[ClientController::class,'index'])->middleware(['auth', 'verified'])->name('dashboard');


Route::prefix('clients')->group(function () {
    Route::get('/client-details', [ClientController::class, 'viewClientDetails'])->name('client-details');
    Route::post('/add-client', [ClientController::class, 'saveClientDetails'])->name('add-client');
    Route::get('/get-archived-client', [ClientController::class, 'getArchivedClientDetails'])->name('get-archived-client-details');
    Route::post('/archive-client', [ClientController::class, 'archiveClient'])->name('archive-client');
    Route::post('/unarchive-client', [ClientController::class, 'unArchiveClientDetails'])->name('unarchive-client');
    Route::post('/delete-client', [ClientController::class, 'deleteClientDetails'])->name('delete-client');
    Route::get('/view-client', [ClientController::class, 'showClientDetails'])->name('view-client');
    Route::get('/edit-client', [ClientController::class, 'editClientDetails'])->name('edit-client');
    Route::post('/update-client', [ClientController::class, 'updateClientDetails'])->name('update-client');
    Route::get('/get-technologies', [ClientController::class, 'getTechnologies'])->name('getTechnologies');
    Route::get('/filter-clients', [ClientController::class, 'filterClientDetails'])->name('client-filtering');
});

Route::get('/archived-clients', [ClientController::class, 'archivedClientDetails'])->middleware(['auth', 'verified'])->name('archived-clients');

Route::prefix('super-admin')->middleware([AuthenticateSuperAdmin::class])->group(function () {
    Route::get('/super-admin-panel', [SuperAdminController::class, 'superAdminPanel'])->name('super-admin-panel');
    Route::get('/get-all-user', [SuperAdminController::class, 'getAllUsers'])->name('super-admin-get-all-users');
    Route::post('/add-users', [SuperAdminController::class, 'addUsers'])->name('super-admin-add-users');
    Route::post('/assign-role', [SuperAdminController::class, 'assignRoles'])->name('super-admin-assign-roles');
    Route::post('/delete-user', [SuperAdminController::class, 'deleteUser'])->name('super-admin-delete-user');
    Route::get('/edit-user', [SuperAdminController::class, 'editUser'])->name('super-admin-edit-user');
    Route::post('/update-user', [SuperAdminController::class, 'updateUser'])->name('super-admin-update-user');
});

Route::prefix('admin')->middleware([AuthenticateAdmin::class])->group(function () {
    Route::get('/admin-panel', [AdminController::class, 'adminPanel'])->name('admin-panel');
    Route::get('/get-all-user', [AdminController::class, 'getAllUsers'])->name('admin-get-all-users');
    Route::post('/add-users', [AdminController::class, 'addUsers'])->name('admin-add-users');
    Route::post('/delete-user', [SuperAdminController::class, 'deleteUser'])->name('admin-delete-user');
    Route::get('/edit-user', [SuperAdminController::class, 'editUser'])->name('admin-edit-user');
    Route::post('/update-user', [SuperAdminController::class, 'updateUser'])->name('admin-update-user');
});


require __DIR__ . '/auth.php';
