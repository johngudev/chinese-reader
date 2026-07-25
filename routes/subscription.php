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
| "web" middleware group. Stripe price ids come from config/services.php.
|
| Monthly and annual share one Cashier subscription slot named "default",
| so User::isBilled() works unchanged for both. A monthly subscriber can
| later move to annual with subscription('default')->swap($annualPriceId).
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
    // Anything that isn't exactly "annual" falls back to monthly, so a
    // malformed or tampered POST can never reach Stripe with a null price.
    $isAnnual = $request->input('plan') === 'annual';

    $priceId = $isAnnual
        ? config('services.stripe.price_annual')
        : config('services.stripe.price_id');

    abort_unless($priceId, 500, 'Stripe price id is not configured.');

    return $request->user()
        ->newSubscription('default', $priceId)
        ->checkout([
            'success_url' => route('generate') . '?upgraded=1',
            'cancel_url'  => route('premium'),
        ]);
})->middleware('auth')->name('subscribe');
