import ProductItem from "@/Components/Application/Product/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, PaginationProps, Product, Vendor } from "@/types";
import { Head } from "@inertiajs/react";

function Profile({ 
    vendor, products, 
}: PageProps<{ 
    vendor: Vendor, 
    products: PaginationProps<Product>, 
}>) {
    
    return (
        <AuthenticatedLayout>
            <Head title={vendor.store_name + ' Profile'} />

            <div className="hero min-h-[320px]" 
                style={{ 
                    backgroundImage: "url(https://picsum.photos/1920/1080)"
                 }}
            >
                <div className="bg-opacity-60 hero-overlay"></div>

                <div className="hero-content text-neutral-content text-center">
                    <div className="max-w-md">
                        <h1 className="font- mb-5 text-5xl">
                            {vendor.store_name}
                        </h1>
                    </div>
                </div>
            </div>

            <div className="container mx-auto">
                <div className="grid grid-cols-1 gap-8 p-8 lg:grid-cols-3 md:grid-cols-2">
                    {products.data.map(product => (
                        <ProductItem key={product.id} product={product} />
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Profile;