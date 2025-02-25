import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import TextInput from "@/Components/Core/TextInput";
import { showProductRoute } from "@/helpers";
import { CartItem as CartItemType } from "@/types";
import { Link, router, useForm } from "@inertiajs/react";
import { useState } from "react";

function CartItem({ item }: { item: CartItemType}) {

    const deleteForm = useForm({
        option_ids: item.option_ids
    });
    
    const [error, setError] = useState('');

    const onDeleteClick = () => {

        deleteForm.delete(route('cart.destroy', item.product_id), {
            preserveScroll: true,
        });
    };

    const handleQuantityChange = (event: React.ChangeEvent<HTMLInputElement>) => {

        // Reset previous error state
        setError('');

        /**
         * Update the quantity for this cart item
         * 
         * Note: form is not used here for concern where quantity dynamically changes which requires set data on the form followed by the form HTTP request.
         *       However, the quantity value from form set data will NOT be reflected on the form HTTP request until the next component render
         */
        router.put(route('cart.update', item.product_id), {
            quantity: event.target.value,
            option_ids: item.option_ids
        }, {
            preserveScroll: true,
            onError: (errors) => {
                setError(Object.values(errors)[0])
            }
        })
    };

    return (
        <>
            <div className="flex gap-6 p-3">
                <Link href={showProductRoute(item)} className="min-h-36 min-w-36 w-36 flex justify-center self-start" >
                    <img src={item.image} alt={item.title + ' image'} className="max-w-full max-h-full rounded-sm" />
                </Link>

                <div className="flex flex-col flex-1">
                    <div className="flex-1">
                        <h3 className="mb-3 text-lg font-semibold hover:underline">
                            <Link href={showProductRoute(item)} >
                                {item.title}
                            </Link>
                        </h3>

                        <div className="text-sm">
                            {item.options.map(option => (
                                <div key={option.id} >
                                    <strong className="text-bold">
                                        {option.type.name}: &nbsp;
                                    </strong>
                                    {option.name}
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="flex justify-between items-center mt-6">
                        <div className="flex items-center gap-2">
                            <div className="text-base">Quantity:</div>

                            {/* Prevent quantity higher than the permissible quantity */}
                            <div className={error ? 'tooltip tooltip-error tooltip-open' : ''} data-tip={error} ></div>

                            <TextInput type="number" defaultValue={item.quantity} onBlur={handleQuantityChange} className="input-sm w-28 me-5" ></TextInput>

                            <button type="button" onClick={() => onDeleteClick()} className="btn btn-ghost btn-sm" >Delete</button>
                            <button type="button" className="btn btn-ghost btn-sm" >Checkout Later</button>
                        </div>

                        <div className="text-lg font-bold">
                            <CurrencyFormatter amount={item.price * item.quantity} />
                        </div>
                    </div>
                </div>
            </div>

            <div className="divider"></div>
        </>
    );
}

export default CartItem;