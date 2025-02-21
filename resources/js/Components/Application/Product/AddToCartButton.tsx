import { ComputedProduct, FormData } from "@/types";
import { InertiaFormProps } from "@inertiajs/react";

function AddToCartButton({ 
    form, 
    onQuantityChange, 
    computedProduct, 
    addToCart 
}: { 
    form: InertiaFormProps<FormData>,
    onQuantityChange: (event: React.ChangeEvent<HTMLSelectElement>) => void,
    computedProduct: ComputedProduct,
    addToCart: () => void
}) {
    return (
        <div className="flex gap-4 mt-4 mb-8">
            <select name="" id="" value={form.data.quantity} 
                onChange={onQuantityChange} 
                className="select select-bordered w-full" 
            >
                {/* Customer can only purchase a maximum of 10 quantities of a single product at a time */}
                {Array.from({
                    length: Math.min(10, computedProduct.quantity)
                }).map((el, i) => (
                    
                    <option key={i + 1} value={i + 1} >Quantity: {i + 1}</option>
                ))
                }
            </select>

            <button 
                onClick={addToCart}
                className="btn btn-primary">
                Add to Cart
            </button>
        </div>
    );
}

export default AddToCartButton;