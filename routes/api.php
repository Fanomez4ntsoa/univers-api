<?php

use App\Modules\ClientPortal\Controllers\ClientPortalController;
use App\Modules\CRM\Controllers\ChantierController;
use App\Modules\Ecosystem\Controllers\EventController;
use App\Modules\Ecosystem\Controllers\JobController;
use App\Modules\Ecosystem\Controllers\ListingController;
use App\Modules\Ecosystem\Controllers\PostController;
use App\Modules\Ecosystem\Controllers\ShopController;
use App\Modules\Ecosystem\Controllers\SocialController;
use App\Modules\CRM\Controllers\ClientController;
use App\Modules\CRM\Controllers\CompanySettingController;
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

    // --- CRM : Paramètres entreprise ---
    Route::prefix('batiment/settings')->group(function () {
        Route::get('/company', [CompanySettingController::class, 'show']);
        Route::put('/company', [CompanySettingController::class, 'update']);
    });

    // --- Ecosystem : Posts / Feed ---
    Route::prefix('ecosystem/posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
        Route::post('/{id}/like', [PostController::class, 'toggleLike']);
        Route::post('/{id}/comments', [PostController::class, 'addComment']);
        Route::get('/{id}/comments', [PostController::class, 'listComments']);
    });

    // --- Ecosystem : Ma Boutique (protégé) ---
    Route::prefix('ecosystem/shop')->group(function () {
        Route::get('/', [ShopController::class, 'showMine']);
        Route::put('/', [ShopController::class, 'updateMine']);
        Route::get('/products', [ShopController::class, 'listMyProducts']);
        Route::post('/products', [ShopController::class, 'storeProduct']);
        Route::put('/products/{id}', [ShopController::class, 'updateProduct']);
        Route::delete('/products/{id}', [ShopController::class, 'destroyProduct']);
    });

    // --- Ecosystem : Mes Annonces (protégé) ---
    Route::prefix('ecosystem/listings')->group(function () {
        Route::get('/my', [ListingController::class, 'myListings']);
        Route::post('/', [ListingController::class, 'store']);
        Route::put('/{id}', [ListingController::class, 'update']);
        Route::delete('/{id}', [ListingController::class, 'destroy']);
        Route::post('/{id}/sold', [ListingController::class, 'markSold']);
    });

    // --- Ecosystem : Jobs ---
    Route::prefix('ecosystem/jobs')->group(function () {
        Route::get('/', [JobController::class, 'index']);
        Route::post('/', [JobController::class, 'store']);
        Route::get('/{id}', [JobController::class, 'show']);
        Route::put('/{id}', [JobController::class, 'update']);
        Route::delete('/{id}', [JobController::class, 'destroy']);
        Route::post('/{id}/apply', [JobController::class, 'apply']);
        Route::get('/{id}/applications', [JobController::class, 'applications']);
    });

    // --- Ecosystem : Events ---
    Route::prefix('ecosystem/events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{id}', [EventController::class, 'show']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
        Route::post('/{id}/attend', [EventController::class, 'toggleAttend']);
    });

    // --- Ecosystem : Réseau Social (profils + follow + feed) ---
    Route::get('ecosystem/users', [SocialController::class, 'discoverUsers']);
    Route::get('ecosystem/users/{id}', [SocialController::class, 'showProfile']);
    Route::post('ecosystem/users/{id}/follow', [SocialController::class, 'toggleFollow']);
    Route::get('ecosystem/users/{id}/followers', [SocialController::class, 'followers']);
    Route::get('ecosystem/users/{id}/following', [SocialController::class, 'following']);
    Route::get('ecosystem/feed', [SocialController::class, 'personalizedFeed']);
    Route::get('ecosystem/profile', [SocialController::class, 'myProfile']);

});

/*
|--------------------------------------------------------------------------
| Boutiques publiques (sans auth Core)
|--------------------------------------------------------------------------
*/

Route::prefix('ecosystem/shops')->group(function () {
    Route::get('/', [ShopController::class, 'listPublic']);
    Route::get('/{slug}', [ShopController::class, 'showPublic']);
});

/*
|--------------------------------------------------------------------------
| Annonces publiques (sans auth Core)
|--------------------------------------------------------------------------
*/

Route::prefix('ecosystem/listings')->group(function () {
    Route::get('/', [ListingController::class, 'listPublic']);
    Route::get('/{id}', [ListingController::class, 'showPublic']);
});

/*
|--------------------------------------------------------------------------
| Client Portal — Routes publiques (sans auth Core)
|--------------------------------------------------------------------------
| Accès via portal_token généré par POST /api/batiment/clients/{id}/generate-portal-token
*/

Route::prefix('portal/{token}')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard']);
    Route::get('/quotes', [ClientPortalController::class, 'listQuotes']);
    Route::get('/quotes/{id}', [ClientPortalController::class, 'showQuote']);
    Route::post('/quotes/{id}/sign', [ClientPortalController::class, 'signQuote']);
    Route::get('/invoices', [ClientPortalController::class, 'listInvoices']);
    Route::get('/invoices/{id}', [ClientPortalController::class, 'showInvoice']);
});
