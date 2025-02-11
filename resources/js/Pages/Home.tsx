import ProductItem from '@/Components/Application/Product/ProductItem';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, PaginationProps, Product } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Home({
    products
}: PageProps<{ products: PaginationProps<Product> }>) {

    return (
        <AuthenticatedLayout>
            <Head title="Home" />

            <div className="bg-gray-200 dark:bg-gray-700 hero h-[24rem]">
                <div className="hero-content text-center">
                    <div className="max-w-md">
                        <h1 className="text-5xl font-bold">Hello there</h1>

                        <p className="py-6">
                            Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem
                            quasi. In deleniti eaque aut repudiandae et a id nisi.
                        </p>
                        
                        <button className="btn btn-primary">Get Started</button>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-8 p-8 lg:grid-cols-3 md:grid-cols-2">
                {products.data.map(product => (
                    <ProductItem product={product} key={product.id} />
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
