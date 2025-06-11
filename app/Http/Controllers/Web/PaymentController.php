<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        $packages = Package::all();
        // Lấy telegram_id từ URL
        $telegramId = $request->query('telegram_id');

        if (!$telegramId) {
            return redirect()->route('web.check_telegram_id');
        }

        // Kiểm tra nếu telegram_id đã tồn tại trong bảng members
        $member = Member::where('telegram_id', $telegramId)->first();

        if ($member) {
            // Nếu đã tồn tại, tiếp tục đến trang bình thường
            // Lưu telegram_id vào session để tránh mất khi reload
            Session::put('telegram_id', $telegramId);

            // Kiểm tra wheel_status, nếu là 2 thì cho phép quay
            if ($member->wheel_status == 2) {
                // Đã cho phép quay
                $canSpin = true;
            } else {
                // Không cho phép quay vì đã quay rồi
                $canSpin = false;
            }

            return view('web.payments.index', compact(['canSpin','member','packages']));
        } else {
            // Nếu chưa có, thêm mới vào bảng members
            Member::create([
                'telegram_id' => $telegramId,
                'promotion' => 0,
                'account_balance' => 0.00,
                'wheel_status' => 2, // Đặt wheel_status là 2 cho phép quay lần đầu
            ]);

            // Lưu telegram_id vào session
            Session::put('telegram_id', $telegramId);

            // Sau khi thêm mới, cho phép quay vòng quay
            $canSpin = true;

            return view('web.payments.index', compact(['canSpin','member','packages']));
        }
    }

    public function spin(Request $request)
    {
        // Lấy telegram_id từ session
        $telegramId = Session::get('telegram_id');
        
        // Kiểm tra nếu telegram_id tồn tại trong bảng members
        $member = Member::where('telegram_id', $telegramId)->first();

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
