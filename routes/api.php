<?php

use App\Modules\ClientPortal\Controllers\ClientPortalController;
use App\Modules\CRM\Controllers\ChantierController;
use App\Modules\Ecosystem\Controllers\EventController;
use App\Modules\Ecosystem\Controllers\JobController;
use App\Modules\Ecosystem\Controllers\ListingController;
use App\Modules\Ecosystem\Controllers\PostController;
use App\Modules\Ecosystem\Controllers\ShopController;
use App\Modules\Ecosystem\Controllers\SocialController;
use App\Modules\Matching\Controllers\MatchingController;
use App\Modules\Subscription\Controllers\SubscriptionController;
use App\Modules\CRM\Controllers\ClientController;
use App\Modules\CRM\Controllers\CompanySettingController;
use App\Modules\CRM\Controllers\InvoiceController;
use App\Modules\CRM\Controllers\ProspectController;
use App\Modules\CRM\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes protégées (middleware core.auth — Bearer JWT du Core)
|--------------------------------------------------------------------------
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

    // --- Ecosystem : Posts (actions protégées) ---
    Route::prefix('ecosystem/posts')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
        Route::post('/{id}/like', [PostController::class, 'toggleLike']);
        Route::post('/{id}/comments', [PostController::class, 'addComment']);
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

    // --- Ecosystem : Jobs (actions protégées) ---
    Route::prefix('ecosystem/jobs')->group(function () {
        Route::post('/', [JobController::class, 'store']);
        Route::put('/{id}', [JobController::class, 'update']);
        Route::delete('/{id}', [JobController::class, 'destroy']);
        Route::post('/{id}/apply', [JobController::class, 'apply']);
        Route::get('/{id}/applications', [JobController::class, 'applications']);
    });

    // --- Ecosystem : Events (actions protégées) ---
    Route::prefix('ecosystem/events')->group(function () {
        Route::post('/', [EventController::class, 'store']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
        Route::post('/{id}/attend', [EventController::class, 'toggleAttend']);
    });

    // --- Ecosystem : Social (actions protégées) ---
    Route::post('ecosystem/users/{id}/follow', [SocialController::class, 'toggleFollow']);
    Route::get('ecosystem/feed', [SocialController::class, 'personalizedFeed']);
    Route::get('ecosystem/profile', [SocialController::class, 'myProfile']);

    // --- Matching : Côté particulier (demandes) ---
    Route::prefix('matching/requests')->group(function () {
        Route::get('/', [MatchingController::class, 'myRequests']);
        Route::post('/', [MatchingController::class, 'storeRequest']);
        Route::get('/{id}', [MatchingController::class, 'showRequest']);
        Route::put('/{id}', [MatchingController::class, 'updateRequest']);
        Route::delete('/{id}', [MatchingController::class, 'destroyRequest']);
        Route::post('/{id}/close', [MatchingController::class, 'closeRequest']);
        Route::post('/{id}/quotes/{quoteId}/accept', [MatchingController::class, 'acceptQuote']);
    });

    // --- Matching : Côté artisan ---
    Route::get('matching/available', [MatchingController::class, 'available']);
    Route::post('matching/requests/{id}/quote', [MatchingController::class, 'submitQuote']);
    Route::get('matching/my-quotes', [MatchingController::class, 'myQuotes']);

    // --- Subscription : Stripe ---
    Route::prefix('subscription')->group(function () {
        Route::get('/status', [SubscriptionController::class, 'status']);
        Route::post('/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/portal', [SubscriptionController::class, 'portal']);
    });

});

/*
|--------------------------------------------------------------------------
| Routes publiques — Ecosystem (consultation sans auth)
|--------------------------------------------------------------------------
| Fidèle à Emergent : les GET de consultation sont publics.
| Seules les actions (POST/PUT/DELETE, like, comment, follow, apply, attend)
| restent protégées par core.auth ci-dessus.
*/

// --- Posts publics ---
Route::get('ecosystem/posts', [PostController::class, 'index']);
Route::get('ecosystem/posts/{id}', [PostController::class, 'show']);
Route::get('ecosystem/posts/{id}/comments', [PostController::class, 'listComments']);

// --- Jobs publics ---
Route::get('ecosystem/jobs', [JobController::class, 'index']);
Route::get('ecosystem/jobs/{id}', [JobController::class, 'show']);

// --- Events publics ---
Route::get('ecosystem/events', [EventController::class, 'index']);
Route::get('ecosystem/events/{id}', [EventController::class, 'show']);

// --- Profils publics ---
Route::get('ecosystem/users', [SocialController::class, 'discoverUsers']);
Route::get('ecosystem/users/{id}', [SocialController::class, 'showProfile']);
Route::get('ecosystem/users/{id}/followers', [SocialController::class, 'followers']);
Route::get('ecosystem/users/{id}/following', [SocialController::class, 'following']);

// --- Boutiques publiques ---
Route::get('ecosystem/shops', [ShopController::class, 'listPublic']);
Route::get('ecosystem/shops/{slug}', [ShopController::class, 'showPublic']);

// --- Annonces publiques ---
Route::get('ecosystem/listings', [ListingController::class, 'listPublic']);
Route::get('ecosystem/listings/{id}', [ListingController::class, 'showPublic']);

/*
|--------------------------------------------------------------------------
| Stripe Webhook (public, signature-verified)
|--------------------------------------------------------------------------
*/

Route::post('stripe/webhook', [SubscriptionController::class, 'webhook']);

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
