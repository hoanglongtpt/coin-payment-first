<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;

class TelegramController extends Controller
{
    public function webhook_video(Request $request)
    {
        // Log::info('Received Telegram webhook:', $request->all());

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN_VIDEO'));
        $update = $telegram->getWebhookUpdate();

        if ($update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();
            $text = $update->getMessage()->getText();

            if (strtolower($text) === '/start') {
                // Lấy domain hiện tại và tạo URL payment
                $domain = request()->getSchemeAndHttpHost(); // Ví dụ: https://1b07-2405-4802-e62c-fcf0-9c12-aadc-3edf-d84.ngrok-free.app
                // $domain = 'http://127.0.0.1:8000';
                $paymentUrl = $domain . "/video/" . $chatId;

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Link payment của bạn là: $paymentUrl"
                ]);
            }
        }

        return response('ok');
    }

    public function webhook_photo(Request $request)
    {
        Log::info('Received Telegram webhook:', $request->all());

        $telegram = new Api(env('TELEGRAM_BOT_TOKEN_PHOTO'));
        $update = $telegram->getWebhookUpdate();

        if ($update->getMessage()) {
            $chatId = $update->getMessage()->getChat()->getId();
            $text = $update->getMessage()->getText();

            if (strtolower($text) === '/start') {
                // Lấy domain hiện tại và tạo URL payment
                $domain = request()->getSchemeAndHttpHost(); // Ví dụ: https://1b07-2405-4802-e62c-fcf0-9c12-aadc-3edf-d84.ngrok-free.app
                // $domain = 'http://127.0.0.1:8000';
                $paymentUrl = $domain . "/photo/" . $chatId;

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Link payment của bạn là: $paymentUrl"
                ]);
            }
        }

        return response('ok');
    }
}
