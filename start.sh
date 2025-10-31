#!/bin/bash
# Startup script for Science-Qur'an Integration

# Set environment to production
export NODE_ENV=production

# Navigate to application directory
cd /var/www/science-quran-integration

# Install dependencies if not already installed
npm install --production

# Start the application
node server/app.js