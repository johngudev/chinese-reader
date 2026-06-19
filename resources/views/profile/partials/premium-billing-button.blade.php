<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Billing') }}
        </h2>

        <p class="my-4 text-sm text-gray-600">
            {{ __('Manage your billing information and subscription plan in Stripe.') }}
        </p>
    </header>

    <a href="{{ route('billing') }}">
        <x-primary-button
            x-data=""
        >{{ __('Manage Billing') }}</x-primary-button>
    </a>
</section>