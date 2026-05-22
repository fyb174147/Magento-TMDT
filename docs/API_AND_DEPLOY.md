# Magento Bookstore - API, Docker, Deploy

## API demo

- `GET /rest/V1/bookstore/catalog/products`: danh sach san pham JSON.
- `GET /rest/V1/bookstore/catalog/categories`: cay danh muc JSON.
- `GET /rest/V1/bookstore/customer/me`: thong tin customer dang nhap.

Magento REST mac dinh van dung duoc cho Cart, Checkout, Customer khi co token:

- `POST /rest/V1/guest-carts`
- `POST /rest/V1/guest-carts/{cartId}/items`
- `PUT /rest/V1/guest-carts/{cartId}/shipping-information`
- `PUT /rest/V1/guest-carts/{cartId}/order`
- `POST /rest/V1/integration/customer/token`
- `GET /rest/V1/customers/me`

## Payment va shipping

- COD: module mac dinh `payment/cashondelivery`, da cau hinh bat.
- VietQR: `payment/bookstore_vietqr`, hien ma QR tai checkout.
- Flat Rate: `carriers/flatrate`, da cau hinh bat.
- Giao hang nhanh: `carriers/bookstore_express`, phi = 18.000 + 3.000 cho moi sach them.

## OpenWeatherMap

Cau hinh API key:

```bash
docker exec magento-php php bin/magento config:set bookstore_utility/weather/api_key "OPENWEATHERMAP_API_KEY"
docker exec magento-php php bin/magento config:set bookstore_utility/weather/city "Hanoi,VN"
docker exec magento-php php bin/magento cache:flush
```

Trang hien thi: `/bookstore-info`.

## Docker deploy len VPS

```bash
cp .env.example .env
docker compose up -d --build
docker exec magento-php php bin/magento setup:upgrade
docker exec magento-php php bin/magento indexer:reindex
docker exec magento-php php bin/magento cache:flush
```

Doi Base URL khi dua len domain:

```bash
docker exec magento-php php bin/magento setup:store-config:set --base-url="https://your-domain.vn/"
docker exec magento-php php bin/magento setup:store-config:set --base-url-secure="https://your-domain.vn/"
docker exec magento-php php bin/magento config:set web/secure/use_in_frontend 1
docker exec magento-php php bin/magento config:set web/secure/use_in_adminhtml 1
docker exec magento-php php bin/magento cache:flush
```

Nginx tren server can tro domain ve port 80 cua container hoac dung reverse proxy/SSL ngoai container.
