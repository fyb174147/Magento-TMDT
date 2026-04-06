define([], function () {
    'use strict';

    return function () {
        const toggleBtn = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement; // Tác động vào thẻ <html>
        const currentTheme = localStorage.getItem('theme') || 'light';

        // 1. Thiết lập theme ban đầu từ localStorage
        if (currentTheme === 'dark') {
            htmlElement.setAttribute('data-theme', 'dark');
        }

        // 2. Sự kiện khi click vào nút
        toggleBtn.addEventListener('click', function () {
            let theme = htmlElement.getAttribute('data-theme');
            
            if (theme === 'dark') {
                htmlElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            } else {
                htmlElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    };
});