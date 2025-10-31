#!/bin/bash
# Health check script for Science-Qur'an Integration

# Configuration
APP_NAME="science-quran-integration"
HEALTH_CHECK_URL="http://localhost:3000/health"
TIMEOUT=10

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a /var/log/${APP_NAME}-health.log
}

# Check if application is responding
if curl -f --max-time $TIMEOUT $HEALTH_CHECK_URL > /dev/null 2>&1; then
    STATUS=$(curl -s --max-time $TIMEOUT $HEALTH_CHECK_URL | jq -r '.status' 2>/dev/null)
    if [ "$STATUS" = "OK" ]; then
        log_message "Application is healthy"
        exit 0
    else
        log_message "Application is running but health check failed"
        exit 1
    fi
else
    log_message "Application is not responding"
    exit 1
fi