@php
    $data = $getState();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">

    <div class="order-item-container">
  
        @foreach ($data[0] as $key => $item)
            <div class="order-item-card">
                <div>
                    <img src="{{ $data[2][$key] ?? $item->product->getFirstMediaUrl('images') }}" alt="Product Image" class="order-item-image">
                </div>

                <div>
                    <div class="item-title item-val">{{ $item->loadMissing('product')->product->title }}</div>
                    <div class="option-container">
                        {!! collect($item->variation_type_option_ids)->map(function ($optionId) use ($data) {

                            return '<div class="option-badge">' . $data[1]->get($optionId)->name . '</div>';
                        })->implode('') !!}
                    </div>
                </div>
                
                <div class="order-item-detail">
                    <div class="detail-box">
                        <div class="item-prop">Price</div>
                        <div class="item-val">{{ Number::currency($item->price) }}</div>
                    </div>
                    
                    <div class="detail-box">
                        <div class="item-prop">Quantity</div>
                        <div class="item-val">{{ $item->quantity }}</div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</x-dynamic-component>