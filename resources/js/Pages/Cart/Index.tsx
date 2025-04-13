import CartItem from "@/Components/Application/Cart/CartItem";
import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import InputError from "@/Components/Core/InputError";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { GroupedCartItems, PageProps } from "@/types";
import { CreditCardIcon, XMarkIcon } from "@heroicons/react/24/outline";
import { Head, Link } from "@inertiajs/react";
import { useState } from "react";

function Index({
    csrf_token,
    cartItems,
    totalQuantity,
    totalPrice,
    errors,
}: PageProps<{ cartItems: Record<number, GroupedCartItems>; errors: {[key: string]: string}; }>) {

    // Whether the customer decides to checkout for a specific vendor only
    const [VendorId, setVendorId] = useState<number | null>(null);

    console.log(errors);
    
    return (
        <AuthenticatedLayout>
            <Head title="Cart Checkout" />

            <div className="container mx-auto flex flex-col gap-4 p-8 lg:flex-row">
                {/* LEFT SIDE */}
                <div className="card flex-1 order-2 bg-white dark:bg-gray-800 lg:order-1">
                    <div className="card-body">
                        <h2 className="font-boo text-lg">
                            Shopping Cart
                        </h2>

                        <div className="my-4">
                            {/* Check if the cart is empty */}
                            {Object.keys(cartItems).length === 0 && (
                                <div className="py-2 text-center text-gray-500">
                                    Cart is currently empty.
                                </div>
                            )}

                            {/* cartItem has the data structure defined for the cartItemData in CartService */}
                            {/* 
                                NOTE:
                                In the case of Object.values(), TypeScript essentially sees that you're extracting values from an object, 
                                but it doesn't automatically propagate the precise GroupedCartItems type without your explicit assertion. 
                            */}
                            {Object.values(cartItems as Record<number, GroupedCartItems>).map(cartItem => (
                                <div key={cartItem.user.id}>
                                    <div className="flex justify-between items-center pb-4 mb-4 border-b border-gray-300">
                                        <Link href="/" className="underline" >
                                            {cartItem.user.name}
                                        </Link>

                                        <div>
                                            <button className="btn btn-ghost btn-sm" 
                                                onClick={()=> {
                                                    setVendorId(cartItem.user.id);
                                                    const modal = document.getElementById('checkout_modal') as HTMLDialogElement | null;
                    
                                                    if (modal) {
                                                        modal.showModal();
                                                    } else {
                                                    console.error("Modal element with ID 'order_detail' not found.");
                                                    }
                                                }}>
                                                <CreditCardIcon className="size-6" />
                                                Pay only for this seller
                                            </button>
                                        </div>
                                    </div>
                                
                                    {cartItem.items.map(item => (
                                        <CartItem item={item} key={item.id} />
                                    ))}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* RIGHT SIDE */}
                <div className="card bg-white dark:bg-gray-800 lg:min-w-[260px] order-1 lg:order-2">
                    <div className="card-body">
                        Subtotal ({totalQuantity} items): &nbsp;
                        <CurrencyFormatter amount={totalPrice} />

                        <PrimaryButton className={"rounded-full " + (totalQuantity === 0 ? 'btn-disabled' : '')} 
                            onClick={()=> {
                                setVendorId(null); // No specific seller
                                const modal = document.getElementById('checkout_modal') as HTMLDialogElement | null;

                                if (modal) {
                                    modal.showModal();
                                } else {
                                console.error("Modal element with ID 'order_detail' not found.");
                                }
                            }}>
                            <CreditCardIcon className="size-6 md:hidden xl:block" />
                            Proceed to Checkout
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            {/* Checkout Modal */}
            {totalQuantity !== 0 && 
                <dialog id="checkout_modal" className="modal modal-top md:modal-middle">
                    <div className="modal-box w-screen bg-slate-100 md:max-w-4xl">
                        <h3 className="border-b-primary flex justify-between items-center pb-1 text-lg font-bold border-b-2">
                            <div>Address Details</div>

                            <form method="dialog">
                                {/* if there is a button in form, it will close the modal */}
                                <button className="btn"><XMarkIcon className="size-5"/></button>
                            </form>
                        </h3>

                        <div className="modal-action">
                            <form action={route('cart.checkout')} method="POST" className="w-full flex flex-col gap-5" >
                                <input type="hidden" name="_token" value={csrf_token} />
                                {VendorId && 
                                    <input type="hidden" name="vendor_id" value={VendorId} />
                                }

                                <div className="w-full flex justify-around items-center p-3 bg-indigo-600 rounded-md">
                                    <div className="text-lg text-white">Autofill from your saved address</div>
                                    <button className="btn btn-primary tracking-wide text-white">Autofill</button>
                                </div>

                                <label htmlFor="fullname">
                                    <div className="font-semibold">Full-name (First and Last name)</div>
                                    <input type="text" name="fullname" id="fullname" className="input input-primary w-full" required />
                                    <InputError message={errors.fullname} className="mt-2" />
                                </label>

                                <label htmlFor="phone">
                                    <div className="font-semibold">Phone number</div>
                                    <input type="tel" name="phone" id="phone" className="input input-primary w-full" required />
                                    <InputError message={errors.phone} className="mt-2" />
                                    <div className="mt-1 text-xs font-medium">Possibly contacted for delivery purposes</div>
                                </label>

                                <label htmlFor="address_line_1">
                                    <div className="font-semibold">Address Line 1</div>
                                    <input type="text" name="address_line_1" id="address_line_1" className="input input-primary w-full" placeholder="Street address or P.O. Box" required />
                                    <InputError message={errors.address_line_1} className="mt-2" />
                                </label>
                                <label htmlFor="address_line_2">
                                    <div className="font-semibold">Address Line 2</div>
                                    <input type="text" name="address_line_2" id="address_line_2" className="input input-primary w-full" placeholder="Apt, suite, unit, building, floor, etc." />
                                    <InputError message={errors.address_line_2} className="mt-2" />
                                </label>

                                <div className="w-full flex flex-col gap-2 md:flex-row">
                                    <label htmlFor="city">
                                        <div className="font-semibold">City</div>
                                        <input type="text" name="city" id="city" className="input input-primary" placeholder="Jakarta, Tangerang, Bandung, etc." />
                                        <InputError message={errors.city} className="mt-2" />
                                    </label>
                                    <label htmlFor="province">
                                        <div className="font-semibold">Province</div>
                                        <input type="text" name="province" id="province" className="input input-primary" placeholder="DKI Jakarta, Banten, Jawa Barat, etc." />
                                        <InputError message={errors.province} className="mt-2" />
                                    </label>
                                    <label htmlFor="postal_code">
                                        <div className="font-semibold">Postal Code</div>
                                        <input type="text" name="postal_code" id="postal_code" className="input input-primary" placeholder="#####" />
                                        <InputError message={errors.postal_code} className="mt-2" />
                                    </label>
                                </div>

                                <label htmlFor="instructions">
                                    <div className="font-semibold">Additional Notes</div>
                                    <InputError message={errors.instructions} className="mt-2" />
                                    <textarea name="instructions" id="instructions" className="textarea w-full h-24" placeholder="Leave a note for your courier..."></textarea>
                                    <div className="text-xs font-light">(Optional)</div>
                                </label>

                                <div>
                                    <input type="checkbox" name="save_address" id="save_address" />
                                    <label htmlFor="save_address" className="ml-1 text-sm">Save as default address</label>
                                </div>

                                <div className="w-full flex justify-center mt-2">
                                    <PrimaryButton className="w-fit rounded-full">Confirm Checkout</PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </dialog>
            }
        </AuthenticatedLayout>
    );
}

export default Index;