/**
 * Detect URL in text and return the last URL found (most relevant).
 * 
 * @param {string} text - The text to search for URLs
 * @returns {string|null} - The detected URL or null if not found
 */
export function detectUrl(text) {
    if (!text || typeof text !== 'string') {
        return null;
    }

    // URL regex pattern - matches http, https, and common URL patterns
    const urlPattern = /(https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9-]+\.[a-zA-Z]{2,}[^\s]*)/gi;
    
    const matches = text.match(urlPattern);
    
    if (!matches || matches.length === 0) {
        return null;
    }

    // Return the last match (most relevant, usually added at the end)
    let url = matches[matches.length - 1].trim();
    
    // Remove trailing punctuation
    url = url.replace(/[.,;:!?]+$/, '');
    
    // Add protocol if missing
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'https://' + url;
    }

    return url;
}

/**
 * Extract all URLs from text.
 * 
 * @param {string} text - The text to search for URLs
 * @returns {string[]} - Array of detected URLs
 */
export function extractAllUrls(text) {
    if (!text || typeof text !== 'string') {
        return [];
    }

    const urlPattern = /(https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9-]+\.[a-zA-Z]{2,}[^\s]*)/gi;
    const matches = text.match(urlPattern);
    
    if (!matches) {
        return [];
    }

    return matches.map(url => {
        let cleanUrl = url.trim();
        cleanUrl = cleanUrl.replace(/[.,;:!?]+$/, '');
        
        if (!cleanUrl.startsWith('http://') && !cleanUrl.startsWith('https://')) {
            cleanUrl = 'https://' + cleanUrl;
        }
        
        return cleanUrl;
    });
}

