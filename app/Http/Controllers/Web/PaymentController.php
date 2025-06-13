<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Package;
use App\Models\VipCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;


class PaymentController extends Controller
{

    public function index(Request $request)
    {
        $telegramId = $request->telegram_id;
        $type_bot = str_contains(request()->fullUrl(), 'video') ? 'video' : 'photo';

        if (!$telegramId) {
            // Alert::error("Error", "Sorry, we couldn't find your account in our system")->autoClose(2000);
            return redirect()->route('web.check_telegram_id');
        }

        $checkUser = $this->checkUser($telegramId, $type_bot);
        if (!$checkUser) {
            Alert::error("Error", "Sorry, we couldn't find your account in our system")->autoClose(2000);
            return redirect()->route('web.check_telegram_id');
        }

        $vipCards = VipCard::all();
        if ($type_bot) {
            // Gọi API từ ainude.dev
            $url = 'https://p2p.ainude.dev/pancake/'.$type_bot;
            $response = Http::get($url); // Laravel HTTP client

            if ($response->successful()) {
                $data = $response->json(); // Trả về dạng mảng

                $choices = $data['choices'] ?? [];

                // Lọc bỏ các gói có subscription = true
                $packages = array_values(array_filter($choices, function ($item) {
                    return empty($item['subscription']) || $item['subscription'] !== true;
                }));
            } else {
                // Trong trường hợp API lỗi → fallback
                $packages = [];
            }
        }
        // Lấy telegram_id từ URL
        

        // Kiểm tra nếu telegram_id đã tồn tại trong bảng members
        $member = Member::where('telegram_id', $telegramId)->where('type_bot',$type_bot)->first();

        if ($member) {
            // Nếu đã tồn tại, tiếp tục đến trang bình thường
            // Lưu telegram_id vào session để tránh mất khi reload
            Session::put('telegram_id', $telegramId);
            Session::put('type_bot', $type_bot);

            // Kiểm tra wheel_status, nếu là 2 thì cho phép quay
            if ($member->wheel_status == 2) {
                // Đã cho phép quay
                $canSpin = true;
            } else {
                // Không cho phép quay vì đã quay rồi
                $canSpin = false;
            }

            return view('web.payments.index', compact(['canSpin','member','packages','vipCards']));
        } else {
            // Nếu chưa có, thêm mới vào bảng members
            Member::create([
                'telegram_id' => $telegramId,
                'promotion' => 0,
                'type_bot' => $type_bot,
                'account_balance' => 0.00,
                'wheel_status' => 2, // Đặt wheel_status là 2 cho phép quay lần đầu
            ]);

            // Lưu telegram_id vào session
            Session::put('telegram_id', $telegramId);
            Session::put('type_bot', $type_bot);

            // Sau khi thêm mới, cho phép quay vòng quay
            $canSpin = true;

            return view('web.payments.index', compact(['canSpin','member','packages','vipCards','type_bot']));
        }
    }

    public function checkUser($telegram_id,$type_bot)
    {
        // Gọi API kiểm tra từ Ainude
        $url = "https://p2p.ainude.dev/me/".$type_bot."/".$telegram_id;

        $response = Http::get($url);
        if ($response->successful()) {
            $data = $response->json();
            return $data['exists'];
        } else {
            return false;
        }
    }

    public function spin(Request $request)
    {
        // Lấy telegram_id từ session
        $telegramId = Session::get('telegram_id');
        $type_bot = Session::get('type_bot');
        
        // Kiểm tra nếu telegram_id tồn tại trong bảng members
        $member = Member::where('telegram_id', $telegramId)->where('type_bot', $type_bot)->first();

        if ($member && $member->wheel_status == 2) {
            // Giả sử bạn có một kết quả mặc định cho vòng quay may mắn
            $reward = 50;  // Nếu không có reward thì mặc định là 50

            // Cập nhật promotion và wheel_status
            $member->promotion = $reward;
            $member->wheel_status = 1;  // Đặt wheel_status thành 1 sau khi quay
            $member->save();

            // Trả về kết quả cho view
            return response()->json([
                'status' => 'success',
                'reward' => $reward . '🍀',
            ]);
        }

        // Nếu không thể quay (wheel_status không phải 2), trả về lỗi
        return response()->json([
            'status' => 'error',
            'message' => 'You cannot spin the wheel at this time.',
        ]);
    }

    public function check_telegram_id(){
        return view('web.payments.noti_check_telegram_id');
    }
}
