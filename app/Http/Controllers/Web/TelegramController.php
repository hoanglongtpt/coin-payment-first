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
                $domain = request()->getSchemeAndHttpHost(); 
                // $domain = 'http://127.0.0.1:8000';
                $paymentUrl = $domain . "/video/" . $chatId;

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Link payment : $paymentUrl"
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
                $domain = request()->getSchemeAndHttpHost(); 
                // $domain = 'http://127.0.0.1:8000';
                $paymentUrl = $domain . "/photo/" . $chatId;

                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Link payment : $paymentUrl"
                ]);
            }
        }

        return response('ok');
    }
}
