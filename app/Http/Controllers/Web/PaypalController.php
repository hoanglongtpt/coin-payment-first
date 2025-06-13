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
use Telegram\Bot\Api; // Nếu dùng gói telegram-bot-sdk
use Illuminate\Support\Facades\Http; // Nếu dùng HTTP Client


class PaypalController extends Controller
{
    public function checkout(Request $request)
    {

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        // tạo mới transaction - status: pending

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

        // Tạo payment (sử dụng phương thức create)
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


        // Kiểm tra nếu thanh toán đã được tạo thành công
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
                    $member->account_balance += $transaction_order->sale; // Cộng tiền vào tài khoản của thành viên
                    $member->promotion += $transaction_order->promotion; // Cộng tiền vào tài khoản của thành viên
                    $member->save();

                    // Gửi thông báo Telegram
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
     * Gửi thông báo Telegram
     */
    private function sendTelegramNotification($member, $transaction)
    {
        try {
            if ($member->type_bot == 'video') {
                $botToken = env('TELEGRAM_BOT_TOKEN_VIDEO');
            } else {
                $botToken = env('TELEGRAM_BOT_TOKEN_PHOTO');
            }
            $chatId = $member->telegram_id; // Lấy telegram_id từ member
            $message = "🎉 Thanh toán thành công!\n\n" .
                "👤 Thành viên: {$member->telegram_id}\n" .
                "📦 Gói: {$transaction->amount}\n" .
                "💰 Số tiền: {$transaction->amount} USD\n" .
                "🎁 Điểm thưởng: {$transaction->sale} 🎟️\n" .
                "🎉 Khuyến mãi: {$transaction->promotion} 🍀\n" .
                "🕒 Thời gian: " . now()->format('d/m/Y H:i:s') . "\n" .
                "📜 Mã giao dịch: {$transaction->id}";

            // Sử dụng HTTP Client để gửi tin nhắn
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('Gửi thông báo Telegram thất bại: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi thông báo Telegram: ' . $e->getMessage());
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
        $vipcard = VipCard::findOrFail($request->vip_card_id);  // Lấy thông tin gói từ cơ sở dữ liệu

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        // tạo mới transaction - status: pending

        $transaction_order = new Transaction();
        $transaction_order->member_id = $request->member_id;
        $transaction_order->vip_card_id = $request->vip_card_id;
        $transaction_order->amount = $vipcard->amount_usd;
        $transaction_order->status = 2;
        $transaction_order->token_pay = $token['access_token'];
        $transaction_order->save();

        // Tạo payment (sử dụng phương thức create)
        $response = $paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $vipcard->amount_usd // Set the package price here
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('cancelTransaction.vip'),
                "return_url" => route('successTransaction.vip')
            ]
        ]);


        // Kiểm tra nếu thanh toán đã được tạo thành công
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
                    $member->account_balance += $vipcard->ticket_count; // Cộng tiền vào tài khoản của thành viên
                    $member->save();

                    // Gửi thông báo Telegram
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
            $chatId = $member->telegram_id; // Lấy telegram_id từ member
            $message = "🎉 Thanh toán thành công!\n\n" .
                "👤 Thành viên: {$member->telegram_id}\n" .
                "📦 Gói Vip: {$vipcard->amount_usd} USD\n" .
                "💰 Số tiền: {$transaction->amount} USD\n" .
                "🎁 Điểm thưởng: {$vipcard->ticket_count} 🎟️\n" .
                "📜 Mô tả: {$vipcard->description} 🎟️\n" .
                "🕒 Thời gian: " . now()->format('d/m/Y H:i:s') . "\n" .
                "📜 Mã giao dịch: {$transaction->id}";

            // Sử dụng HTTP Client để gửi tin nhắn
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('Gửi thông báo Telegram thất bại: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi thông báo Telegram: ' . $e->getMessage());
        }
    }
}
