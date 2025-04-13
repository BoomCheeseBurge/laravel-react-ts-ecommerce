import Item from "@/Components/Application/Order/Item";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Order, PageProps, VariationOption } from "@/types";
import { Head } from "@inertiajs/react";

function Index(
    { 
        orders, 
        variationOptions, 
        locale, 
        deliveryStatuses
    }: 
    PageProps<{ orders: Order[]; variationOptions: VariationOption[]; locale: string; deliveryStatuses: string[]; }>
) {

    return (
        <AuthenticatedLayout>
            <Head title="Your Orders" />

            <div className="container mx-auto [&>*]:mt-5 max-sm:mx-2">
                <h2 className="text-2xl font-bold">Your Orders</h2>

                <div className="tabs">
                    {/* Orders Tab */}
                    <input
                        type="radio"
                        name="order_tab"
                        className="tab text-base-content mr-2 bg-slate-100 rounded-t-md border-x-2 border-t-2 border-b-transparent checked:bg-primary checked:tracking-wider checked:text-white checked:border-none"
                        aria-label="Orders"
                        defaultChecked
                    />
                    <div className="border-base-300 tab-content p-6 bg-slate-100 border-t-2">
                        {orders.map(order => (
                            <div key={order.id}>
                                {order.orderItems.map((item) => (
                                    <Item key={item.id} item={item} created_at={order.created_at} deliveryStatuses={deliveryStatuses} variationOptions={variationOptions} />
                                ))}
                            </div>
                        ))}
                    </div>

                    {/* In Delivery Tab */}
                    <input
                        type="radio"
                        name="order_tab"
                        className="tab text-base-content mr-2 bg-slate-100 rounded-t-md border-x-2 border-t-2 border-b-transparent checked:bg-primary checked:tracking-wider checked:text-white checked:border-none"
                        aria-label="Cancelled"
                    />
                    <div className="border-base-300 tab-content p-6 bg-slate-100 border-t-2">Tab content 2</div>

                    {/* Cancelled Orders Tab */}
                    <input
                        type="radio"
                        name="order_tab"
                        className="tab text-base-content mr-2 bg-slate-100 rounded-t-md border-x-2 border-t-2 border-b-transparent checked:bg-primary checked:tracking-wider checked:text-white checked:border-none"
                        aria-label="Archived"
                    />
                    <div className="border-base-300 tab-content p-6 bg-slate-100 border-t-2">Tab content 3</div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Index;