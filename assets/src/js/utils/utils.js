/**
 * Check if the $el is present
 * @param {jQuery} $el
 */
export function elementAvailable($el){
    return $el.length > 0;
}

/**
 * Get a parameter from url by name
 * @param name
 * @param url
 * @return {string|null}
 */
export function getParameterByName(name, url) {
    if (!url){
        url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    let regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}