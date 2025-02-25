import CartItem from "@/Components/Application/Cart/CartItem";
import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { GroupedCartItems, PageProps } from "@/types";
import { CreditCardIcon } from "@heroicons/react/24/outline";
import { Head, Link } from "@inertiajs/react";

function Index({
    csrf_token,
    cartItems,
    totalQuantity,
    totalPrice
}: PageProps<{ cartItems: Record<number, GroupedCartItems> }>) {
    
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
                            {Object.values(cartItems).map(cartItem => (
                                <div key={cartItem.user.id}>
                                    <div className="flex justify-between items-center pb-4 mb-4 border-b border-gray-300">
                                        <Link href="/" className="underline" >
                                            {cartItem.user.name}
                                        </Link>

                                        <div>
                                            <form action={route('cart.checkout')} method="POST">
                                                <input type="hidden" name="_token" value={csrf_token} />
                                                <input type="hidden" name="vendor_id" value={cartItem.user.id} />

                                                <button className="btn btn-ghost btn-sm">
                                                    <CreditCardIcon className="size-6" />

                                                    Pay only for this seller
                                                </button>
                                            </form>
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

                        <form action={route('cart.checkout')} method="POST" >
                            <input type="hidden" name="_token" value={csrf_token} />

                            <PrimaryButton className="rounded-full">
                                <CreditCardIcon className="size-6" />
                                Proceed to Checkout
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Index;