import './bootstrap';

import Alpine from 'alpinejs';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import './utils/notifications';

window.Alpine = Alpine;
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

        // Close menu when user clicks a link (handled by @click in template)
        // Close menu on route change
        const handleRouteChange = () => {
            this.mobileMenuOpen = false;
        };

        window.addEventListener('resize', handleResize);
        document.addEventListener('turbo:load', handleRouteChange);
        document.addEventListener('turbo:visit', handleRouteChange);

        // Update body overflow for accessibility
        this.$watch('mobileMenuOpen', (value) => {
            if (value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Cleanup on destroy
        return () => {
            window.removeEventListener('resize', handleResize);
            document.removeEventListener('turbo:load', handleRouteChange);
            document.removeEventListener('turbo:visit', handleRouteChange);
        };
    }
});

Alpine.start();
