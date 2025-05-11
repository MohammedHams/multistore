{{-- This file is used to display translated text in the dashboard --}}
@php
// Define the translations for the sidebar menu
$translations = [
    'dashboard' => __('dashboard.dashboard'),
    'default' => __('dashboard.default'),
    'stores' => __('dashboard.stores'),
    'manage_stores' => __('dashboard.manage_stores'),
    'all_stores' => __('dashboard.all_stores'),
    'add_new_store' => __('dashboard.add_new_store'),
    'store_management' => __('dashboard.store_management'),
    'store_access' => __('dashboard.store_access'),
    'store_owners' => __('dashboard.store_owners'),
    'store_staff' => __('dashboard.store_staff'),
    'catalog' => __('dashboard.catalog'),
    'welcome' => __('dashboard.welcome'),
    'user' => __('dashboard.user'),
    'logout' => __('auth.logout'),
];
@endphp

{{-- Output the translations as JavaScript variables --}}
<script>
    window.translations = @json($translations);
    
    // Function to replace text in elements with translations
    document.addEventListener('DOMContentLoaded', function() {
        // Replace menu titles
        document.querySelectorAll('.menu-title').forEach(function(element) {
            const text = element.textContent.trim();
            const key = text.toLowerCase().replace(/\s+/g, '_');
            
            if (window.translations[key]) {
                element.textContent = window.translations[key];
            }
        });
        
        // Replace menu section titles
        document.querySelectorAll('.menu-section').forEach(function(element) {
            const text = element.textContent.trim();
            const key = text.toLowerCase().replace(/\s+/g, '_');
            
            if (window.translations[key]) {
                element.textContent = window.translations[key];
            }
        });
    });
</script>
