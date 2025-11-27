import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Quill from 'quill';
import { initializeSweetAlertUtils, initializeDataAttributeListeners } from './utils/sweetalert';
import 'quill/dist/quill.snow.css';

window.Alpine = Alpine;
window.Swal = Swal;
window.Quill = Quill;

// Navigation menu component with responsive behavior
window.navigationMenu = () => ({
    mobileMenuOpen: false,
    init() {
        // Close menu when screen resizes to desktop
        const handleResize = () => {
            if (window.innerWidth >= 1024) { // lg breakpoint
                this.mobileMenuOpen = false;
            }
        };
        
        // Close menu when user scrolls
        const handleScroll = () => {
            if (this.mobileMenuOpen) {
                this.mobileMenuOpen = false;
            }
        };
        
        // Close menu when DOM content changes (navigation/route change)
        const handleMutations = (mutations) => {
            // Only close if significant DOM changes (not just hover/focus)
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && this.mobileMenuOpen) {
                    this.mobileMenuOpen = false;
                }
            });
        };
        
        window.addEventListener('resize', handleResize);
        window.addEventListener('scroll', handleScroll, true); // Use capture phase
        document.addEventListener('turbo:load', () => {
            this.mobileMenuOpen = false; // Close on page load
        });
        document.addEventListener('turbo:visit', () => {
            this.mobileMenuOpen = false; // Close on turbo visit
        });
        
        // Watch for body content changes
        const observer = new MutationObserver(handleMutations);
        const mainContent = document.querySelector('main') || document.body;
        if (mainContent) {
            observer.observe(mainContent, { childList: true, subtree: false });
        }
        
        // Update body class for accessibility
        this.$watch('mobileMenuOpen', () => {
            if (this.mobileMenuOpen) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }
});

// Initialize SweetAlert utilities immediately (not waiting for DOMContentLoaded)
// This ensures window.showSuccess, window.showError, etc. are available immediately
initializeSweetAlertUtils();
initializeDataAttributeListeners();

// Also initialize when DOM is ready for any elements that need it
document.addEventListener('DOMContentLoaded', () => {
    // Ensure event listeners are attached to any dynamically loaded content
    initializeDataAttributeListeners();
});

Alpine.start();
