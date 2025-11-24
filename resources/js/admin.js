// Admin JavaScript - Tabler
import '@tabler/core';

// Add your custom admin scripts here

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const body = document.body;
    const sidebar = document.querySelector('.sidebar');
    const pageWrapper = document.querySelector('.page-wrapper');
    
    // Restore sidebar state from localStorage
    if (localStorage.getItem('sidebar-state') === 'collapsed') {
        body.classList.add('sidebar-collapsed');
        if (pageWrapper) {
            pageWrapper.style.marginLeft = '5rem';
        }
    } else {
        if (pageWrapper) {
            pageWrapper.style.marginLeft = '18rem';
        }
    }
    
    // Toggle sidebar on button click
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sidebar-collapsed');
            
            // Manually update page-wrapper margin
            if (body.classList.contains('sidebar-collapsed')) {
                if (pageWrapper) {
                    pageWrapper.style.marginLeft = '5rem';
                }
                localStorage.setItem('sidebar-state', 'collapsed');
            } else {
                if (pageWrapper) {
                    pageWrapper.style.marginLeft = '18rem';
                }
                localStorage.setItem('sidebar-state', 'expanded');
            }
        });
    }
    
    // Optional: Add keyboard shortcut (Ctrl + B) to toggle sidebar
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            if (toggleBtn) {
                toggleBtn.click();
            }
        }
    });
});
