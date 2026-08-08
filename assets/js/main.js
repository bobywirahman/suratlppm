// Main JavaScript for LPPM Surat System
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate duration based on start and end dates
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const durationInput = document.getElementById('duration_months');

    if (startDateInput && endDateInput) {
        calculateDuration();
        
        startDateInput.addEventListener('change', calculateDuration);
        endDateInput.addEventListener('change', calculateDuration);
    }

    function calculateDuration() {
        if (!startDateInput.value || !endDateInput.value) return;

        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return;

        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const months = Math.floor(diffDays / 30) || 1;

        if (durationInput) {
            durationInput.value = max(1, months);
        }
    }

    function max(a, b) {
        return a > b ? a : b;
    }

    // Form validation helper
    function validateForm() {
        const form = document.querySelector('form');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        
        return true;
    }

    // --- Responsive sidebar (off-canvas drawer on mobile) ---
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (sidebar) {
        const setExpanded = function(expanded) {
            sidebar.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        };

        const openSidebar = function() {
            sidebar.classList.add('open');
            if (backdrop) backdrop.classList.add('open');
            document.body.classList.add('sidebar-open');
            setExpanded(true);
        };

        const closeSidebar = function() {
            sidebar.classList.remove('open');
            if (backdrop) backdrop.classList.remove('open');
            document.body.classList.remove('sidebar-open');
            setExpanded(false);
        };

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        // Close when a menu link is clicked (mobile)
        sidebar.querySelectorAll('a[href]').forEach(function(link) {
            link.addEventListener('click', closeSidebar);
        });

        // Reset when resizing back to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) closeSidebar();
        });

        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
        });
    }
});
