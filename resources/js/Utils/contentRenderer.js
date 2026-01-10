/**
 * Safely render HTML content.
 * 
 * @param {string} html 
 * @returns {string}
 */
export function renderContent(html) {
    if (!html) return '';
    
    // Basic sanitization - in production, use a proper HTML sanitizer
    // For now, we'll rely on backend sanitization
    return html;
}

/**
 * Strip HTML tags and get plain text.
 * 
 * @param {string} html 
 * @returns {string}
 */
export function stripHtml(html) {
    if (!html) return '';
    
    const tmp = document.createElement('DIV');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}


