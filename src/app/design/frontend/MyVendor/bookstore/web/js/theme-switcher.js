define([], function () {
    'use strict';

    return function () {
        const htmlElement = document.documentElement;
        
        // 1. Kiểm tra và áp dụng theme ngay khi script load
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            htmlElement.setAttribute('data-theme', 'dark');
        }

        // 2. Sử dụng Event Delegation để tránh lỗi nút chưa render xong
        document.addEventListener('click', function (e) {
            // Tìm phần tử gần nhất có ID là theme-toggle
            const toggleBtn = e.target.closest('#theme-toggle');
            
            if (toggleBtn) {
                e.preventDefault();
                let isDark = htmlElement.getAttribute('data-theme') === 'dark';
                
                if (isDark) {
                    htmlElement.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                } else {
                    htmlElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });
    };
});