import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type Image = {
    id: number;
    // The string values below will store the direct URL to the associated image size types
    thumb: string;
    small: string;
    large: string;
}

export type VariationTypeOption = {
    id: number;
    name: string;
    images: Image[];
    // type: VariationType; // Just in case if needed
}

export type VariationType = {
    id: number;
    name: string;
    type: 'Select' | 'Radio' | 'Image';
    /**
     * An array of objects type VariationTypeOption (related to the 'variation_type_options' table)
     */
    options: VariationTypeOption[];
}

/**
 * This type covers for product list item and product details
 */
export type Product = {
    id: number;
    title: string;
    slug: string;
    price: number;
    quantity: number;
    image: string;
    images: Image[];
    short_description: string;
    description: string;
    user: {
        id: number;
        name: string;
    };
    department: {
        id: number;
        name: string;
    };
    /**
     * An array of objects type VariationType (related to the 'variation_types' table)
     */
    variationTypes: VariationType[],
    /**
     * An array of objects (related to the 'product_variations' table)
     */
    variations: Array<{
        id: number;
        variation_type_option_ids: number[];
        quantity: number;
        price: number;
    }>
}

export type ComputedProduct = {
    quantity: number;
    price: number;
}

export type FormData = {
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
}

export type PaginationProps<T> = {
    data: Array<T>;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};
