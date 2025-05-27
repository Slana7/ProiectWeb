// Script universal pentru responsive design
document.addEventListener('DOMContentLoaded', function() {
    function applyResponsiveLayout() {
        const isMobile = window.innerWidth <= 992;
        const sidebar = document.querySelector('.sidebar');
        const mobileHeader = document.querySelector('.mobile-header');
        
        if (isMobile) {
            // Pe mobil/tableta
            if (sidebar) sidebar.style.display = 'none';
            if (mobileHeader) mobileHeader.style.display = 'block';
        } else {
            // Pe desktop
            if (sidebar) sidebar.style.display = 'flex';
            if (mobileHeader) mobileHeader.style.display = 'none';
        }
        
        // Trigger pentru leaflet map sa se redimensioneze corect
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    }
    
    //Aplicam
    applyResponsiveLayout();
    
    // Si la redimensionare
    window.addEventListener('resize', applyResponsiveLayout);
    
    // Meniu burger pentru mobil
    const burgerMenu = document.querySelector('.burger-menu');
    if (burgerMenu) {
        burgerMenu.addEventListener('click', function() {
            document.querySelector('.burger-icon').classList.toggle('active');
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    }
    
    // Inchide meniul la click in afara lui
    document.addEventListener('click', function(event) {
        const navMenu = document.querySelector('.nav-menu');
        const burgerIcon = document.querySelector('.burger-icon');
        
        if (navMenu && burgerIcon && navMenu.classList.contains('active')) {
            if (!event.target.closest('.nav-menu') && !event.target.closest('.burger-menu')) {
                navMenu.classList.remove('active');
                burgerIcon.classList.remove('active');
            }
        }
    });
    
    // Toggle pentru lista de proprietati pe pagina de harta
    const toggleList = document.getElementById('toggle-list');
    if (toggleList) {
        toggleList.addEventListener('click', function() {
            const sidebar = document.getElementById('property-sidebar');
            const isMobile = window.innerWidth <= 992;
            
            if (isMobile) {
                sidebar.classList.toggle('mobile-visible');
            } else {
                sidebar.classList.toggle('hidden');
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            }
        });
    }
}); 