<?php

use App\Modules\CRM\Controllers\ClientController;
use App\Modules\CRM\Controllers\ProspectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AbracadaBati API Routes
|--------------------------------------------------------------------------
| All routes require a valid JWT from AbracadaWorld Core.
| The `core.auth` middleware calls GET /api/me on the Core and syncs
| the local user. Use $request->attributes->get('auth_user') to access it.
*/

Route::middleware('core.auth')->group(function () {

    // --- CRM : Prospects ---
    Route::prefix('batiment/prospects')->group(function () {
        Route::get('/', [ProspectController::class, 'index']);
        Route::post('/', [ProspectController::class, 'store']);
        Route::get('/{id}', [ProspectController::class, 'show']);
        Route::put('/{id}', [ProspectController::class, 'update']);
        Route::delete('/{id}', [ProspectController::class, 'destroy']);
        Route::post('/{id}/convert-to-client', [ProspectController::class, 'convertToClient']);
    });

    // --- CRM : Clients ---
    Route::prefix('batiment/clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::get('/{id}', [ClientController::class, 'show']);
        Route::put('/{id}', [ClientController::class, 'update']);
        Route::delete('/{id}', [ClientController::class, 'destroy']);
        Route::post('/{id}/notes', [ClientController::class, 'addNote']);
        Route::post('/{id}/generate-portal-token', [ClientController::class, 'generatePortalToken']);
    });

    // TODO: quotes, invoices, chantiers, company_settings

});
