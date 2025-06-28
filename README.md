Instalación: composer install cp .env.example .env php artisan key:generate php artisan migrate --seed npm install EN SHELL COMO ADMINISTRADOR: Get-ExecutionPolicy Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned

Para iniciar: npm run dev php artisan serve

para migraciones: php artisan migrate:fresh --seed

para ruta nueva creada o repo clonado: php artisan route:clear php artisan route:cache

Intrucciones para produccion: composer install --optimize-autoloader --no-dev npm install && npm run build contenido de storage/app/public

Para evitar Laravel\SerializableClosure\Exceptions\InvalidSignatureException:
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache


para actualizar repositorio
git reset --hard origin/master
git pull origin master
sudo chmod 777 storage/logs/ -R
sudo chmod 777 storage/ -R
sudo service php8.3-fpm restart
sudo service nginx restart
sudo php artisan config:clear
sudo php artisan config:cache
sudo php artisan route:clear
sudo php artisan route:cache


php artisan storage:link