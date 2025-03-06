import AddToCartButton from "@/Components/Application/Product/AddToCartButton";
import ProductVariationTypes from "@/Components/Application/Product/ProductVariationTypes";
import Carousel from "@/Components/Core/Custom/Carousel";
import CurrencyFormatter from "@/Components/Core/Custom/CurrencyFormatter";
import { arraysAreEqual } from "@/helpers";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PageProps, Product, VariationTypeOption } from "@/types";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";

function Show({ 
    appName, 
    product, 
    variationOptions 
}: PageProps<{ 
    product: Product, 
    variationOptions: number[] 
}>) {
    /**
     * 
     * ██    ██  █████  ██████  ██  █████  ██████  ██      ███████ 
     * ██    ██ ██   ██ ██   ██ ██ ██   ██ ██   ██ ██      ██      
     * ██    ██ ███████ ██████  ██ ███████ ██████  ██      █████   
     *  ██  ██  ██   ██ ██   ██ ██ ██   ██ ██   ██ ██      ██      
     *   ████   ██   ██ ██   ██ ██ ██   ██ ██████  ███████ ███████ 
     * 
     */    
    // **************************************
    // Define the form for adding to cart
    const form = useForm<{
        /**
         * string: variation type id (e.g., "1" that refers to size)
         * number: variation type option id (e.g., 1 that refers to small)
         */
        option_ids: Record<string, number>;
        quantity: number;
        price: number | null; // null means the default product price is used
    }>({
        option_ids: {},
        quantity: 1,
        price: null,
    });
    // **************************************

    // Retrieve the URL of the current page (to append the selected options query parameter)
    const {url} = usePage();

    /**
     * number: variation type ID (e.g., size or color)
     * VariationTypeOption: all ids of variation type options of the associated variation type ID (e.g., small or red)
     * 
     * The selectedOptions will be used as query parameters in the URL
     */
    const [selectedOptions, setSelectedOptions] = useState<Record<number, VariationTypeOption>>([]);

    // console.log(selectedOptions);

    // ------------------------------------------------------------------------------------------------------------------------------------
    /**
     * 
     *   ███    ███ ███████ ███    ███  ██████  
     *   ████  ████ ██      ████  ████ ██    ██ 
     *   ██ ████ ██ █████   ██ ████ ██ ██    ██ 
     *   ██  ██  ██ ██      ██  ██  ██ ██    ██ 
     *   ██      ██ ███████ ██      ██  ██████  
     * 
     */

    /**
     * Cache images if no changes are observed from product and selectedOptions
     */
    const images = useMemo(() => {

        for (const typeId in selectedOptions) {

            // Retrieve the variation type option object
            const option = selectedOptions[typeId];

            /**
             * Check if the variation type option has an image attached to it
             * Whatever the option has the image attached to it, return the images that the option has 
             * 
             * For example, option here refers to variation option 'black' of variation type 'color'
             * Or it can also be variation option 'small' of variation type 'size'
             * 
             * If variation option 'black' has an image associated with it, then directly return that variation option associated images, 
             * otherwise, keep looping to the next variation option
             */
            if (option.images.length > 0) return option.images;
        };

        // Else, return the main product images
        return product.images;

    }, [product, selectedOptions]);

    /**
     * Cache price and quantity of the product of the selected variation type option
     */
    const computedProduct = useMemo(() => {

        /**
         * The IDs will be sorted in pairs
         */
        const selectedOptionIds = Object.values(selectedOptions).map(option => option.id).sort();

        // console.log(product.variations.length);

        /**
         * Loop through existing variation type options
         * to set existing quantity and price of the variation type options
         */
        for (const variation of product.variations) {
            
            const optionIds = variation.variation_type_option_ids.sort();
            // console.log(optionIds);

            // Check if the selected variation type options matches any of the current indexed variation type options
            if (arraysAreEqual(selectedOptionIds, optionIds)) {

                /**
                 * If quantity is not set, then set quantity as maximum number provided from ES6 Number object
                 */
                return {
                    price: variation.price,
                    quantity: variation.quantity === null ? Number.MAX_VALUE : variation.quantity
                }
            }
        }

        // Else, set default quantity and price using the product's value
        return {
            price: product.price,
            quantity: product.quantity
        };

    }, [product, selectedOptions]);

    // ------------------------------------------------------------------------------------------------
    /**
     * 
     *   ██   ██ ███████ ██      ██████  ███████ ██████  
     *   ██   ██ ██      ██      ██   ██ ██      ██   ██ 
     *   ███████ █████   ██      ██████  █████   ██████  
     *   ██   ██ ██      ██      ██      ██      ██   ██ 
     *   ██   ██ ███████ ███████ ██      ███████ ██   ██ 
     * 
     *   ███████ ██    ██ ███    ██  ██████ ████████ ██  ██████  ███    ██ 
     *   ██      ██    ██ ████   ██ ██         ██    ██ ██    ██ ████   ██ 
     *   █████   ██    ██ ██ ██  ██ ██         ██    ██ ██    ██ ██ ██  ██ 
     *   ██      ██    ██ ██  ██ ██ ██         ██    ██ ██    ██ ██  ██ ██ 
     *   ██       ██████  ██   ████  ██████    ██    ██  ██████  ██   ████ 
     *
     */

    // newOptions will have the same object format as 'selectedOptions' above
    const getOptionIdsMap = (newOptions: object) => {

        // Creates a new object from a list of key/value pairs
        return Object.fromEntries(
            /**
             * a: type ID (remains unchanged)
             * b: variation type option object whose ID value is the only one returned
             * 
             * For example,
             * 
             * - newOptions:
             * 
             * {
             *  "1": {
             *      "id": 1,
             *      "name": "Black",
             *      "images": [],
             *  }
             * }
             * 
             * - Result of Object.entries().map():
             * 
             * {
             *  "1": 1
             * }
             */
            Object.entries(newOptions).map(([a, b]: [string, VariationTypeOption]) => [a, b.id])
        );
    };

    // Modify the selected option and the URI query parameter if necessary
    const chooseOption = (
        typeId: number,
        option: VariationTypeOption,
        updateRouter: boolean = true
    ) => {

        // Update the query parameters in the URI using an updater function
        setSelectedOptions((prevSelectedOptions) => {

            // Merge the newly selected option with the existing option parameters 
            const newOptions = {
                ...prevSelectedOptions,
                [typeId]: option
            }

            // console.log(newOptions);

            // Check if the URI query parameter is to be updated
            if (updateRouter) {
                /**
                 * url: current page URL
                 */
                router.get(url, {
                        options: getOptionIdsMap(newOptions)
                    }, 
                    {
                        preserveScroll: true,
                        // Preserve the URL and existing query parameters
                        preserveState: true
                    }
                );
            }

            // Return the updated selected options
            return newOptions;
        });
    };

    // Update the quantity field for the 'Add to Cart' form
    const onQuantityChange = (event: React.ChangeEvent<HTMLSelectElement>) => {

        // Update the quantity field
        form.setData('quantity', parseInt(event.target.value));
    };

    // Handles add to cart items
    const addToCart = () => {

        form.post(route('cart.store', product.id), {
            preserveScroll: true,
            preserveState: true,
            onError: (err: any) => {
                console.log(err);
            }
        });
    };

    // ---------------------------------------------------------------------------
    /**
     * 
     *   ███████ ███████ ███████ ███████  ██████ ████████ 
     *   ██      ██      ██      ██      ██         ██    
     *   █████   █████   █████   █████   ██         ██    
     *   ██      ██      ██      ██      ██         ██    
     *   ███████ ██      ██      ███████  ██████    ██    
     *
     */

    // Run once on initial component mount
    useEffect(() => {

        // console.log('I run only once at initial render!');
        // console.log(variationOptions);

        /**
         * Loop through the variation types object (e.g., size and color)
         */
        for (const type of product.variationTypes) {
            
            /**
             * variationOptions is the variation option ID on the URI query parameter
             * 
             * The query parameter can be visualized as '?option[1]=1&option[2]=6
             * where,
             *      type.id -> option[2]
             *      variationOptions[type.id] -> 6
             */
            const selectedOptionId = variationOptions[type.id];

            // console.log(selectedOptionId, type.options);

            chooseOption(
                type.id,
                // In the case where variationOptions is empty, take the first option
                type.options.find(op => op.id === selectedOptionId) || type.options[0],
                // Do not update the URL since the code in the effect hook here reads from the URL
                false
            );
        }
    
    //   return () => {}
    }, []);

    /**
     * Update the selected variation type options field on the 'Add to Cart' form
     */
    useEffect(() => {

        /** 
         * Similar to the getOptionIdsMap method above
         * 
         * Contains the following data structure:
         * 
         * {
         *  "1": 1
         * }
         * 
         */
      const idsMap = Object.fromEntries(

        Object.entries(selectedOptions).map(([typeId, option]: [string, VariationTypeOption]) => [typeId, option.id])
      );

    //   console.log(idsMap);
    
      form.setData('option_ids', idsMap);

    //   return () => {}
    }, [selectedOptions]);
    
    // ------------------------------------------------------------------------
        
    return (
        <AuthenticatedLayout>
            <Head>
                {/* For SEO */}
                <title>{product.title}</title>
                <meta name="title" content={product.meta_title || product.title} />
                <meta name="description" content={product.meta_description} />
                <link rel="canonical" href={route('product.show', product.slug)} />

                <meta property="og:title" content={product.meta_title} />
                <meta property="og:description" content={product.meta_description} />
                <meta property="og:image" content={images[0]?.small} />
                <meta property="og:url" content={route('product.show', product.slug)} />
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content={appName} />
            </Head>

            <div className="container mx-auto p-8">
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    {/* Left Side Column */}
                    <div className="col-span-7">
                        <Carousel images={images} />
                    </div>

                    {/* Right Side Column */}
                    <div className="col-span-5">
                        <h1 className="text-2xl">
                            {product.title}
                        </h1>

                        <p className="mb-8">
                            by <Link href={route('vendor.profile', product.vendor.store_name)} 
                                className="hover:underline" >
                                {product.vendor.store_name}
                            </Link>
                            &nbsp;
                            in <Link href={route('product.byDepartment', product.department.slug)} 
                                className="hover:underline" >
                                {product.department.name} 
                            </Link>
                        </p>

                        <div className="text-3xl font-semibold">
                            <CurrencyFormatter amount={computedProduct.price} />
                        </div>

                        <ProductVariationTypes product={product} selectedOptions={selectedOptions} chooseOption={chooseOption} />

                        {/* Alert the user when the quantity is less than 10 */}
                        {computedProduct.quantity != undefined && computedProduct.quantity < 10 && (

                            <div className="text-error my-4">
                                <span>Only {computedProduct.quantity} left</span>
                            </div>
                        )}

                        <AddToCartButton form={form} onQuantityChange={onQuantityChange} computedProduct={computedProduct} addToCart={addToCart} />

                        <b className="text-xl">About the Product</b>
                        <div className="wysiwyg-output" dangerouslySetInnerHTML={{ __html: product.description }} ></div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Show;