<x-mail::message>
<h1 style="text-align: center; font-size: 24px;">Monthly payout has been sent to your Stripe account!</h1>

<x-mail::subcopy>
<div>Thank you for using our platform for your products, {{ $vendor->store_name }}!</div>
<br>
<div>Your earnings this month has been sent to your Stripe account.</div>
<br>
<div style="text-align: center; font-size: 2rem;">Subtotal: {{ $subtotal }}</div>
</x-mail::subcopy>

<x-mail::panel>
    <i>Please reach out to us at <a href="mailto:larastore@example.com">larastore@example.com</a> for any issues regarding the monthly payout.</i>
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>