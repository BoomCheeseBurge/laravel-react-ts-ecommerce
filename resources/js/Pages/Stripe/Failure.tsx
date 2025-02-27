import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { XCircleIcon } from "@heroicons/react/24/outline";
import { Head, Link } from "@inertiajs/react";

function Failure() {
    
    return (
        <AuthenticatedLayout>
            <Head title="Payment Failed" />

            <div className="mx-auto py-8 px-4 w-[500px]">
                <div className="flex flex-col items-center gap-2">
                    <div className="text-6xl text-red-600">
                        <XCircleIcon className="size-24" />
                    </div>

                    <div className="text-3xl">Payment Failed</div>
                </div>

                <div className="my-8 text-lg">
                    Something went wrong with your payment process. Please try again.
                </div>

                <div className="flex justify-center">
                    <Link href={route('cart.index')} className="btn btn-primary" >
                        Return to Cart Checkout
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Failure;