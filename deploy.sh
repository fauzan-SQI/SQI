#!/bin/bash
# Deployment script for Science-Qur'an Integration on VPS

# Exit immediately if a command exits with a non-zero status
set -e

# Configuration
APP_NAME="science-quran-integration"
APP_DIR="/var/www/$APP_NAME"
BACKUP_DIR="/var/backups/$APP_NAME"
LOG_FILE="/var/log/$APP_NAME-deploy.log"

echo "$(date): Starting deployment of $APP_NAME" | tee -a $LOG_FILE

# Create backup of current version
if [ -d "$APP_DIR" ]; then
    echo "$(date): Creating backup of current installation" | tee -a $LOG_FILE
    mkdir -p "$BACKUP_DIR"
    cp -r "$APP_DIR" "$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S)" || echo "Warning: Could not create backup"
fi

# Create application directory if it doesn't exist
mkdir -p "$APP_DIR"

# Copy application files (excluding development files)
rsync -av --exclude '.git' --exclude 'node_modules' --exclude '.env.local' --exclude 'TODO.md' ./ "$APP_DIR/"

# Navigate to application directory
cd "$APP_DIR"

# Install/update dependencies
echo "$(date): Installing/updating dependencies" | tee -a $LOG_FILE
npm install --production

# Set proper permissions
echo "$(date): Setting proper permissions" | tee -a $LOG_FILE
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 644 "$APP_DIR"/*.html
chmod -R 644 "$APP_DIR"/assets/*
chmod -R 644 "$APP_DIR"/server/*

# Verify environment configuration
if [ ! -f ".env" ]; then
    echo "$(date): WARNING - .env file not found. Please create one with proper configuration." | tee -a $LOG_FILE
else
    echo "$(date): Environment file found." | tee -a $LOG_FILE
fi

# Test database connection (optional - requires mysql client)
if command -v mysql &> /dev/null; then
    echo "$(date): Testing database connection" | tee -a $LOG_FILE
    DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2 | head -c -1)
    DB_USER=$(grep DB_USER .env | cut -d '=' -f2 | head -c -1)
    DB_NAME=$(grep DB_NAME .env | cut -d '=' -f2 | head -c -1)
    
    if [ -n "$DB_HOST" ] && [ -n "$DB_USER" ] && [ -n "$DB_NAME" ]; then
        # Test connection (this is a basic test)
        echo "SELECT 1;" | mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > /dev/null 2>&1 && \
        echo "$(date): Database connection test passed" | tee -a $LOG_FILE || \
        echo "$(date): WARNING - Database connection test failed" | tee -a $LOG_FILE
    fi
fi

# Restart application service (if using systemd)
if systemctl is-active --quiet "$APP_NAME"; then
    echo "$(date): Restarting application service" | tee -a $LOG_FILE
    systemctl restart "$APP_NAME"
else
    echo "$(date): Application service not found or not active" | tee -a $LOG_FILE
fi

# Wait for application to start
sleep 5

# Check if application is running
if curl -f http://localhost:3000/health > /dev/null 2>&1; then
    echo "$(date): Application is running and responding to health check" | tee -a $LOG_FILE
else
    echo "$(date): WARNING - Application may not be running properly" | tee -a $LOG_FILE
fi

echo "$(date): Deployment completed" | tee -a $LOG_FILE