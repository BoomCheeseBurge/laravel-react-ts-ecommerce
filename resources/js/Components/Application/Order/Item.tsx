import { OrderItem, VariationOption } from "@/types";
import { Link } from "@inertiajs/react";

function Item({ 
    item, 
    created_at, 
    variationOptions, 
    deliveryStatuses, 
}: { item: OrderItem; created_at: string; variationOptions: VariationOption[]; deliveryStatuses: string[]; }
) {

    // Informs TypeScript that this object must conform to the structure defined by that interface
    let options: Intl.DateTimeFormatOptions = {
        year: "numeric",
        month: "long",
        day: "numeric",
    };

    // Date formatter
    let dateFormatter = new Intl.DateTimeFormat("en-US", options);

    // Delivery Status
    let status: string = item.address.status;

    const stepActivationStatuses: {[key: number]: string[];} = {
        0: [deliveryStatuses[0], deliveryStatuses[1], deliveryStatuses[2], deliveryStatuses[3]], // Order Received
        1: [deliveryStatuses[1], deliveryStatuses[2], deliveryStatuses[3]], // Order Processed
        2: [deliveryStatuses[2], deliveryStatuses[3]], // In Delivery
        3: [deliveryStatuses[3]], // Delivered
    };
    
    const getStepClass = (stepIndex: number, currentStatus: string) => {
        let className = "step";

        if (stepActivationStatuses[stepIndex] && stepActivationStatuses[stepIndex].includes(currentStatus)) {
            className += " step-primary";
        }
        
        return className;
    };
    
    return (
        <div className="bg-base-100 card w-full mb-5 shadow-sm">
            <div className="bg-primary/80 flex justify-between px-10 py-3 mb-2 rounded-t-md max-sm:flex-col max-sm:gap-5">
                {/* Left Side */}
                <div className="flex gap-5">
                    <div>
                        <div className="text-lg font-light text-white">ORDERED ON</div>
                        <div className="text-xl font-bold">
                        {dateFormatter.format(new Date(created_at))}
                        </div>
                    </div>
                    <div>
                        <div className="text-lg font-light text-white">TOTAL PRICE</div>
                        <div className="text-xl font-bold">$13.99</div>
                    </div>
                </div>
                {/* Right Side */}
                <div className="flex flex-col">
                    <div className="text-lg text-white">Order #</div>
                    <div className="flex gap-3">
                        <button className="underline hover:no-underline" type="button">View Order Details</button>
                        <button className="underline hover:no-underline" type="button">View Invoice</button>
                    </div>
                </div>
            </div>

            {/* Order Progress */}
            <ul className="steps my-10 [&>li]:font-semibold">
                <li className={getStepClass(0, status)} >
                    Order Received
                </li>
                <li className={getStepClass(1, status)} >
                    Order Processed
                </li>
                <li className={getStepClass(2, status)} >
                    In Delivery
                </li>
                <li className={getStepClass(3, status)} >
                    Delivered
                </li>
            </ul>

            <figure>
                <img 
                className="max-h-60"
                src={item.product.image}
                alt={item.product.title + ' image'} />
            </figure>

            <div className="card-body">
                <Link href={route('product.show', item.product.slug)} className="card-title text-blue-600 underline hover:no-underline max-sm:mx-auto max-sm:mb-3">
                    {item.product.title}
                </Link>

                {item.variation_type_option_ids && Object.keys(item.variation_type_option_ids).length > 0 ? (
                    <div className="flex gap-2 my-3">
                        {Object.values(item.variation_type_option_ids).map(option => (
                        <div key={option} className="from-slate-700 to-gray-800 px-3 py-1 text-sm font-semibold tracking-wider text-amber-500 bg-gradient-to-r rounded-3xl drop-shadow-md select-none dark:from-amber-500 dark:to-orange-600 dark:font-medium dark:text-slate-900 dark:shadow-stone-500">
                            {variationOptions[option].name}
                        </div>
                        ))}
                    </div>
                ) : null}

                <div className="card-actions justify-center">
                    {/* Only show review button when the package has arrived at destination */}
                    {item.address.status === deliveryStatuses[3] && 
                        <button className="btn btn-primary text-white">Leave a Review</button>
                    }
                    <button className="btn btn-primary text-white">Buy Again</button>
                </div>
            </div>
        </div>
    );
}

export default Item;