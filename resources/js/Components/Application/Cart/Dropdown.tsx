import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import { showProductRoute } from "@/helpers";
import { Link, usePage } from "@inertiajs/react";

function Dropdown() {

    const {totalQuantity, totalPrice, dropdownCartItems} = usePage().props;
    
    return (
        <div className="dropdown dropdown-end">
            <div tabIndex={0} role="button" className="btn btn-circle btn-ghost">
                <div className="indicator">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span className="badge badge-sm indicator-item">
                        {totalQuantity}
                    </span>
                </div>
            </div>

            <div
                tabIndex={0}
                className="card card-compact dropdown-content bg-base-100 z-50 mt-3 w-[380px] shadow max-sm:-left-[16.5rem]">
                <div className="card-body">
                    <span className="text-lg font-bold">
                        {totalQuantity} Items
                    </span>

                    <div className="my-4 max-h-[300px] overflow-auto">
                        {dropdownCartItems.length === 0 && (
                            <div className="py-2 text-center text-gray-500">
                                Cart is currently empty.
                            </div>
                        )}

                        {dropdownCartItems.map((item) => (
                            <div key={item.id} className="flex gap-4 p-3">
                                <Link href={showProductRoute(item)} className="w-24 h-24 justify-center items-center">
                                    <img src={item.image} alt={item.title + ' image'} className="max-w-full max-h-full rounded-md" />
                                </Link>

                                <div className="flex-1">
                                    <h3 className="mb-3 font-semibold">
                                        <Link href={showProductRoute(item)}>
                                            {item.title}
                                        </Link>
                                    </h3>

                                    <div className="flex justify-between text-sm">
                                        <div>Quantity: {item.quantity}</div>
                                        <div><CurrencyFormatter amount={item.quantity * item.price} /></div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <span className="text-info mb-2 text-lg">
                        Subtotal: <CurrencyFormatter amount={totalPrice} />
                    </span>
                    <div className="card-actions">
                        <Link href={route('cart.index')} className="btn btn-block btn-primary">
                            View cart
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Dropdown;