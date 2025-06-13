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
            $url = 'https://p2p.ainude.dev/pancake/'.$type_bot;
            $response = Http::get($url); // Laravel HTTP client

            if ($response->successful()) {
                $data = $response->json(); 

                $choices = $data['choices'] ?? [];

                
                $packages = array_values(array_filter($choices, function ($item) {
                    return empty($item['subscription']) || $item['subscription'] !== true;
                }));
            } else {
                
                $packages = [];
            }
        }
        
        

        
        $member = Member::where('telegram_id', $telegramId)->where('type_bot',$type_bot)->first();

        if ($member) {
            
            Session::put('telegram_id', $telegramId);
            Session::put('type_bot', $type_bot);

            
            if ($member->wheel_status == 2) {
                $canSpin = true;
            } else {
                $canSpin = false;
            }

            return view('web.payments.index', compact(['canSpin','member','packages','vipCards']));
        } else {
            Member::create([
                'telegram_id' => $telegramId,
                'promotion' => 0,
                'type_bot' => $type_bot,
                'account_balance' => 0.00,
                'wheel_status' => 2, 
            ]);

            
            Session::put('telegram_id', $telegramId);
            Session::put('type_bot', $type_bot);

            
            $canSpin = true;

            return view('web.payments.index', compact(['canSpin','member','packages','vipCards','type_bot']));
        }
    }

    public function checkUser($telegram_id,$type_bot)
    {
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
        $telegramId = Session::get('telegram_id');
        $type_bot = Session::get('type_bot');
        
        $member = Member::where('telegram_id', $telegramId)->where('type_bot', $type_bot)->first();

        if ($member && $member->wheel_status == 2) {
            $reward = 50;  

            
            $member->promotion = $reward;
            $member->wheel_status = 1;  
            $member->save();

            return response()->json([
                'status' => 'success',
                'reward' => $reward . '🍀',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'You cannot spin the wheel at this time.',
        ]);
    }

    public function check_telegram_id(){
        return view('web.payments.noti_check_telegram_id');
    }
}
