# Application Process Management for Science-Qur'an Integration

# This configuration is for PM2 (Process Manager 2) for Node.js applications

module.exports = {
  apps: [{
    name: 'science-quran-integration',
    script: './server/app.js',
    instances: 'max', // Use all CPU cores
    exec_mode: 'cluster', // Use cluster mode for better performance
    env: {
      NODE_ENV: 'development',
      PORT: 3000,
    },
    env_production: {
      NODE_ENV: 'production',
      PORT: 3000,
      DB_HOST: 'localhost',
      DB_USER: 'science_quran_user',
      DB_PASS: 'your_secure_password',
      DB_NAME: 'science_quran',
    },
    error_file: '/var/log/science-quran-integration/err.log',
    out_file: '/var/log/science-quran-integration/out.log',
    log_file: '/var/log/science-quran-integration/combined.log',
    time: true,
    max_restarts: 5,
    max_memory_restart: '1G',
    min_uptime: '10s',
    wait_ready: true,
    listen_timeout: 10000,
    kill_timeout: 5000,
    autorestart: true,
    node_args: ['--max-http-header-size=8192'],
    watch: false, // Don't use in production
  }]
};