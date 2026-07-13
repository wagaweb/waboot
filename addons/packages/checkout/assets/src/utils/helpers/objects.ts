export function deepClone(originalObject: object){
    return JSON.parse(JSON.stringify(originalObject));
}