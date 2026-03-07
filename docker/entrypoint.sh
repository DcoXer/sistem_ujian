#!/bin/sh  
set -e  
cd /var/www/html  
if [ ! -f .env ] && [ -f .env.example ]; then  
  cp .env.example .env  
fi  
php artisan config:cache  
php artisan route:cache  
php artisan view:cache  
# Create Caddyfile for reverse proxy  
cat > /etc/caddy/Caddyfile <<'EOF'  
:8080 {  
    root * /var/www/html/public  
      
    encode gzip  
      
    @notStatic {  
        not {  
            path /build/*  
            path /storage/*  
            path /favicon.ico  
            path /robots.txt  
        }  
    }  
      
    rewrite @notStatic /index.php?{query}  
      
    php_fastcgi 127.0.0.1:9000 {  
        split .php  
    }  
      
    file_server  
}  
EOF  
# Start PHP-FPM in background  
php-fpm &  
# Keep lifecycle scheduler running in-app  
php artisan schedule:work &  
# Start Caddy in foreground  
exec caddy run --config /etc/caddy/Caddyfile  
