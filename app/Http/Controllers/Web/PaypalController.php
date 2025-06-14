<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Package;
use App\Models\VipCard;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Http;


class PaypalController extends Controller
{
    public function checkout(Request $request)
    {

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);


        $transaction_order = new Transaction();
        $transaction_order->member_id = $request->member_id;
        $transaction_order->package_sku = $request->package_sku;
        $transaction_order->amount = $request->price;
        $transaction_order->tokens_first_time = $request->tokens_first_time;
        $transaction_order->sale = $request->sale;
        $transaction_order->promotion = $request->promotion;
        $transaction_order->status = 2;
        $transaction_order->token_pay = $token['access_token'];
        $transaction_order->save();

        $response = $paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->price // Set the package price here
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('cancelTransaction'),
                "return_url" => route('successTransaction')
            ]
        ]);


        if (isset($response['id'])) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        } else {
            return redirect()->back()->with('error', 'An error occurred while creating the payment.');
        }
    }


    public function successTransaction(Request $request)
    {
        DB::beginTransaction();
        try {
            $paypal = new PayPalClient;
            $paypal->setApiCredentials(config('paypal'));
            $token = $paypal->getAccessToken();
            $paypal->setAccessToken($token);

            $response = $paypal->capturePaymentOrder($request->query('token'));

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                // Handle successful payment logic here (e.g., updating the database)

                $transaction_order = Transaction::where('token_pay', $token['access_token'])
                    ->orderBy('id', 'desc')
                    ->first();
                $transaction_order->status = 1;
                $transaction_order->save();


                $member = Member::find($transaction_order->member_id);
                if ($member) {
                    $member->account_balance += $transaction_order->tokens_first_time;
                    $member->promotion += $transaction_order->promotion;
                    $member->save();

                    $api_url = $member->type_bot === 'video'
                        ? "https://p2p.ainude.dev/add/video"
                        : "https://p2p.ainude.dev/add/photo";

                    $price = (int) $transaction_order->amount;
                    $user_id = (string) $member->telegram_id; // bạn dùng telegram_id (chuẩn từ mẫu)
                    $order_id = 'order_' . time();

                    $hmac_secret = "7b06a1045a6d1da2617b3d110eb0b545";
                    $data_string = $price . $user_id . $order_id; // giữ đúng thứ tự này như mẫu
                    $hmac_key = hash_hmac('sha256', $data_string, $hmac_secret);

                    $payload = [
                        'price' => $price,
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'key' => $hmac_key,
                    ];

                    $response_api = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post($api_url, $payload);

                    // Debug để so sánh nếu lỗi
                    Log::debug('AINUDE DEBUG', [
                        'data_string' => $data_string,
                        'key' => $hmac_key,
                        'payload' => $payload,
                        'response' => $response_api->body(),
                    ]);

                    if (!$response_api->successful()) {
                        Log::warning('AINUDE API call failed: ' . $response_api->body());
                    }


                    $this->sendTelegramNotification($member, $transaction_order);
                }

                DB::commit();
                Alert::success("Success",  "Pay success!")->autoClose(2000);
                if ($member->type_bot == 'video') {
                    return redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id]);
                } else {
                    return redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
                }
            } else {

                $transaction_order = Transaction::where('token_pay', $token['access_token'])
                    ->orderBy('id', 'desc')
                    ->first();
                $transaction_order->status = 3;
                $transaction_order->save();
                $member = Member::find($transaction_order->member_id);

                DB::commit();
                Alert::error("Success",  "Pay success!")->autoClose(2000);
                if ($member->type_bot == 'video') {
                    return redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id]);
                } else {
                    return redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi call method pay: ' . $e->getMessage());
            Alert::error("Error", "Pay Fail")->autoClose(2000);
            return redirect()->back();
        }
    }

    /**
     */
    private function sendTelegramNotification($member, $transaction)
    {
        try {
            if ($member->type_bot == 'video') {
                $botToken = env('TELEGRAM_BOT_TOKEN_VIDEO');
            } else {
                $botToken = env('TELEGRAM_BOT_TOKEN_PHOTO');
            }
            $chatId = $member->telegram_id;
            $message = "🎉 Payment successful!\n\n" .
                "👤 Member: {$member->telegram_id}\n" .
                "📦 Package: {$transaction->amount}\n" .
                "💰 Amount: {$transaction->amount} USD\n" .
                "🎁 Bonus points: {$transaction->tokens_first_time} 🎟️\n" .
                "🎉 Promotion: {$transaction->promotion} 🍀\n" .
                "🕒 Time: " . now()->format('d/m/Y H:i:s') . "\n" .
                "📜 Transaction ID: {$transaction->id}";


            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('error: ' . $e->getMessage());
        }
    }

    public function cancelTransaction(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();

        if (empty($token['access_token'])) {
            Alert::error("Error", "Unable to get PayPal token")->autoClose(2000);
            return back();
        }

        $paypal->setAccessToken($token);

        $transaction_order = Transaction::where('token_pay', $request->input('token_pay'))
            ->orderBy('id', 'desc')
            ->first();

        if (!$transaction_order) {
            Alert::error("Error", "Transaction not found")->autoClose(2000);
            return redirect()->route('web.index.photo');
        }

        $transaction_order->status = 3;
        $transaction_order->save();

        $member = Member::find($transaction_order->member_id);

        Alert::error("Payment Failed", "Your payment was not completed.")->autoClose(2000);

        return $member->type_bot === 'video'
            ? redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id])
            : redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
    }


    // Checkout VIP
    public function checkout_vip(Request $request)
    {

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);


        $transaction_order = new Transaction();
        $transaction_order->member_id = $request->member_id;
        $transaction_order->package_sku = $request->package_sku;
        $transaction_order->amount = $request->price;
        $transaction_order->tokens_first_time = $request->tokens_first_time;
        $transaction_order->sale = $request->sale;
        $transaction_order->promotion = $request->promotion;
        $transaction_order->status = 2;
        $transaction_order->token_pay = $token['access_token'];
        $transaction_order->save();

        $response = $paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->price
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('cancelTransaction.vip'),
                "return_url" => route('successTransaction.vip')
            ]
        ]);


        if (isset($response['id'])) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        } else {
            return redirect()->back()->with('error', 'An error occurred while creating the payment.');
        }
    }

    public function cancelTransaction_vip(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();

        if (empty($token['access_token'])) {
            Alert::error("Error", "Unable to get PayPal token")->autoClose(2000);
            return back();
        }

        $paypal->setAccessToken($token);

        $transaction_order = Transaction::where('token_pay', $request->input('token_pay'))
            ->orderBy('id', 'desc')
            ->first();

        if (!$transaction_order) {
            Alert::error("Error", "Transaction not found")->autoClose(2000);
            return redirect()->route('web.index.photo');
        }

        $transaction_order->status = 3;
        $transaction_order->save();

        $member = Member::find($transaction_order->member_id);

        Alert::error("Payment Failed", "Your payment was not completed.")->autoClose(2000);

        return $member->type_bot === 'video'
            ? redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id])
            : redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
    }

    public function successTransaction_vip(Request $request)
    {
        DB::beginTransaction();
        try {
            $paypal = new PayPalClient;
            $paypal->setApiCredentials(config('paypal'));
            $token = $paypal->getAccessToken();
            $paypal->setAccessToken($token);

            $response = $paypal->capturePaymentOrder($request->query('token'));

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                // Handle successful payment logic here (e.g., updating the database)

                $transaction_order = Transaction::where('token_pay', $token['access_token'])
                    ->orderBy('id', 'desc')
                    ->first();
                $transaction_order->status = 1;
                $transaction_order->save();

                $member = Member::find($transaction_order->member_id);
                if ($member) {
                    $member->account_balance += $transaction_order->tokens_first_time;
                    $member->promotion += $transaction_order->promotion;
                    $member->save();

                    $api_url = $member->type_bot === 'video'
                        ? "https://p2p.ainude.dev/add/video"
                        : "https://p2p.ainude.dev/add/photo";

                    $price = (int) $transaction_order->amount;
                    $user_id = (string) $member->telegram_id; 
                    $order_id = 'order_' . time();

                    $hmac_secret = "7b06a1045a6d1da2617b3d110eb0b545";
                    $data_string = $price . $user_id . $order_id; 
                    $hmac_key = hash_hmac('sha256', $data_string, $hmac_secret);

                    $payload = [
                        'price' => $price,
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'key' => $hmac_key,
                    ];

                    $response_api = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post($api_url, $payload);

                    Log::debug('AINUDE DEBUG', [
                        'data_string' => $data_string,
                        'key' => $hmac_key,
                        'payload' => $payload,
                        'response' => $response_api->body(),
                    ]);

                    if (!$response_api->successful()) {
                        Log::warning('AINUDE API call failed: ' . $response_api->body());
                    }

                    $this->sendTelegramNotification_vip($member, $transaction_order);
                }

                DB::commit();
                Alert::success("Success",  "Pay success!")->autoClose(2000);
                if ($member->type_bot == 'video') {
                    return redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id]);
                } else {
                    return redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
                }
            } else {

                $transaction_order = Transaction::where('token_pay', $token['access_token'])
                    ->orderBy('id', 'desc')
                    ->first();
                $transaction_order->status = 3;
                $transaction_order->save();
                $member = Member::find($transaction_order->member_id);

                DB::commit();
                Alert::error("Success",  "Pay success!")->autoClose(2000);
                if ($member->type_bot == 'video') {
                    return redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id]);
                } else {
                    return redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi call method pay: ' . $e->getMessage());
            Alert::error("Error", "Pay Fail")->autoClose(2000);
            return redirect()->back();
        }
    }

    private function sendTelegramNotification_vip($member, $transaction)
    {
        try {
            if ($member->type_bot == 'video') {
                $botToken = env('TELEGRAM_BOT_TOKEN_VIDEO');
            } else {
                $botToken = env('TELEGRAM_BOT_TOKEN_PHOTO');
            }
            $chatId = $member->telegram_id;
            $message = "🎉 Payment successful!\n\n" .
                "👤 Member: {$member->telegram_id}\n" .
                "📦 VIP Package: {$transaction->amount} USD\n" .
                "💰 Paid Amount: {$transaction->amount} USD\n" .
                "🎁 Bonus Tickets: {$transaction->tokens_first_time} 🎟️\n" .
                "🕒 Time: " . now()->format('d/m/Y H:i:s') . "\n" .
                "📜 Transaction ID: {$transaction->id}";


            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('error: ' . $e->getMessage());
        }
    }
}
