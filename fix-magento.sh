#!/bin/bash

# --- Load Environment Variables ---
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo -e "\033[0;31m[ERROR]\033[0m .env file not found. Please copy .env.example to .env and configure it."
    exit 1
fi

# --- ANSI Color Codes ---
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' 
BOLD='\033[1m'

# --- Logging Functions ---
log_header() {
    echo -e "\n${BOLD}===================================================================="
    echo -e "  $1"
    echo -e "====================================================================${NC}"
}

log_task() {
    echo -e "${BLUE}[PROCESSING]${NC} $1..."
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# --- 1. Permissions Check ---
log_header "PHASE 1: SYSTEM PERMISSIONS"

log_task "Synchronizing ownership for $USER"
sudo chown -R $USER:$USER $PROJECT_DIR/
log_success "Ownership synchronized."

# --- 2. Assets Cleanup ---
log_header "PHASE 2: CLEANUP & ASSET PATCHING"

log_task "Patching Email LESS errors"
EMAIL_CSS_PATH="$PROJECT_DIR/app/design/frontend/$THEME_NAME/web/css"
mkdir -p "$EMAIL_CSS_PATH"
echo "body { background-color: transparent !important; }" | sudo tee $EMAIL_CSS_PATH/email.less > /dev/null
echo "body { background-color: transparent !important; }" | sudo tee $EMAIL_CSS_PATH/email-inline.less > /dev/null

log_task "Purging var, generated, and static content"
sudo rm -rf $PROJECT_DIR/var/cache/* $PROJECT_DIR/var/page_cache/* $PROJECT_DIR/var/view_preprocessed/*
sudo rm -rf $PROJECT_DIR/generated/code/* $PROJECT_DIR/pub/static/*

sudo chmod -R 777 $PROJECT_DIR/var $PROJECT_DIR/generated $PROJECT_DIR/pub
log_success "Environment cleaned."

# --- 3. Magento Configuration ---
log_header "PHASE 3: SYSTEM CONFIGURATION"

log_task "Applying CSP Report-Only mode via SQL"
docker compose exec -T $DB_CONTAINER mysql -u $DB_USER -p$DB_PASS -e "
    INSERT INTO core_config_data (path, value) VALUES ('web/csp/mode/admin/report_only', '1') ON DUPLICATE KEY UPDATE value = '1';
    INSERT INTO core_config_data (path, value) VALUES ('web/csp/mode/storefront/report_only', '1') ON DUPLICATE KEY UPDATE value = '1';
" $DB_NAME

log_task "Optimizing developer settings"
docker compose exec $PHP_CONTAINER bin/magento config:set dev/static/sign 0 > /dev/null
docker compose exec $PHP_CONTAINER bin/magento deploy:mode:set developer > /dev/null
log_success "System settings optimized."

# --- 4. Deployment ---
log_header "PHASE 4: COMPILATION & STATIC CONTENT"

log_task "Running setup:upgrade"
docker compose exec $PHP_CONTAINER bin/magento setup:upgrade --keep-generated

log_task "Deploying Static Assets (vi_VN, en_US)"
docker compose exec $PHP_CONTAINER bin/magento setup:static-content:deploy -f vi_VN en_US --theme $THEME_NAME

log_success "Static assets deployed."

# --- 5. Finalize ---
log_header "PHASE 5: FINALIZATION"

sudo chmod -R 777 $PROJECT_DIR/var $PROJECT_DIR/generated $PROJECT_DIR/pub
docker compose exec $PHP_CONTAINER bin/magento cache:flush

echo -e "\n${GREEN}${BOLD}CORE PROCESS COMPLETED SUCCESSFULLY${NC}"
echo -e "${BOLD}====================================================================${NC}\n"
