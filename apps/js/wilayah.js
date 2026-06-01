/*
MIT License
copyright (c) 2017-2024 by cahya dsn; cahyadsn@gmail.com
================================================================================
wilayah.js - theme switcher and utility interactions
================================================================================*/

document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    var toggle = document.getElementById('themeToggle');
    var layoutGrid = document.querySelector('.layout-grid');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarStorageKey = 'wilayahSidebarCollapsed';

    function syncMapSize() {
        window.setTimeout(function() {
            if (window.map && typeof window.map.invalidateSize === 'function') {
                window.map.invalidateSize(true);
            }
        }, 260);
    }

    function applyTheme(theme) {
        var nextTheme = theme === 'light' ? 'light' : 'dark';
        body.classList.remove('theme-dark', 'theme-light');
        body.classList.add('theme-' + nextTheme);
        if (toggle) {
            var nextLabel = nextTheme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme';
            toggle.setAttribute('aria-label', nextLabel);
            toggle.setAttribute('title', nextLabel);
        }
        if (typeof window.refreshMapThemeAwareness === 'function') {
            window.refreshMapThemeAwareness();
        }
    }

    function applySidebarState(collapsed) {
        if (!layoutGrid || !sidebarToggle) return;

        layoutGrid.classList.toggle('is-sidebar-collapsed', collapsed);
        var label = collapsed ? 'Buka panel selector' : 'Tutup panel selector';
        sidebarToggle.setAttribute('aria-label', label);
        sidebarToggle.setAttribute('title', label);

        try {
            localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
        } catch (e) {}

        syncMapSize();
    }

    if (toggle) {
        toggle.addEventListener('click', function() {
            var currentTheme = body.classList.contains('theme-light') ? 'light' : 'dark';
            var nextTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(nextTheme);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'inc/change.color.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('theme=' + encodeURIComponent(nextTheme));
        });
    }

    if (sidebarToggle && layoutGrid) {
        var savedCollapsed = false;
        try {
            savedCollapsed = localStorage.getItem(sidebarStorageKey) === '1';
        } catch (e) {}

        applySidebarState(savedCollapsed);

        sidebarToggle.addEventListener('click', function() {
            applySidebarState(!layoutGrid.classList.contains('is-sidebar-collapsed'));
        });
    }

    applyTheme(body.classList.contains('theme-light') ? 'light' : 'dark');
});
