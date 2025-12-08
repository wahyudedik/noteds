/**
 * Client-Side Click Protection Script
 * Prevents multiple rapid clicks pada affiliate landing page
 * 
 * Deployment: Include dalam landing page HTML
 * <script src="/js/affiliate-click-protection.js"></script>
 */

class AffiliateClickProtection {
    constructor() {
        this.lastClickTime = null;
        this.minClickInterval = 5000; // 5 seconds - sama seperti backend
        this.clickInProgress = false;
        this.clickCount = 0;
        this.sessionStartTime = Date.now();

        // UI Elements
        this.clickButton = document.getElementById('affiliate-click-button');
        this.feedbackElement = document.getElementById('click-feedback');

        if (this.clickButton) {
            this.clickButton.addEventListener('click', (e) => this.handleClick(e));
        }
    }

    /**
     * Handle affiliate click dengan protection
     */
    handleClick(event) {
        event.preventDefault();

        // Check 1: Already clicking?
        if (this.clickInProgress) {
            this.showFeedback('Please wait, processing...', 'warning');
            return;
        }

        // Check 2: Too soon since last click?
        const now = Date.now();
        if (this.lastClickTime !== null) {
            const timeSinceLastClick = now - this.lastClickTime;
            const remainingTime = this.minClickInterval - timeSinceLastClick;

            if (remainingTime > 0) {
                const seconds = Math.ceil(remainingTime / 1000);
                this.showFeedback(
                    `Please wait ${seconds} second${seconds > 1 ? 's' : ''} before clicking again`,
                    'error'
                );
                return;
            }
        }

        // Check 3: Too many clicks in session?
        this.clickCount++;
        if (this.clickCount > 100) { // Sanity check
            this.showFeedback('Too many clicks detected. Please refresh page.', 'error');
            return;
        }

        // All checks passed - process click
        this.processClick();
    }

    /**
     * Send click ke server dengan fraud detection
     */
    async processClick() {
        this.clickInProgress = true;
        this.lastClickTime = Date.now();

        // Disable button durant request
        if (this.clickButton) {
            this.clickButton.disabled = true;
            this.clickButton.textContent = 'Processing...';
        }

        try {
            // Get affiliate code dari URL atau data attribute
            const affiliateCode = this.getAffiliateCode();
            if (!affiliateCode) {
                throw new Error('Invalid affiliate code');
            }

            // Send click ke backend
            const response = await fetch(`/api/affiliate/click/${affiliateCode}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    // Backend bisa tambah data lain jika perlu
                    timestamp: Date.now(),
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // ✅ Valid click registered
                this.handleClickSuccess(data);
            } else if (!response.ok && data.reason === 'duplicate_click_detected') {
                // ⚠️ Duplicate click detected
                this.handleDuplicateClick(data);
            } else {
                // ❌ Other error
                this.handleClickError(data);
            }

        } catch (error) {
            console.error('Click processing error:', error);
            this.showFeedback(
                'Error processing click. Please try again.',
                'error'
            );
        } finally {
            this.clickInProgress = false;

            // Re-enable button
            if (this.clickButton) {
                this.clickButton.disabled = false;
                this.clickButton.textContent = 'Visit';
            }
        }
    }

    /**
     * Handle successful click
     */
    handleClickSuccess(data) {
        console.log('✅ Click registered successfully', {
            clickId: data.click_id,
            fraudRisk: data.fraud_risk,
            indicators: data.fraud_indicators,
        });

        this.showFeedback(
            'Click registered successfully! Redirecting...',
            'success'
        );

        // Store click ID untuk conversion tracking nanti
        sessionStorage.setItem('affiliate_click_id', data.click_id);
        sessionStorage.setItem('affiliate_click_time', Date.now());

        // Redirect setelah delay
        setTimeout(() => {
            this.redirectToDestination();
        }, 1500);
    }

    /**
     * Handle duplicate click (rejected oleh backend)
     */
    handleDuplicateClick(data) {
        console.warn('⚠️ Duplicate click detected by backend', {
            reason: data.reason,
            clickId: data.click_id,
            indicators: data.fraud_indicators,
        });

        // Still proceed dengan original click ID
        sessionStorage.setItem('affiliate_click_id', data.click_id);
        sessionStorage.setItem('affiliate_click_time', Date.now());

        this.showFeedback(
            'Click already registered. Proceeding...',
            'warning'
        );

        setTimeout(() => {
            this.redirectToDestination();
        }, 1500);
    }

    /**
     * Handle click error
     */
    handleClickError(data) {
        console.error('❌ Click processing failed', data);

        let message = data.error || 'Click could not be processed';

        if (data.error === 'Account suspended due to fraud detection') {
            message = 'This account has been suspended. Please contact support.';
        } else if (data.reason === 'rate_limit_exceeded_minute') {
            message = 'Too many clicks. Please wait a moment.';
        }

        this.showFeedback(message, 'error');
    }

    /**
     * Show feedback message ke user
     */
    showFeedback(message, type = 'info') {
        if (!this.feedbackElement) {
            console.log(`[${type.toUpperCase()}] ${message}`);
            return;
        }

        this.feedbackElement.textContent = message;
        this.feedbackElement.className = `click-feedback click-feedback-${type}`;
        this.feedbackElement.style.display = 'block';

        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                this.feedbackElement.style.display = 'none';
            }, 5000);
        }
    }

    /**
     * Get affiliate code dari URL atau data attribute
     */
    getAffiliateCode() {
        // Method 1: From data attribute
        if (this.clickButton?.dataset.affiliateCode) {
            return this.clickButton.dataset.affiliateCode;
        }

        // Method 2: From URL query parameter
        const params = new URLSearchParams(window.location.search);
        if (params.has('affiliate')) {
            return params.get('affiliate');
        }

        // Method 3: From window variable (set oleh server)
        if (window.AFFILIATE_CODE) {
            return window.AFFILIATE_CODE;
        }

        return null;
    }

    /**
     * Get CSRF token untuk POST request
     */
    getCsrfToken() {
        // Method 1: From meta tag
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.content;
        }

        // Method 2: From cookie
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'XSRF-TOKEN') {
                return decodeURIComponent(value);
            }
        }

        return '';
    }

    /**
     * Redirect ke destination URL
     */
    redirectToDestination() {
        // Get destination dari data attribute atau query param
        const destination =
            this.clickButton?.dataset.destination ||
            new URLSearchParams(window.location.search).get('redirect') ||
            '/';

        window.location.href = destination;
    }

    /**
     * Get session duration untuk analytics
     */
    getSessionDuration() {
        return Math.floor((Date.now() - this.sessionStartTime) / 1000);
    }

    /**
     * Get click statistics
     */
    getStats() {
        return {
            clickCount: this.clickCount,
            sessionDuration: this.getSessionDuration(),
            lastClickTime: this.lastClickTime,
            clickId: sessionStorage.getItem('affiliate_click_id'),
        };
    }
}

// Initialize pada DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.affiliateClickProtection = new AffiliateClickProtection();
});

// Export untuk testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AffiliateClickProtection;
}
