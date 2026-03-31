<?php

use App\Modules\CRM\Controllers\ChantierController;
use App\Modules\CRM\Controllers\ClientController;
use App\Modules\CRM\Controllers\InvoiceController;
use App\Modules\CRM\Controllers\ProspectController;
use App\Modules\CRM\Controllers\QuoteController;
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

    // --- CRM : Devis (Quotes) ---
    Route::prefix('batiment/quotes')->group(function () {
        Route::get('/', [QuoteController::class, 'index']);
        Route::post('/', [QuoteController::class, 'store']);
        Route::get('/{id}', [QuoteController::class, 'show']);
        Route::put('/{id}', [QuoteController::class, 'update']);
        Route::delete('/{id}', [QuoteController::class, 'destroy']);
        Route::post('/{id}/send', [QuoteController::class, 'send']);
        Route::post('/{id}/sign', [QuoteController::class, 'sign']);
        Route::post('/{id}/duplicate', [QuoteController::class, 'duplicate']);
        Route::post('/{id}/convert-invoice', [QuoteController::class, 'convertToInvoice']);
    });

    // --- CRM : Factures (Invoices) ---
    Route::prefix('batiment/invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/{id}', [InvoiceController::class, 'show']);
        Route::put('/{id}', [InvoiceController::class, 'update']);
        Route::delete('/{id}', [InvoiceController::class, 'destroy']);
        Route::post('/{id}/send', [InvoiceController::class, 'send']);
        Route::post('/{id}/mark-paid', [InvoiceController::class, 'markPaid']);
        Route::post('/{id}/cancel', [InvoiceController::class, 'cancel']);
    });

    // --- CRM : Chantiers ---
    Route::prefix('batiment/chantiers')->group(function () {
        Route::get('/', [ChantierController::class, 'index']);
        Route::post('/', [ChantierController::class, 'store']);
        Route::get('/pipeline', [ChantierController::class, 'pipeline']);
        Route::get('/{id}', [ChantierController::class, 'show']);
        Route::put('/{id}', [ChantierController::class, 'update']);
        Route::delete('/{id}', [ChantierController::class, 'destroy']);
        Route::put('/{id}/move-stage', [ChantierController::class, 'moveStage']);
        Route::post('/{id}/documents', [ChantierController::class, 'addDocument']);
        Route::delete('/{id}/documents/{documentId}', [ChantierController::class, 'removeDocument']);
        Route::post('/{id}/comments', [ChantierController::class, 'addComment']);
        Route::post('/{id}/time-entries', [ChantierController::class, 'addTimeEntry']);
        Route::post('/{id}/costs', [ChantierController::class, 'addCost']);
    });

    // TODO: company_settings

});
