import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Order, PageProps, VariationOption } from "@/types";
import { CheckCircleIcon } from "@heroicons/react/24/outline";
import { Head, Link } from "@inertiajs/react";
import { useState } from "react";

function Success({ orders, variationOptions }: PageProps<{ orders: Order[], variationOptions: VariationOption[] }>
) {
    const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
    
    const handleViewDetailsClick = (orderId: number) => {
        const orderToView = orders.find(order => order.id === orderId);

        if(orderToView) {
            setSelectedOrder(orderToView);
    
            const modal = document.getElementById('order_detail') as HTMLDialogElement | null;
            if (modal) {
              modal.showModal();
            } else {
              console.error("Modal element with ID 'order_detail' not found.");
            }
        } else {
        console.error(`Order with ID ${orderId} not found.`);
      }
    };
        
    return (
        <AuthenticatedLayout>
            <Head title="Completed Payment" />

            <div className="mx-auto py-8 px-4 w-[480px]">
                <div className="flex flex-col items-center gap-2">
                    <div className="text-6xl text-emerald-600">
                        <CheckCircleIcon className="size-24" />
                    </div>

                    <div className="text-3xl">Your payment was completed</div>
                </div>

                <div className="my-6 text-lg">
                    Thank you for your purchase! Your payment was completed successfully.
                </div>

                {orders.map(order => (
                    <div key={order.id} className="p-6 mb-4 bg-white rounded-lg dark:bg-gray-800">
                        <h3 className="mb-3 text-3xl">Order Summary</h3>

                        <div className="flex justify-between mb-2 font-bold">
                            <div className="text-gray-400">
                                Seller
                            </div>

                            <div>
                                <Link href={route('vendor.profile', order.vendorUser.store_name)} className="hover:underline" >
                                    {order.vendorUser.store_name}
                                </Link>
                            </div>
                        </div>

                        <div className="flex justify-between mb-2">
                            <div className="text-gray-400">
                                Order Number
                            </div>

                            <div>
                                <Link href="#" className="hover:underline" >
                                    #{order.id}
                                </Link>
                            </div>
                        </div>

                        <div className="flex justify-between mb-3">
                            <div className="text-gray-400">
                                Total Items
                            </div>

                            <div>
                                {order.orderItems.length}
                            </div>
                        </div>

                        <div className="flex justify-between mb-3">
                            <div className="text-gray-400">
                                Total Price
                            </div>

                            <div>
                                <CurrencyFormatter amount={order.total_price} />
                            </div>
                        </div>

                        <div className="flex justify-between mt-4">
                            <div className="btn btn-primary" onClick={()=>handleViewDetailsClick(order.id)}>
                                View Order Details
                            </div>

                            <Link href={route('home')} className="btn" >
                                Back to Home
                            </Link>
                        </div>
                    </div>
                ))}
            </div>

            <dialog id="order_detail" className="modal">
                <div className="modal-box">
                    <h3 className="text-lg font-bold">Order Details</h3>
                    <div>
                        {selectedOrder && 
                            selectedOrder.orderItems.map( item => (
                                <div key={item.id} className="bg-base-100 card w-full shadow-sm">
                                    <figure>
                                        <img 
                                            className="mx-auto h-60"
                                            src={item.product.image}
                                            alt={item.product.title + " image"} />
                                    </figure>

                                    <div className="card-body">
                                        <h2 className="card-title">
                                            {item.product.title}

                                            {item.variation_type_option_ids && Object.keys(item.variation_type_option_ids).length > 0 ? (
                                                <div className="flex gap-2 my-3">
                                                    {Object.values(item.variation_type_option_ids).map(option => (
                                                    <div key={option} className="from-slate-700 to-gray-800 px-3 py-1 text-sm font-semibold tracking-wider text-amber-500 bg-gradient-to-r rounded-3xl drop-shadow-md select-none dark:from-amber-500 dark:to-orange-600 dark:font-medium dark:text-slate-900 dark:shadow-stone-500">
                                                        {variationOptions[option].name}
                                                    </div>
                                                    ))}
                                                </div>
                                            ) : null}
                                        </h2>

                                        <div className="card-actions justify-start">
                                            <div className="badge badge-outline">{item.product.departmentName}</div>
                                        </div>

                                        <div>
                                            <div>
                                                <span className="font-light">QTY:</span> <span className="text-lg font-semibold">{item.quantity}</span>
                                            </div>
                                            <div>
                                                <span className="font-light">PRICE:</span>&nbsp;
                                                <span className="text-lg font-semibold">
                                                    <CurrencyFormatter amount={item.price} />
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        ))}
                    </div>

                    <div className="modal-action">
                        <form method="dialog">
                            {/* if there is a button in form, it will close the modal */}
                            <button className="btn">Close</button>
                        </form>
                    </div>
                </div>
            </dialog>
        </AuthenticatedLayout>
    );
}

export default Success;