import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

window.Alpine = Alpine;
window.Swal = Swal;
window.Quill = Quill;

// Configure SweetAlert2 for toast notifications in top-right corner
Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

Alpine.start();
