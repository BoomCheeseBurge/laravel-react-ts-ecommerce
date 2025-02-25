import { CartItem } from "./types";

// Determine if two arrays are equal
export const arraysAreEqual = (arr1: any[], arr2: any[]) => {

    // Return false if the array are NOT equal
    if (arr1.length !== arr2.length) return false;

    // Check if the value of each element in both arrays is equal
    return arr1.every((value, index) => value === arr2[index]);
}

// Generate the product route for the given cart item
export const showProductRoute = (item: CartItem) => {

    // To append the option IDs to the URI query parameter a product with variation type option
    const params = new URLSearchParams();

    Object.entries(item.option_ids)
        .forEach(([typeId, optionId]) => {
            params.append(`options[${typeId}]`, optionId.toString());
        });

    return route('product.show', item.slug) + '?' + params.toString();
}