<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;
use Inertia\Response;
use App\Models\Delivery;
use Illuminate\Http\Request;
use App\Enums\DeliveryStatusEnum;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\App;
use Filament\Notifications\Notification;
use App\Http\Resources\OrderViewResource;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::where('user_id', auth()->user()->id)
                        ->orderBy('created_at', 'desc')
                        ->with(['orderItems', 'vendorUser', 'orderItems.address'])
                        ->get();

        return Inertia::render('Order/Index', [
            'orders' => OrderViewResource::collection($orders)->collection->toArray(),
            'variationOptions' => VariationTypeOption::whereIn(
                'id',
                $orders->flatMap(fn($order) => $order->orderItems->flatMap(fn($orderItem) => array_values($orderItem->variation_type_option_ids)))
                    ->unique() // Add this line to get only unique IDs
                    ->toArray()
                )
                ->get()
                ->keyBy('id'),
            'locale' => App::getLocale(), // Get the current Laravel application locale
            'deliveryStatuses' => DeliveryStatusEnum::values(),
        ]);
    }

    public function update(Request $request)
    {
        $delivery = Delivery::findOrFail($request->input('id'));

        $delivery->update([
            'status' => $request->input('status'),
        ]);

        Notification::make()
            ->title('Status updated successfully')
            ->success()
            ->send();

        // Redirect back to the show order page
        return Inertia::location(route('filament.admin.resources.orders.view', ['record' => $request->input('order_id')]));
    }
}
