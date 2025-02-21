
export const arraysAreEqual = (arr1: any[], arr2: any[]) => {

    // Return false if the array are NOT equal
    if (arr1.length !== arr2.length) return false;

    // Check if the value of each element in both arrays is equal
    return arr1.every((value, index) => value === arr2[index]);
}