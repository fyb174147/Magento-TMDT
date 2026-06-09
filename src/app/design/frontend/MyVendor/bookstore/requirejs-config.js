var config = {
    map: {
        '*': {
            'themeCustom': 'js/theme-custom',
            'themeSwitcher': 'js/theme-switcher'
        }
    },
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                // Sửa đường dẫn chuẩn theo thư mục theme của bạn
                'Magento_Catalog/js/mixin/catalog-add-to-cart-mixin': true
            }
        }
    }
};
