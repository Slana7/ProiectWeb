document.addEventListener('DOMContentLoaded', function()
{
    const TABLET_BREAKPOINT = 768;
    
    function applyResponsiveLayout() {
        const isMobile = window.innerWidth <= TABLET_BREAKPOINT;
        const sidebar = document.querySelector('.sidebar');
        const mobileHeader = document.querySelector('.mobile-header');
        const isAuthPage = document.body.querySelector('.auth-page');
        
        if (isMobile) {
            if (sidebar) sidebar.style.display = 'none';
            if (mobileHeader && !isAuthPage) mobileHeader.style.display = 'block';
        } else {
            if (sidebar) sidebar.style.display = 'flex';
            if (mobileHeader && !isAuthPage) mobileHeader.style.display = 'none';
            
            const navMenu = document.querySelector('.nav-menu');
            const burgerIcon = document.querySelector('.burger-icon');
            if (navMenu) navMenu.classList.remove('active');
            if (burgerIcon) burgerIcon.classList.remove('active');
        }
          if (isAuthPage && mobileHeader) {
            mobileHeader.style.display = 'block';
            mobileHeader.style.setProperty('display', 'block', 'important');
        }
        
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    }
    
    function createMobileOverlay() {
        let overlay = document.getElementById('mobile-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'mobile-overlay';
            overlay.className = 'mobile-overlay';
            document.body.appendChild(overlay);
        }
        return overlay;
    }
    
    function showMobileOverlay() {
        const overlay = createMobileOverlay();
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function hideMobileOverlay() {
        const overlay = document.getElementById('mobile-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
        document.body.style.overflow = '';
    }
    
    applyResponsiveLayout();
    
    window.addEventListener('resize', applyResponsiveLayout);
    
    const burgerMenu = document.querySelector('.burger-menu');
    if (burgerMenu) {
        burgerMenu.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const burgerIcon = document.querySelector('.burger-icon');
            const navMenu = document.querySelector('.nav-menu');
            
            if (burgerIcon && navMenu) {
                const isActive = navMenu.classList.contains('active');
                
                burgerIcon.classList.toggle('active');
                navMenu.classList.toggle('active');
                
                if (!isActive) {
                    showMobileOverlay();
                } else {
                    hideMobileOverlay();
                }
            }
        });
    }

    const toggleList = document.getElementById('toggle-list');
    if (toggleList) {
        toggleList.addEventListener('click', function() {
            const sidebar = document.getElementById('property-sidebar');
            const filterPanel = document.getElementById('filter-panel');
            const isMobile = window.innerWidth <= TABLET_BREAKPOINT;
            
            if (isMobile) {
                if (sidebar) {
                    const isVisible = sidebar.classList.contains('mobile-visible');
                    sidebar.classList.toggle('mobile-visible');
                    
                    if (!isVisible) {
                        showMobileOverlay();
                    } else {
                        hideMobileOverlay();
                    }
                }
                if (filterPanel) {
                    filterPanel.classList.remove('open');
                }
            } else {
                if (sidebar) {
                    sidebar.classList.toggle('hidden');
                    setTimeout(function() {
                        window.dispatchEvent(new Event('resize'));
                    }, 300);
                }
            }
        });
    }
    
    const filterButton = document.getElementById('filter-button');
    if (filterButton) {
        filterButton.addEventListener('click', function() {
            const filterPanel = document.getElementById('filter-panel');
            const sidebar = document.getElementById('property-sidebar');
            const isMobile = window.innerWidth <= TABLET_BREAKPOINT;
            
            if (filterPanel) {
                if (isMobile) {
                    if (sidebar) {
                        sidebar.classList.remove('mobile-visible');
                    }
                    showMobileOverlay();
                }
                filterPanel.classList.add('open');
            }
        });
    }
    
    const closeFilter = document.getElementById('close-filter');
    if (closeFilter) {
        closeFilter.addEventListener('click', function() {
            const filterPanel = document.getElementById('filter-panel');
            if (filterPanel) {
                filterPanel.classList.remove('open');
                filterPanel.scrollTop = 0;
                hideMobileOverlay();
            }
        });
    }
    
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('property-sidebar');
        const filterPanel = document.getElementById('filter-panel');
        const overlay = document.getElementById('mobile-overlay');
        const isMobile = window.innerWidth <= TABLET_BREAKPOINT;
        
        if (isMobile && overlay && event.target === overlay) {
            if (sidebar && sidebar.classList.contains('mobile-visible')) {
                sidebar.classList.remove('mobile-visible');
            }
            if (filterPanel && filterPanel.classList.contains('open')) {
                filterPanel.classList.remove('open');
            }
            hideMobileOverlay();
        }
    });
    
    document.addEventListener('click', function(event) {
        const navMenu = document.querySelector('.nav-menu');
        const burgerIcon = document.querySelector('.burger-icon');
        const overlay = document.getElementById('mobile-overlay');
        
        if (navMenu && burgerIcon && navMenu.classList.contains('active')) {
            if (event.target === overlay || (!event.target.closest('.nav-menu') && !event.target.closest('.burger-menu'))) {
                navMenu.classList.remove('active');
                burgerIcon.classList.remove('active');
                hideMobileOverlay();
            }
        }
    });
    
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            const navMenu = document.querySelector('.nav-menu');
            const burgerIcon = document.querySelector('.burger-icon');
            
            if (navMenu && burgerIcon) {
                navMenu.classList.remove('active');
                burgerIcon.classList.remove('active');
                hideMobileOverlay();
            }
        });
    });
    
    const sidebar = document.getElementById('property-sidebar');
    const filterPanel = document.getElementById('filter-panel');
    
    if (sidebar) {
        sidebar.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
    
    if (filterPanel) {
        filterPanel.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
});