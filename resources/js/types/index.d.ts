import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    stripe_account_active: boolean;
    vendor: {
        status: string;
        status_label: string;
        store_name: string;
        store_address: string;
        cover_image: string;
    };
};

export type Image = {
    id: number;
    // The string values below will store the direct URL to the associated image size types
    thumb: string;
    small: string;
    large: string;
};

export type VariationTypeOption = {
    id: number;
    name: string;
    images: Image[];
    type: VariationType; // Just in case if needed
};

export type VariationType = {
    id: number;
    name: string;
    type: 'Select' | 'Radio' | 'Image';
    /**
     * An array of objects type VariationTypeOption (related to the 'variation_type_options' table)
     */
    options: VariationTypeOption[];
};

export type VariationOption = {
    id: number;
    variation_type_id: number;
    name: string;
};

/**
 * This type covers for product list item and product details
 */
export type Product = {
    id: number;
    title: string;
    slug: string;
    meta_title: string;
    meta_description: string;
    price: number;
    quantity: number;
    image: string;
    images: Image[];
    short_description: string;
    description: string;
    vendor: {
        store_name: string;
    };
    department: {
        id: number;
        name: string;
        slug: string;
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
};

export type CartItem = {
    id: number;
    product_id: number;
    title: string;
    slug: string;
    price: number;
    quantity: number;
    checkout_later: boolean;
    image: string;
    option_ids: Record<string, number>;
    options: VariationTypeOption[];
};

export type GroupedCartItems = {
    user: User;
    items: CartItem[];
    totalQuantity: number;
    totalPrice: number;
};

export type ComputedProduct = {
    quantity: number;
    price: number;
};

export type FormData = {
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
};

export type OrderItem = {
    id: number;
    quantity: number;
    price: number;
    variation_type_option_ids: number[];
    product: {
        id: number;
        title: string;
        slug: string;
        description: string;
        image: string;
        departmentName: string;
    };
    address: {
        id: number;
        order_id: number;
        date: string | null;
        status: DeliveryStatus;
        full_name: string;
        phone_number: string | null;
        address_line_1: string;
        address_line_2: string | null;
        city: string | null;
        province: string | null;
        postal_code: string | null;
        instructions: string | null;
    }
};

export type Order = {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
    vendorUser: {
        id: number;
        name: string;
        email: string;
        store_name: string;
        store_address: string;
    };
    orderItems: OrderItem[];
};

export type Vendor = {
    id: number;
    store_name: string;
    store_address: string;
};

export type Category = {
    id: number;
    name: string;
};

export type Department = {
    id: number;
    name: string;
    slug: string;
    meta_title: string;
    meta_description: string;
    categories: Category[];
};

export type PexelsImage = {
    id: number;
    width: number;
    height: number;
    url: string;
    photographer: string;
    photographer_url: string;
    photographer_id: number;
    avg_color: string;
    src: {
      original: string;
      large2x: string;
      large: string;
      medium: string;
      small: string;
      portrait: string;
      landscape: string;
      tiny: string;
    };
};

export type TextInputRef = {
    focus: () => void;
    getValue: () => string | undefined;
};

export type PaginationProps<T> = {
    data: Array<T>;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        is_admin_or_vendor: boolean;
    };
    ziggy: Config & { location: string };
    totalQuantity: number;
    totalPrice: number;
    dropdownCartItems: CartItem[];
    csrf_token: string;
    success: {
        message: string;
        time: number;
    };
    error: string;
    departments: Department[];
    appName: string;
    departmentParam: string;
    keyword: string;
    cartItems: array;
};
