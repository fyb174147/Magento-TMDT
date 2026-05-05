#!/bin/bash

# --- 0. KHAI BÁO BIẾN ---
PROJECT_DIR="src" 
PHP_CONTAINER="magento-php"
DB_CONTAINER="magento-db"

# --- MÀU SẮC ---
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' 

# --- Logging Functions ---
log_header() {
    echo -e "\n${YELLOW}>>> $1${NC}"
}

# --- PHASE 1: DỌN DẸP SÂU (DEEP CLEAN) ---
log_header "1. Dọn dẹp file tĩnh và code sinh ra..."
# Xóa sạch theo đúng các đường dẫn bạn đã chạy thành công
sudo rm -rf $PROJECT_DIR/pub/static/frontend/* \
            $PROJECT_DIR/pub/static/adminhtml/* \
            $PROJECT_DIR/var/view_preprocessed/* \
            $PROJECT_DIR/generated/code/* \
            $PROJECT_DIR/generated/metadata/* \
            $PROJECT_DIR/var/cache/* \
            $PROJECT_DIR/var/page_cache/*

# --- PHASE 2: CẤP QUYỀN (PERMISSIONS) ---
log_header "2. Chỉnh lại quyền sở hữu (UID 33 cho www-data)..."
docker compose exec -u root -T php chown -R 33:33 /var/www/html/var /var/www/html/pub /var/www/html/generated

# --- PHASE 3: BIÊN DỊCH & DEPLOY ---
log_header "3. Biên dịch DI (Fix lỗi Proxy/Reflection)..."
docker compose exec -u 33 -T php bin/magento setup:di:compile

log_header "4. Deploy Static Content (vi_VN & en_US)..."
# Ép buộc deploy cho cả 2 ngôn ngữ theo ý bạn
docker compose exec -u 33 -T php bin/magento setup:static-content:deploy -f vi_VN en_US

log_header "5. Resize ảnh sản phẩm (Cứu cánh cho placeholder)..."
# Lệnh này giúp tạo ảnh cho các Widget và List page
docker compose exec -u 33 -T php bin/magento catalog:images:resize

# --- PHASE 4: KẾT THÚC ---
log_header "6. Flush Cache & Reindex..."
docker compose exec -u 33 -T php bin/magento indexer:reindex
docker compose exec -u 33 -T php bin/magento cache:flush

# Cấp lại quyền 777 cho Nginx đọc file dễ dàng
sudo chmod -R 777 $PROJECT_DIR/var $PROJECT_DIR/pub $PROJECT_DIR/generated $PROJECT_DIR/pub/static

echo -e "\n${GREEN}✔ Xong! Hãy nhấn Ctrl + F5 trên Firefox để kiểm tra kết quả.${NC}"
