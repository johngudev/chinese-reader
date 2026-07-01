<?php

use App\Models\PremiumVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Subscription & Billing Routes
|--------------------------------------------------------------------------
| Stripe webhook, the billing portal, the premium upsell page, and the
| checkout flow. Pulled in via require from web.php, so these inherit the
| "web" middleware group. Stripe price id comes from config/services.php.
*/

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::get('/billing', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('dashboard'));
})->middleware('auth')->name('billing');

Route::get('/premium', function () {
    PremiumVisit::create(['user_id' => auth()->id()]);
    return view('premium');
})->middleware('auth')->name('premium');

Route::post('/subscribe', function (Request $request) {
    return $request->user()
        ->newSubscription('default', config('services.stripe.price_id'))
        ->checkout([
            'success_url' => route('generate') . '?upgraded=1',
            'cancel_url'  => route('premium'),
        ]);
})->middleware('auth')->name('subscribe');
