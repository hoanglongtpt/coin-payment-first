php artisan vendor:publish --provider="Telegram\Bot\Laravel\TelegramServiceProvider"

curl -X POST https://api.telegram.org/bot7986087926:AAGfmsBI0RjCQxjNgaRII2ECUxYWhITkUb0/setWebhook?url=https://1b07-2405-4802-e62c-fcf0-9c12-aadc-3edf-d84.ngrok-free.app/api/telegram/webhook

composer require srmklive/paypal
php artisan vendor:publish --provider="RealRashid\SweetAlert\SweetAlertServiceProvider" --tag=config
php artisan vendor:publish --provider="RealRashid\SweetAlert\SweetAlertServiceProvider" --tag=views