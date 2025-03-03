import ProductItem from "@/Components/Application/Product/ProductItem";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Department, PageProps, PaginationProps, Product } from "@/types";
import { Head } from "@inertiajs/react";

function Index({ 
    appName,
    department,
    products,
}: PageProps<{
    department: Department,
    products: PaginationProps<Product> 
}>){
    
    return (
        <AuthenticatedLayout>
            <Head>
                {/* For SEO */}
                <title>{department.name}</title>
                <meta name="title" content={department.meta_title} />
                <meta name="description" content={department.meta_description} />
                <link rel="canonical" href={route('product.byDepartment', department.slug)} />

                <meta property="og:title" content={department.meta_title} />
                <meta property="og:description" content={department.meta_description} />
                <meta property="og:url" content={route('product.byDepartment', department.slug)} />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content={appName} />
            </Head>

            <div className="container mx-auto">
                {/* Display department name */}
                <div className="hero bg-base-200 min-h-[120px]">
                    <div className="hero-content text-center">
                        <div className="max-w-lg">
                            <h1 className="text-5xl font-bold">
                                {department.name}
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

                <div className="grid grid-cols-1 gap-8 p-8 lg:grid-cols-3 md:grid-cols-2">
                    {products.data.map(product => (
                        <ProductItem key={product.id} product={product} />
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Index;