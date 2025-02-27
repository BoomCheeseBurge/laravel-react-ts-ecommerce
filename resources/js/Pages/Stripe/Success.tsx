import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Order, PageProps } from "@/types";
import { CheckCircleIcon } from "@heroicons/react/24/outline";
import { Head, Link } from "@inertiajs/react";

function Success({ orders }: PageProps<{ orders: Order[] }>
) {
    
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
                                <Link href="#" className="hover:underline" >
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
                            <Link href="#" className="btn btn-primary" >
                                View Order Details
                            </Link>

                            <Link href={route('home')} className="btn" >
                                Back to Home
                            </Link>
                        </div>
                    </div>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}

export default Success;