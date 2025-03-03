import ProductItem from "@/Components/Application/Product/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, PaginationProps, PexelsImage, Product, Vendor } from "@/types";
import { Head } from "@inertiajs/react";

function Profile({ 
    vendor, products, storeImg
}: PageProps<{ 
    vendor: Vendor, 
    products: PaginationProps<Product>,
    storeImg: PexelsImage, 
}>) {
        
    return (
        <AuthenticatedLayout>
            <Head title={vendor.store_name + ' Profile'} />

            <div className="hero min-h-[320px]" 
                style={{ 
                    backgroundImage: `url(${storeImg.src.original})`,
                    backgroundSize: 'cover',
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
            
            {/* Display message if there are no products related to the department */}
            {products.data.length === 0 && (
                <div className="px-8 py-16 text-3xl text-center text-gray-300">
                    No products found
                </div>
            )}

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