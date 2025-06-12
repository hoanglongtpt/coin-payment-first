php artisan vendor:publish --provider="Telegram\Bot\Laravel\TelegramServiceProvider"

curl -X POST https://api.telegram.org/bot7986087926:AAGfmsBI0RjCQxjNgaRII2ECUxYWhITkUb0/setWebhook?url=https://87ba-2a09-bac5-d46a-16d2-00-246-dd.ngrok-free.app/api/telegram/webhook

composer require srmklive/paypal
php artisan vendor:publish --provider="RealRashid\SweetAlert\SweetAlertServiceProvider" --tag=config
php artisan vendor:publish --provider="RealRashid\SweetAlert\SweetAlertServiceProvider" --tag=views