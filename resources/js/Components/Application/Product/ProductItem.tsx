import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import {Product} from "@/types";
import { Link, useForm } from "@inertiajs/react";

function ProductItem({ product }: { product: Product}) {

    const form = useForm<{
        option_ids: Record<string, number>;
        quantity: number;
    }>({
        option_ids: {},
        quantity: 1,
    });

    const addToCart = () => {

        form.post(route('cart.store', product.id), {
            preserveScroll: true,
            preserveState: true,
            onError: (err) => {
                console.log(err);
            }
        })
    };
    
    return (
        <div className="bg-base-100 card shadow-xl">
            <Link href={route('product.show', product.slug)}>
                <figure>
                    <img src={product.image} alt={product.title} className="aspect-square object-cover rounded-t-md" />
                </figure>
            </Link>

            <div className="card-body">
                <h2 className="card-title">{product.title}</h2>

                <p>
                    by <Link href={route('vendor.profile', product.user.store_name)} 
                        className="hover:underline" >
                        {product.user.name}
                    </Link>
                    &nbsp;
                    in <Link href={route('product.byDepartment', product.department.slug)} 
                        className="hover:underline" >
                        {product.department.name}
                    </Link>
                </p>

                <div className="card-actions justify-between items-center mt-3">
                    <button onClick={addToCart} className="btn btn-primary">
                        Add to Cart
                    </button>

                    <span className="text-2xl">
                        <CurrencyFormatter amount={product.price} />
                    </span>
                </div>
            </div>
        </div>
    );
}

export default ProductItem;