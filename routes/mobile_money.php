<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\MobileMoney\MixxController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ReconController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tanzania mobile-money collections module
|--------------------------------------------------------------------------
| Public: inbound provider webhooks + public payment-link checkout.
| Everything else lives behind `auth` (+ granular `perm` gates).
*/

// ---- Public: provider webhooks (signature-verified, CSRF-exempt) ----
Route::post('/webhooks/{provider}', [WebhookController::class, 'receive'])
    ->where('provider', 'mpesa|airtel|mixx|halopesa|tpesa')
    ->name('webhooks.receive');

// ---- Public: payment-link checkout (opaque short code + legacy token) ----
Route::get('/c/{code}', [CollectionController::class, 'checkoutByCode'])->where('code', '[A-Za-z0-9]{6}')->name('collections.links.short');
Route::post('/c/{code}', [CollectionController::class, 'payByCode'])->where('code', '[A-Za-z0-9]{6}')->name('collections.links.short.pay');
Route::get('/c/pay/{token}', [CollectionController::class, 'checkout'])->name('collections.links.checkout');
Route::post('/c/pay/{token}', [CollectionController::class, 'checkoutPay'])->name('collections.links.checkout.pay');

// ---- Public: unified collection endpoint (used by /pay) ----
Route::post('/c/pay', [CollectionPaymentController::class, 'publicStore'])->name('collections.public.store');

