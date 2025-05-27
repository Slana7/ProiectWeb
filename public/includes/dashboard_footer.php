    </main>
</div>

<!-- Clear any floating elements and ensure proper container closing -->
<div style="clear: both;"></div>

<!-- Script pentru responsive design -->
<script src="<?= BASE_URL ?>public/assets/js/responsive.js"></script>

<!-- Fix for mobile viewport -->
<script>
    // Ensure proper viewport scaling on mobile
    document.addEventListener('DOMContentLoaded', function() {
        // Force layout recalculation on mobile
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 200);
    });
</script> 