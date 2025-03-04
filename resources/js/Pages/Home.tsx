import ProductItem from '@/Components/Application/Product/ProductItem';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, PaginationProps, Product } from '@/types';
import { Head } from '@inertiajs/react';

export default function Home({
    appName,
    products
}: PageProps<{ products: PaginationProps<Product> }>) {

    return (
        <AuthenticatedLayout>
            <Head>
                {/* For SEO */}
                <title>{'Home'}</title>
                <meta name="title" content={'home'} />
                <meta name="description" content={'larastore homepage'} />
                <link rel="canonical" href={route('home')} />

                <meta property="og:title" content={'home'} />
                <meta property="og:description" content={'larastore homepage'} />
                <meta property="og:url" content={route('home')} />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content={appName} />
            </Head>

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

            {/* Display message if there are no products related to the department */}
            {products.data.length === 0 && (
                <div className="px-8 py-16 text-3xl text-center text-gray-300">
                    No products found
                </div>
            )}

            <div className="grid grid-cols-1 gap-8 p-8 lg:grid-cols-3 md:grid-cols-2">
                {products.data.map(product => (
                    <ProductItem product={product} key={product.id} />
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
