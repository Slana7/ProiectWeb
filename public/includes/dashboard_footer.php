    </main>
</div>

<div style="clear: both;"></div>

<script src="<?= BASE_URL ?>public/assets/js/responsive.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 200);
    });
</script> 