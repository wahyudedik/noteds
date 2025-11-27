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
        // Close menu when screen resizes to desktop width
        const handleResize = () => {
            if (window.innerWidth >= 1024) { // lg breakpoint
                this.mobileMenuOpen = false;
            }
        };
        
        window.addEventListener('resize', handleResize);
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
