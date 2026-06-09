define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    var defaultUrl = urlBuilder.build('bookstore-utility-ajax/feed');

    function formatNumber(num) {
        if (num === null || num === undefined || num === '') return '—';
        return Number(num).toLocaleString('vi-VN');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            var d = new Date(dateStr);
            return d.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateStr;
        }
    }

    function getWeatherIconUrl(iconCode) {
        return 'https://openweathermap.org/img/wn/' + iconCode + '@2x.png';
    }

    function renderWeather(data) {
        var $el = $('#utility-weather-content');
        if (!data || data.message) {
            $el.html('<p class="utility-muted">' + (data ? data.message || 'Không có dữ liệu' : 'Lỗi kết nối') + '</p>');
            return;
        }

        var html = '<div class="weather-main">';
        html += '<div class="weather-icon-wrap">';
        html += '<img src="' + getWeatherIconUrl(data.icon) + '" alt="' + (data.description || '') + '" class="weather-icon-img"/>';
        html += '</div>';
        html += '<div class="weather-temp-block">';
        html += '<span class="weather-temp-value">' + formatNumber(data.temperature) + '°C</span>';
        html += '<span class="weather-desc">' + (data.description || '') + '</span>';
        html += '</div>';
        html += '</div>';
        html += '<div class="weather-details">';
        html += '<div class="weather-detail-item"><span class="wd-label">Thành phố</span><span class="wd-value">' + (data.city || '') + '</span></div>';
        html += '<div class="weather-detail-item"><span class="wd-label">Cảm giác như</span><span class="wd-value">' + formatNumber(data.feels_like) + '°C</span></div>';
        html += '<div class="weather-detail-item"><span class="wd-label">Độ ẩm</span><span class="wd-value">' + formatNumber(data.humidity) + '%</span></div>';
        html += '<div class="weather-detail-item"><span class="wd-label">Gió</span><span class="wd-value">' + formatNumber(data.wind_speed) + ' m/s</span></div>';
        html += '<div class="weather-detail-item"><span class="wd-label">Áp suất</span><span class="wd-value">' + formatNumber(data.pressure) + ' hPa</span></div>';
        html += '</div>';

        $el.html(html);
    }

    function renderRates(data) {
        var $el = $('#utility-rates-content');
        if (!data || !data.length) {
            $el.html('<p class="utility-muted">Không lấy được dữ liệu tỷ giá.</p>');
            return;
        }

        var html = '<div class="utility-rates-scroll">';
        html += '<table class="utility-rate-table">';
        html += '<thead><tr><th>Loại tiền</th><th>Mua</th><th>Chuyển khoản</th><th>Bán</th></tr></thead>';
        html += '<tbody>';

        for (var i = 0; i < data.length; i++) {
            var r = data[i];
            html += '<tr>';
            html += '<td><strong>' + r.code + '</strong><span>' + r.name + '</span></td>';
            html += '<td>' + (r.buy === '-' || r.buy === '' ? '—' : r.buy) + '</td>';
            html += '<td>' + formatNumber(r.transfer) + '</td>';
            html += '<td>' + formatNumber(r.sell) + '</td>';
            html += '</tr>';
        }

        html += '</tbody></table></div>';
        $el.html(html);
    }

    function renderNews(data) {
        var $el = $('#utility-news-content');
        if (!data || !data.length) {
            $el.html('<p class="utility-muted">Chưa lấy được tin tức.</p>');
            return;
        }

        var html = '<div class="utility-news-grid">';

        for (var i = 0; i < data.length; i++) {
            var item = data[i];
            html += '<article class="news-item">';
            if (item.image) {
                html += '<div class="news-thumb"><a href="' + item.link + '" target="_blank" rel="noopener"><img src="' + item.image + '" alt="" loading="lazy"/></a></div>';
            }
            html += '<div class="news-body">';
            html += '<a href="' + item.link + '" target="_blank" rel="noopener" class="news-title">' + item.title + '</a>';
            html += '<p class="news-desc">' + (item.description || '') + '</p>';
            html += '<span class="news-date">' + formatDate(item.date) + '</span>';
            html += '</div>';
            html += '</article>';
        }

        html += '</div>';
        $el.html(html);
    }

    function showLoading(selector) {
        $(selector).html('<div class="utility-loading"><span></span><span></span><span></span></div>');
    }

    function fetchSection(url, section) {
        showLoading('#utility-' + section + '-content');

        $.ajax({
            url: url + '?section=' + section,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (section === 'weather') renderWeather(res);
                else if (section === 'rates') renderRates(res);
                else if (section === 'news') renderNews(res);
            },
            error: function () {
                $('#utility-' + section + '-content').html(
                    '<p class="utility-muted">Lỗi khi tải dữ liệu.</p>'
                );
            }
        });
    }

    /**
     * Widget initializer - called by Magento's data-mage-init
     * @param {Object} config - { ajaxUrl: string }
     */
    return function (config) {
        var feedUrl = (config && config.ajaxUrl) ? config.ajaxUrl : defaultUrl;

        /* Bind refresh buttons */
        $(document).on('click', '.utility-refresh-btn', function () {
            var section = $(this).data('section');
            if (section) {
                fetchSection(feedUrl, section);
            }
        });
    };
});