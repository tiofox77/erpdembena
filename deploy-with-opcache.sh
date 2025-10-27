#!/bin/bash

###############################################################################
# Laravel Deployment Script with OPcache Optimization
# 
# This script deploys your Laravel application with full OPcache optimization
# 
# NOTE: The system automatically runs 'php artisan opcache:optimize --clear'
# during GitHub updates via the web interface (Settings > Updates)
###############################################################################

set -e  # Exit on error

echo "🚀 Starting Laravel deployment with OPcache optimization..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Enable maintenance mode
echo -e "${YELLOW}📝 Step 1: Enabling maintenance mode...${NC}"
php artisan down --message="Deploying new version. Please wait..." --retry=60
echo -e "${GREEN}   ✓ Maintenance mode enabled${NC}"
echo ""

# Step 2: Pull latest code (if using git)
if [ -d ".git" ]; then
    echo -e "${YELLOW}📥 Step 2: Pulling latest code from repository...${NC}"
    git pull origin main
    echo -e "${GREEN}   ✓ Code updated${NC}"
    echo ""
fi

# Step 3: Install/Update dependencies
echo -e "${YELLOW}📦 Step 3: Installing/Updating Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}   ✓ Dependencies updated${NC}"
echo ""

# Step 4: Run database migrations
echo -e "${YELLOW}🗄️  Step 4: Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}   ✓ Migrations completed${NC}"
echo ""

# Step 5: Clear all Laravel caches
echo -e "${YELLOW}🧹 Step 5: Clearing all caches...${NC}"
php artisan optimize:clear
echo -e "${GREEN}   ✓ Caches cleared${NC}"
echo ""

# Step 6: Rebuild Laravel caches
echo -e "${YELLOW}⚙️  Step 6: Building Laravel caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}   ✓ Laravel caches built${NC}"
echo ""

# Step 7: Clear and warm up OPcache
echo -e "${YELLOW}🔥 Step 7: Optimizing OPcache...${NC}"
php artisan opcache:optimize --clear --warm-up
echo -e "${GREEN}   ✓ OPcache optimized${NC}"
echo ""

# Step 8: Optimize storage and permissions
echo -e "${YELLOW}📂 Step 8: Optimizing storage...${NC}"
php artisan storage:link > /dev/null 2>&1 || true
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}   ✓ Storage optimized${NC}"
echo ""

# Step 9: Restart queue workers (if using queues)
if [ -f "artisan" ]; then
    echo -e "${YELLOW}🔄 Step 9: Restarting queue workers...${NC}"
    php artisan queue:restart
    echo -e "${GREEN}   ✓ Queue workers restarted${NC}"
    echo ""
fi

# Step 10: Disable maintenance mode
echo -e "${YELLOW}✅ Step 10: Disabling maintenance mode...${NC}"
php artisan up
echo -e "${GREEN}   ✓ Application is now live!${NC}"
echo ""

# Step 11: Show OPcache status
echo -e "${YELLOW}📊 Step 11: OPcache Status:${NC}"
php artisan opcache:optimize --status
echo ""

# Final message
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "Application URL: ${YELLOW}$(php artisan env | grep APP_URL | cut -d'=' -f2)${NC}"
echo -e "Deployed at: ${YELLOW}$(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo ""
