<x-mail::message>
{{-- # Vendor Application Status --}}
<h1 style="text-align: center; font-size: 24px;">Vendor Application Status</h1>

@if ($status === \App\Enums\VendorStatusEnum::Approved->value)
<div style="padding: 5% 1%; color: white; background-color: #60cb0b; border-radius: 5px; margin-bottom: 20px;" >
    We are excited to have you onboard as our new vendor! <br>
    Don't forget to update your store information in your profile page.
</div>
@elseif ($status === \App\Enums\VendorStatusEnum::Rejected->value)
<div style="padding: 5% 1%; color: white; background-color: #fd4747; border-radius: 5px; margin-bottom: 20px;" >
    We regret to inform that your vendor application have been rejected. <br>
    For further information on your vendor application, please contact us.
</div>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
