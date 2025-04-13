@php
    use App\Enums\DeliveryStatusEnum;
    $data = $getState();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="step-bar-container">
        @foreach ($data[0] as $delivery)
            <div class="delivery-wrapper">
                <div>Order Item #{{ $delivery->order_id }}</div>

                <div class="test-wrapper">
                    <div class="test-container">
                        <div class="test-icon-wrapper">
                            <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(1) ? 'test-content-filled' : 'test-content-empty' }}>1</div>
                            <div class="test-desc-container">
                                <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(1) ? 'test-desc-filled' : 'test-desc-empty' }}>Order Received</div>
                            </div>
                        </div>
    
                        <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(2) ? 'test-holder-filled' : 'test-holder-empty' }}></div>
                    </div>
                  
                    <div class="test-container">
                        <div class="test-icon-wrapper">
                            <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(2) ? 'test-content-filled' : 'test-content-empty' }}>2</div>
                            <div class="test-desc-container">
                                <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(2) ? 'test-desc-filled' : 'test-desc-empty' }}>Order Processed</div>
                            </div>
                        </div>
    
                        <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(3) ? 'test-holder-filled' : 'test-holder-empty' }}></div>
                    </div>
                  
                    <div class="test-container">
                        <div class="test-icon-wrapper">
                            <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(3) ? 'test-content-filled' : 'test-content-empty' }}>3</div>
                            <div class="test-desc-container">
                                <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(3) ? 'test-desc-filled' : 'test-desc-empty' }}>In Delivery</div>
                            </div>
                        </div>
    
                        <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(4) ? 'test-holder-filled' : 'test-holder-empty' }}></div>
                    </div>
    
                    <div class="test-container-end">
                        <div class="test-icon-wrapper">
                            <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(4) ? 'test-content-filled' : 'test-content-empty' }}>4</div>
                            <div class="test-desc-container">
                                <div class={{ DeliveryStatusEnum::from($delivery->status)->isStepActive(4) ? 'test-desc-filled' : 'test-desc-empty' }}>Delivered</div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action={{ route('order.update') }} method="post" class="status-form">
                    @csrf
                    <input type="hidden" name="id" value={{ $delivery->id }}>
                    <input type="hidden" name="order_id" value={{ $delivery->order_id }}>
                    @if (DeliveryStatusEnum::from($delivery->status)->value === "OrderReceived")
                        <input type="hidden" name="status" value={{ DeliveryStatusEnum::OrderProcessed->value }}>
                    @else
                        <input type="hidden" name="status" value={{ DeliveryStatusEnum::InDelivery->value }}>
                    @endif

                    @if (DeliveryStatusEnum::from($delivery->status)->value === "OrderReceived")
                        <button type="submit" class="status-btn"><span class="relative">Order Processed</span></button>
                    @else
                        <button type="submit" class="status-btn">In Delivery</button>                        
                    @endif
                </form>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
