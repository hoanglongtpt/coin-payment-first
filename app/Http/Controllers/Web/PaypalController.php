<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api; // Nếu dùng gói telegram-bot-sdk
use Illuminate\Support\Facades\Http; // Nếu dùng HTTP Client


class PaypalController extends Controller
{
    public function checkout(Request $request)
    {
        $package = Package::findOrFail($request->package_id);  // Lấy thông tin gói từ cơ sở dữ liệu

        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        // tạo mới transaction - status: pending

        $transaction_order = new Transaction();
        $transaction_order->member_id = $request->member_id;
        $transaction_order->package_id = $request->package_id;
        $transaction_order->amount = $package->price;
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
                        "value" => $package->price // Set the package price here
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
            return redirect()->route('web.index')->with('error', 'An error occurred while creating the payment.');
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

                $package = Package::find($transaction_order->package_id);

                if ($package) {
                    $member = Member::find($transaction_order->member_id);
                    $member->account_balance += $package->reward_points; // Cộng tiền vào tài khoản của thành viên
                    $member->promotion += $package->bonus; // Cộng tiền vào tài khoản của thành viên
                    $member->save();

                    // Gửi thông báo Telegram
                    $this->sendTelegramNotification($member, $package, $transaction_order);
                }

                DB::commit();
                Alert::success( "Success" ,  "Pay success!" )->autoClose(2000);
                return redirect()->route('web.index',['telegram_id' => $member->telegram_id]);
            } else {

                $transaction_order = Transaction::where('token_pay', $token['access_token'])
                                ->orderBy('id', 'desc') 
                                ->first();
                $transaction_order->status = 3;
                $transaction_order->save();
                $member = Member::find($transaction_order->member_id);

                DB::commit();
                Alert::error("Success" ,  "Pay success!")->autoClose(2000);
                return redirect()->route('web.index',['telegram_id' => $member->telegram_id]);
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
    private function sendTelegramNotification($member, $package, $transaction)
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = $member->telegram_id; // Lấy telegram_id từ member
            $message = "🎉 Thanh toán thành công!\n\n" .
                    "👤 Thành viên: {$member->telegram_id}\n" .
                    "📦 Gói: {$package->name}\n" .
                    "💰 Số tiền: {$transaction->amount} USD\n" .
                    "🎁 Điểm thưởng: {$package->reward_points} 🎟️\n" .
                    "🎉 Khuyến mãi: {$package->bonus} 🍀\n" .
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
        return redirect()->route('web.index',['telegram_id' => $member->telegram_id]);
    }
}
