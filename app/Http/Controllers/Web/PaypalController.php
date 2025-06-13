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
                    $member->account_balance += $transaction_order->sale;
                    $member->promotion += $transaction_order->promotion;
                    $member->save();

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
                "🎁 Bonus points: {$transaction->sale} 🎟️\n" .
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
        $paypal->setAccessToken($token);

        $transaction_order = Transaction::where('token_pay', $token['access_token'])
            ->orderBy('id', 'desc')
            ->first();
        $transaction_order->status = 3;
        $transaction_order->save();


        $member = Member::find($transaction_order->member_id);
        // Rollback the transaction if necessary
        // Handle the cancellation logic here
        Alert::error("Error", "Pay Fail")->autoClose(2000);
        if ($member->type_bot == 'video') {
            return redirect()->route('web.index.video', ['telegram_id' => $member->telegram_id]);
        } else {
            return redirect()->route('web.index.photo', ['telegram_id' => $member->telegram_id]);
        }
    }

    // Checkout VIP
    public function checkout_vip(Request $request)
    {
        $vipcard = VipCard::findOrFail($request->vip_card_id);

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);


        $transaction_order = new Transaction();
        $transaction_order->member_id = $request->member_id;
        $transaction_order->vip_card_id = $request->vip_card_id;
        $transaction_order->amount = $vipcard->amount_usd;
        $transaction_order->status = 2;
        $transaction_order->token_pay = $token['access_token'];
        $transaction_order->save();

        $response = $paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $vipcard->amount_usd
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
            return redirect()->route('web.index')->with('error', 'An error occurred while creating the payment.');
        }
    }

    public function cancelTransaction_vip(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        $transaction_order = Transaction::where('token_pay', $token['access_token'])
            ->orderBy('id', 'desc')
            ->first();
        $transaction_order->status = 3;
        $transaction_order->save();


        $member = Member::find($transaction_order->member_id);
        // Rollback the transaction if necessary
        // Handle the cancellation logic here
        Alert::error("Error", "Pay Fail")->autoClose(2000);
        return redirect()->route('web.index', ['telegram_id' => $member->telegram_id]);
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

                $vipcard = VipCard::find($transaction_order->vip_card_id);

                if ($vipcard) {
                    $member = Member::find($transaction_order->member_id);
                    $member->account_balance += $vipcard->ticket_count;
                    $member->save();

                    $this->sendTelegramNotification_vip($member, $vipcard, $transaction_order);
                }

                DB::commit();
                Alert::success("Success",  "Pay success!")->autoClose(2000);
                return redirect()->route('web.index', ['telegram_id' => $member->telegram_id]);
            } else {

                $transaction_order = Transaction::where('token_pay', $token['access_token'])
                    ->orderBy('id', 'desc')
                    ->first();
                $transaction_order->status = 3;
                $transaction_order->save();
                $member = Member::find($transaction_order->member_id);

                DB::commit();
                Alert::error("Success",  "Pay success!")->autoClose(2000);
                return redirect()->route('web.index', ['telegram_id' => $member->telegram_id]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi call method pay: ' . $e->getMessage());
            Alert::error("Error", "Pay Fail")->autoClose(2000);
            return redirect()->back();
        }
    }

    private function sendTelegramNotification_vip($member, $vipcard, $transaction)
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = $member->telegram_id;
            $message = "🎉 Payment successful!\n\n" .
                "👤 Member: {$member->telegram_id}\n" .
                "📦 VIP Package: {$vipcard->amount_usd} USD\n" .
                "💰 Paid Amount: {$transaction->amount} USD\n" .
                "🎁 Bonus Tickets: {$vipcard->ticket_count} 🎟️\n" .
                "📜 Description: {$vipcard->description} 🎟️\n" .
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
