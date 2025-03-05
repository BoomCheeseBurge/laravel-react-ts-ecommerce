<x-mail::message>
<h1 style="text-align: center; font-size: 24px;">Payment was Completed Successfully</h1>

@foreach ($orders as $order)
<h3 style="font-size: 20px; margin-bottom: 5px;">Vendor Order Details</h3>
<x-mail::table>
    <table>
        <tbody>
            <tr>
                <td>Seller</td>
                <td>
                    <a href="{{ route('vendor.profile', $order->vendorUser->vendor->store_name) }}">
                        {{ $order->vendorUser->vendor->store_name }}
                    </a>
                </td>
            </tr>
            <tr>
                <td>Order #</td>
                <td>#{{ $order->id }}</td>
            </tr>
            <tr>
                <td>Item Quantity</td>
                <td>{{ $order->orderItems->count() }}</td>
            </tr>
            <tr>
                <td>Total Price</td>
                <td>{{ Number::currency($order->total_price) }}</td>
            </tr>
        </tbody>
    </table>
</x-mail::table>

<div style="width: 100%; border: 1px solid gray;"></div>

<h3 style="font-size: 20px; margin-bottom: 5px;">Order Item Details</h3>
<x-mail::table>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $orderItem)
                <tr>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td padding="5" style="padding: 5px;">
                                        <img src="{{ $orderItem->product->getImageForOptions($orderItem->variation_type_option_ids) }}" alt="{{ $orderItem->product->title }} image" style="min-width: 60px; max-width: 60px;">
                                    </td>
                                    <td style="font-size: 13px; padding: 5px;">
                                        {{ $orderItem->product->title }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        {{ $orderItem->quantity }}
                    </td>
                    <td>
                        {{ Number::currency($orderItem->price) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-mail::table>

<x-mail::button :url="$order->id">
    View Order Details
</x-mail::button>

<hr>
@endforeach

<x-mail::subcopy>
    Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellat fuga qui ipsum quod pariatur doloremque necessitatibus neque? Fugit, odit. Explicabo, asperiores repellendus. Impedit similique officiis maxime quibusdam corrupti veritatis nihil.Provident similique officiis aspernatur aliquam quis, possimus inventore laudantium consequatur perferendis doloremque. Expedita at tenetur vitae quisquam consequatur quod sunt. Laboriosam illo minima suscipit, eaque cumque earum repudiandae incidunt consequuntur!
</x-mail::subcopy>

<x-mail::panel>
    Lorem ipsum dolor sit amet consectetur adipisicing elit. Nisi, nobis dicta! Ad voluptates qui quo, repellat omnis eveniet sequi accusamus porro dicta hic, modi aut aspernatur, voluptas corrupti cumque doloribus.
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>