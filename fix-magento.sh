#!/bin/bash

# --- 0. KHAI BÁO BIẾN & CẤU HÌNH ---
PROJECT_DIR="src" 
THEME_NAME="MyVendor/bookstore"
PHP_CONTAINER="php"
DB_CONTAINER="db"

# --- MÀU SẮC ANSI ---
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' 
BOLD='\033[1m'

log_header() {
    echo -e "\n${BOLD}${YELLOW}===================================================================="
    echo -e "  $1"
    echo -e "====================================================================${NC}"
}

log_task() { echo -ne "${BLUE}[PROCESSING]${NC} $1... "; }
log_done() { echo -e "${GREEN}DONE${NC}"; }
log_fail() { echo -e "${RED}FAILED${NC}"; }

# --- PHASE 1: PRE-CHECK (Kiểm tra an toàn) ---
log_header "PHASE 1: ENVIRONMENT PRE-CHECK"

if [ ! -f "docker-compose.yml" ]; then
    echo -e "${RED}[ERROR]${NC} Không tìm thấy docker-compose.yml. Hãy chạy script tại gốc dự án!"
    exit 1
fi

if [ ! -f .env ]; then
    echo -e "${RED}[ERROR]${NC} Không tìm thấy .env. Hãy tạo file .env từ .env.example!"
    exit 1
fi

# Load biến môi trường
export $(grep -v '^#' .env | xargs)

if ! docker compose ps | grep -q "Up"; then
    echo -e "${RED}[ERROR]${NC} Docker containers chưa chạy. Hãy gõ 'docker compose up -d'!"
    exit 1
fi
log_done "Môi trường sẵn sàng."

# --- PHASE 2: SYSTEM PERMISSIONS & CLEANUP ---
log_header "PHASE 2: PERMISSIONS & CLEANUP"

log_task "Synchronizing ownership for $(whoami)"
sudo chown -R $(whoami):$(whoami) . && log_done

log_task "Purging cache, generated and session files"
# Xóa sạch các file gây lỗi UID (var/session) và code cũ
sudo rm -rf $PROJECT_DIR/var/cache/* \
            $PROJECT_DIR/var/page_cache/* \
            $PROJECT_DIR/var/view_preprocessed/* \
            $PROJECT_DIR/var/session/* \
            $PROJECT_DIR/generated/code/* \
            $PROJECT_DIR/pub/static/* && log_done

log_task "Granting write permissions (777)"
sudo chmod -R 777 $PROJECT_DIR/var $PROJECT_DIR/pub $PROJECT_DIR/generated && log_done

# --- PHASE 3: CONFIGURATION (CSP & Developer Mode) ---
log_header "PHASE 3: SYSTEM CONFIGURATION"

log_task "Applying CSP Report-Only mode via SQL"
docker compose exec -T $DB_CONTAINER mysql -u $DB_USER -p$DB_PASS -e "
    INSERT INTO core_config_data (path, value) VALUES ('web/csp/mode/admin/report_only', '1') ON DUPLICATE KEY UPDATE value = '1';
    INSERT INTO core_config_data (path, value) VALUES ('web/csp/mode/storefront/report_only', '1') ON DUPLICATE KEY UPDATE value = '1';
" $DB_NAME && log_done

log_task "Disabling Static File Signing (Avoid 404 CSS)"
# Dòng mới thêm vào đây:
docker compose exec $PHP_CONTAINER bin/magento config:set dev/static/sign 0 > /dev/null && log_done

log_task "Setting Developer Mode"
docker compose exec $PHP_CONTAINER bin/magento deploy:mode:set developer > /dev/null && log_done

# --- PHASE 4: MAGENTO BUILD (Trọng tâm) ---
log_header "PHASE 4: MAGENTO BUILD PROCESS"

log_task "Running setup:upgrade (Database migration)"
docker compose exec $PHP_CONTAINER bin/magento setup:upgrade --keep-generated && log_done

log_task "Deploying Static Assets ($THEME_NAME)"
docker compose exec $PHP_CONTAINER bin/magento setup:static-content:deploy -f vi_VN en_US --theme $THEME_NAME > /dev/null && log_done

# --- PHASE 5: FINALIZATION ---
log_header "PHASE 5: FINALIZATION"

log_task "Final permission check"
sudo chmod -R 777 $PROJECT_DIR/var $PROJECT_DIR/pub $PROJECT_DIR/generated && log_done

log_task "Flushing Magento cache"
docker compose exec $PHP_CONTAINER bin/magento cache:flush > /dev/null && log_done

echo -e "\n${GREEN}${BOLD}CORE PROCESS COMPLETED SUCCESSFULLY${NC}"
echo -e "${BOLD}Hệ thống đã sẵn sàng với đầy đủ tính năng!${NC}\n"