Route::middleware('auth')->group(function () {

    // ---- PAYMENTS ----
    Route::prefix('collections/payments')->name('collections.payments.')->group(function () {
        Route::get('/', [CollectionPaymentController::class, 'index'])->middleware('perm:payments.view')->name('index');
        Route::get('/create', [CollectionPaymentController::class, 'create'])->middleware('perm:payments.initiate')->name('create');
        Route::post('/', [CollectionPaymentController::class, 'store'])->middleware('perm:payments.initiate')->name('store');
        Route::get('/pending', [CollectionPaymentController::class, 'index'])->middleware('perm:payments.view')->defaults('status', 'PENDING')->name('pending');
        Route::get('/successful', [CollectionPaymentController::class, 'index'])->middleware('perm:payments.view')->defaults('status', 'SUCCESS')->name('successful');
        Route::get('/failed', [CollectionPaymentController::class, 'index'])->middleware('perm:payments.view')->defaults('status', 'FAILED')->name('failed');
        Route::get('/reversed', [CollectionPaymentController::class, 'index'])->middleware('perm:payments.view')->defaults('status', 'REVERSED')->name('reversed');
        Route::get('/refunds', [CollectionPaymentController::class, 'refunds'])->middleware('perm:payments.view')->name('refunds');
        Route::post('/{payment}/refund', [CollectionPaymentController::class, 'requestRefund'])->middleware('perm:payments.refund.request')->name('refund.request');
        Route::post('/refunds/{refund}/approve', [CollectionPaymentController::class, 'approveRefund'])->middleware('perm:payments.refund.approve')->name('refund.approve');
        Route::post('/{payment}/reverse', [CollectionPaymentController::class, 'requestReversal'])->middleware('perm:payments.reverse')->name('reverse');
        Route::post('/{transaction}/refresh', [CollectionPaymentController::class, 'refresh'])->middleware('perm:payments.view')->name('refresh');
        Route::get('/{transaction}', [CollectionPaymentController::class, 'show'])->middleware('perm:payments.view')->name('show');
    });

    // ---- COLLECTIONS ----
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/requests', [CollectionController::class, 'requests'])->middleware('perm:payments.view')->name('requests');
        Route::get('/requests/create', [CollectionController::class, 'createRequest'])->middleware('perm:payments.initiate')->name('requests.create');
        Route::post('/requests', [CollectionController::class, 'storeRequest'])->middleware('perm:payments.initiate')->name('requests.store');
        Route::post('/requests/{request}/cancel', [CollectionController::class, 'cancelRequest'])->middleware('perm:payments.initiate')->name('requests.cancel');
        Route::post('/requests/{request}/collect', [CollectionController::class, 'collectRequest'])->middleware('perm:payments.initiate')->name('requests.collect');

        Route::get('/links', [CollectionController::class, 'links'])->middleware('perm:payments.view')->name('links');
        Route::post('/links', [CollectionController::class, 'storeLink'])->middleware('perm:payments.initiate')->name('links.store');
        Route::post('/links/{link}/revoke', [CollectionController::class, 'revokeLink'])->middleware('perm:payments.initiate')->name('links.revoke');

        Route::get('/invoices', [CollectionController::class, 'invoices'])->middleware('perm:payments.view')->name('invoices');
        Route::get('/invoices/create', [CollectionController::class, 'createInvoice'])->middleware('perm:payments.initiate')->name('invoices.create');
        Route::post('/invoices', [CollectionController::class, 'storeInvoice'])->middleware('perm:payments.initiate')->name('invoices.store');
        Route::post('/invoices/{invoice}/collect', [CollectionController::class, 'collectInvoice'])->middleware('perm:payments.initiate')->name('invoices.collect');
        Route::post('/invoices/{invoice}/cancel', [CollectionController::class, 'cancelInvoice'])->middleware('perm:payments.initiate')->name('invoices.cancel');

        Route::get('/recurring', [CollectionController::class, 'recurring'])->middleware('perm:payments.view')->name('recurring');
        Route::get('/recurring/create', [CollectionController::class, 'createRecurring'])->middleware('perm:payments.initiate')->name('recurring.create');
        Route::post('/recurring', [CollectionController::class, 'storeRecurring'])->middleware('perm:payments.initiate')->name('recurring.store');
        Route::post('/recurring/{recurring}/pause', [CollectionController::class, 'pauseRecurring'])->middleware('perm:payments.initiate')->name('recurring.pause');
        Route::post('/recurring/{recurring}/resume', [CollectionController::class, 'resumeRecurring'])->middleware('perm:payments.initiate')->name('recurring.resume');
    });

    // ---- CUSTOMERS ----
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->middleware('perm:customers.view')->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->middleware('perm:customers.view')->name('create');
        Route::post('/', [CustomerController::class, 'store'])->middleware('perm:customers.view')->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->middleware('perm:customers.view')->name('show');
        Route::get('/{customer}/history', [CustomerController::class, 'show'])->middleware('perm:customers.view')->defaults('tab', 'history')->name('history');
        Route::post('/{customer}/reveal', [CustomerController::class, 'reveal'])->middleware('perm:customers.reveal')->name('reveal');
    });

    // ---- MOBILE MONEY providers (one page per section) ----
    Route::prefix('providers')->name('providers.')->group(function () {
        Route::get('/', [ProviderController::class, 'index'])->middleware('perm:providers.view')->name('index');
        Route::get('/{provider}', [ProviderController::class, 'show'])->middleware('perm:providers.view')->name('show');
        Route::get('/{provider}/collections', [ProviderController::class, 'collections'])->middleware('perm:providers.view')->name('collections');
        Route::get('/{provider}/status', [ProviderController::class, 'statusPage'])->middleware('perm:providers.view')->name('status');
        Route::get('/{provider}/webhooks', [ProviderController::class, 'webhooksPage'])->middleware('perm:providers.view')->name('webhooks');
        Route::get('/{provider}/logs', [ProviderController::class, 'logsPage'])->middleware('perm:providers.view')->name('logs');
        Route::get('/{provider}/config', [ProviderController::class, 'configPage'])->middleware('perm:providers.configure')->name('config.page');
        Route::put('/{provider}/config', [ProviderController::class, 'updateConfig'])->middleware('perm:providers.configure')->name('config.update');
        Route::get('/{provider}/credentials', [ProviderController::class, 'credentialsPage'])->middleware('perm:credentials.view')->name('credentials.page');
        Route::post('/{provider}/credentials', [ProviderController::class, 'storeCredential'])->middleware('perm:credentials.manage')->name('credentials.store');
        Route::post('/{provider}/credentials/test', [ProviderController::class, 'testCredential'])->middleware('perm:providers.view')->name('credentials.test');
        Route::post('/{provider}/webhook-secret/rotate', [ProviderController::class, 'rotateWebhookSecret'])->middleware('perm:credentials.manage')->name('webhook.rotate');
        Route::post('/{provider}/status-query', [ProviderController::class, 'statusQuery'])->middleware('perm:providers.view')->name('status.query');
    });

    // ---- RECONCILIATION ----
    Route::prefix('reconciliation')->name('reconciliation.')->group(function () {
        Route::get('/', [ReconController::class, 'index'])->middleware('perm:reconciliation.review')->name('index');
        Route::get('/unmatched', [ReconController::class, 'index'])->middleware('perm:reconciliation.review')->defaults('tab', 'unmatched')->name('unmatched');
        Route::get('/provider', [ReconController::class, 'index'])->middleware('perm:reconciliation.review')->defaults('tab', 'provider')->name('provider');
        Route::get('/exceptions', [ReconController::class, 'index'])->middleware('perm:reconciliation.review')->defaults('tab', 'exceptions')->name('exceptions');
        Route::post('/match/{transaction}', [ReconController::class, 'match'])->middleware('perm:reconciliation.review')->name('match');
        Route::post('/exceptions/{event}/resolve', [ReconController::class, 'resolve'])->middleware('perm:reconciliation.review')->name('resolve');
        Route::post('/approve', [ReconController::class, 'approve'])->middleware('perm:reconciliation.approve')->name('approve');
    });

    // ---- SETTLEMENTS ----
    Route::prefix('settlements')->name('settlements.')->group(function () {
        Route::get('/', [SettlementController::class, 'index'])->middleware('perm:settlements.manage')->name('index');
        Route::get('/create', [SettlementController::class, 'create'])->middleware('perm:settlements.manage')->name('create');
        Route::post('/', [SettlementController::class, 'store'])->middleware('perm:settlements.manage')->name('store');
        Route::get('/transactions', [SettlementController::class, 'transactions'])->middleware('perm:settlements.manage')->name('transactions');
        Route::get('/reports', [SettlementController::class, 'reports'])->middleware('perm:settlements.manage')->name('reports');
        Route::get('/{settlement}', [SettlementController::class, 'show'])->middleware('perm:settlements.manage')->name('show');
        Route::post('/{settlement}/approve', [SettlementController::class, 'approve'])->middleware('perm:settlements.approve')->name('approve');
    });

    // ---- REPORTS ----
    Route::prefix('reports')->name('mm.reports.')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily'])->middleware('perm:reports.view')->name('daily');
        Route::get('/providers', [ReportController::class, 'providers'])->middleware('perm:reports.view')->name('providers');
        Route::get('/transactions', [ReportController::class, 'transactions'])->middleware('perm:reports.view')->name('transactions');
        Route::get('/fees', [ReportController::class, 'fees'])->middleware('perm:reports.view')->name('fees');
        Route::get('/export', [ReportController::class, 'export'])->middleware('perm:reports.export')->name('export');
    });

    // ---- DEVELOPERS ----
    Route::prefix('developers')->name('developers.')->group(function () {
        Route::get('/api-keys', [DeveloperController::class, 'keys'])->middleware('perm:developers.keys')->name('keys');
        Route::post('/api-keys', [DeveloperController::class, 'storeKey'])->middleware('perm:developers.keys')->name('keys.store');
        Route::post('/api-keys/{key}/revoke', [DeveloperController::class, 'revokeKey'])->middleware('perm:developers.keys')->name('keys.revoke');
        Route::get('/webhooks', [DeveloperController::class, 'webhooks'])->middleware('perm:developers.keys')->name('webhooks');
        Route::get('/api-logs', [DeveloperController::class, 'apiLogs'])->middleware('perm:developers.keys')->name('logs');
        Route::get('/docs', [DeveloperController::class, 'docs'])->middleware('perm:developers.keys')->name('docs');
    });

    // ---- MIXX BY YAS full module ----
    Route::prefix('providers/mixx')->name('mixx.')->group(function () {
        Route::get('/dashboard', [MixxController::class, 'dashboard'])->middleware('perm:providers.view')->name('dashboard');
        Route::get('/collect', [MixxController::class, 'createPayment'])->middleware('perm:payments.initiate')->name('collect');
        Route::post('/collect', [MixxController::class, 'storePayment'])->middleware('perm:payments.initiate')->name('collect.store');
        Route::get('/transactions/{transaction}', [MixxController::class, 'showTransaction'])->middleware('perm:payments.view')->name('transactions.show');
        Route::post('/transactions/{transaction}/verify', [MixxController::class, 'verifyTransaction'])->middleware('perm:payments.view')->name('transactions.verify');
        Route::get('/disbursements', [MixxController::class, 'disbursements'])->middleware('perm:payments.view')->name('disbursements');
        Route::get('/disbursements/create', [MixxController::class, 'createDisbursement'])->middleware('perm:payments.initiate')->name('disbursements.create');
        Route::post('/disbursements', [MixxController::class, 'storeDisbursement'])->middleware('perm:payments.initiate')->name('disbursements.store');
        Route::get('/disbursements/{disbursement}', [MixxController::class, 'showDisbursement'])->middleware('perm:payments.view')->name('disbursements.show');
        Route::get('/error-codes', [MixxController::class, 'errorCodes'])->middleware('perm:providers.view')->name('error-codes');
        Route::get('/reconciliation', [MixxController::class, 'reconciliation'])->middleware('perm:reconciliation.review')->name('recon');
    });

    // ---- ADMINISTRATION: roles + security events ----
    Route::get('/roles', [RoleController::class, 'index'])->middleware('perm:roles.manage')->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('perm:roles.manage')->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('perm:roles.manage')->name('roles.show');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('perm:roles.manage')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('perm:roles.manage')->name('roles.destroy');
    Route::get('/security-events', [RoleController::class, 'securityEvents'])->middleware('perm:audit.view')->name('security.events');
});
