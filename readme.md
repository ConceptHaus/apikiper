Kiper 2.0

Instalación

1. Obtener el repositorio y colocarlo en la carpeta del servidor local
2. Crear un archivo .env a partir del .env.example contenido en el repositorio
3. Otorgar permisos de escritura a las carpetas storage & bootstrap localizadas en la raíz del proyecto.
4. Composer install
5. Ejecutar migraciones -> php artisan migrate
6. Si se trabajará con una base de datos nueva y sin registros, entonces ejecutar seeders -> php artisan db:seed
7. Si se trabajará con una base de datos antigua, entonces ejecutar seeders -> php artisan db:seed --class RolesTableSeeder.php
8. Si se trabajará con una base de datos antigua, entonces ejecutar seguidamente al paso 7 -> php artisan db:seed --class SettingsTableSeeder
9. Ajustar dominios en servidor local para que la API pueda responder

Crontab

Para programar una tarea en Forge

1. Scheduler->New Scheduled Job
2. Introducir php7.3 /home/forge/qa.kiper.io/artisan schedule:run en el campo Command
3. Introducir forge en el campo User
4. Seleccionar la frecuencia de la tarea
5. Click en el botón CREATE

