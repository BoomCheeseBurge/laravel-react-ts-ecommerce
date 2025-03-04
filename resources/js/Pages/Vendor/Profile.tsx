import ProductItem from "@/Components/Application/Product/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, PaginationProps, PexelsImage, Product, Vendor } from "@/types";
import { Head } from "@inertiajs/react";

function Profile({ 
    appName, 
    vendor, 
    products, 
    storeImg 
}: PageProps<{ 
    vendor: Vendor, 
    products: PaginationProps<Product>,
    storeImg: PexelsImage, 
}>) {
        
    return (
        <AuthenticatedLayout>
            <Head>
                {/* For SEO */}
                <title>{vendor.store_name + ' Profile'}</title>
                <meta name="title" content={vendor.store_name + ' Profile'} />
                <meta name="description" content={'Larastore store page of ' + vendor.store_name} />
                <link rel="canonical" href={route('vendor.profile', vendor.store_name)} />

                <meta property="og:title" content={vendor.store_name + ' Profile'} />
                <meta property="og:description" content={'Larastore store page of ' + vendor.store_name} />
                <meta property="og:url" content={route('vendor.profile', vendor.store_name)} />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content={appName} />
            </Head>

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