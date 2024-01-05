<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Miner;
use App\Models\Order;
use App\Models\Plan;
use App\Models\ReferralNetwork;
use App\Models\ReferralNetworkLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserCoinBalance;
use Illuminate\Http\Request;

class OrderPlanController extends Controller {
    public function plans() {
    
        $pageTitle = "Mining Plans";
        $miners    = Miner::with('activePlans')->whereHas('activePlans')->get();
        return view($this->activeTemplate . 'user.plans.index', compact('pageTitle', 'miners'));
    }
    public function orderPlan(Request $request) {
        $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'payment_method' => 'required|integer|between:1,2',
        ], [
            'payment_method.required' => 'Please Select a Payment System',
        ]);

        $plan = Plan::active()->with('miner')->findOrFail($request->plan_id);
        
        switch ($plan->price) {
            case 10:
                $network_lv = 1;
                break;
            case 100:
                $network_lv = 2;
                break; 
            case 1000:
                $network_lv = 3;
                break;
            case 5000:
                $network_lv = 4;
                break;
            case 10000:
                $network_lv = 5;
                break; 
            default:
                $network_lv = 0;
                break;
        }

        $user = auth()->user();

        if ($request->payment_method == 1 && $user->balance < $plan->price) {
            $notify[] = ['error', 'Insufficient balance'];
            return back()->withNotify($notify);
        }

        $planDetails = [
            'title'        => $plan->title,
            'miner'        => $plan->miner->name,
            'speed'        => $plan->speed . ' ' . $plan->speedUnitText,
            'period'       => $plan->period . ' ' . $plan->periodUnitText,
            'period_value' => $plan->period,
            'period_unit'  => $plan->period_unit,
        ];

        $order                     = new Order();
        $order->trx                = getTrx();
        $order->user_id            = $user->id;
        $order->plan_details       = $planDetails;
        $order->amount             = $plan->price;
        $order->min_return_per_day = $plan->min_return_per_day;
        $order->max_return_per_day = $plan->max_return_per_day ?? $plan->min_return_per_day;
        $order->miner_id           = $plan->miner->id;
        $order->maintenance_cost   = $plan->maintenance_cost;
        $period                    = totalPeriodInDay($plan->period, $plan->period_unit);
        $order->period             = $period;
        $order->period_remain      = $period;
        $order->last_paid          = (new \DateTime())->format('Y-m-d H:i:s');

        if ($request->payment_method == 1) {
            $order->status        = Status::ORDER_APPROVED;
            $order->save();

            //Check If Exists
            $ucb = UserCoinBalance::where('user_id', $user->id)->where('miner_id', $order->miner_id)->firstOrCreate([
                'user_id'  => $user->id,
                'miner_id' => $order->miner_id,
            ]);

            $user->balance -= $order->amount;
            $user->save();

            $general  = gs();
            $referrer = $user->referrer;
            if ($general->referral_system && $referrer) {
                levelCommission($user, $order->amount, $order->trx);
            }

            $transaction               = new Transaction();
            $transaction->user_id      = $order->user_id;
            $transaction->amount       = getAmount($order->amount);
            $transaction->charge       = 0;
            $transaction->currency     = $general->cur_text;
            $transaction->post_balance = $user->balance;
            $transaction->trx_type     = '-';
            $transaction->details      = 'Paid to buy a plan';
            $transaction->remark       = 'payment';
            $transaction->trx          = $order->trx;
            $transaction->save();

            //set referral_networks;
            $network = new ReferralNetwork();
            $network->user_id = $user->id;
            $network->network_lv = $network_lv;
            $network->save();

            $this->sendRefNetwork($user,$order->trx,$network_lv,$plan->price);

            notify($user, 'PAYMENT_VIA_USER_BALANCE', [
                'plan_title'      => $plan->title,
                'amount'          => showAmount($order->amount),
                'method_currency' => $general->cur_text,
                'post_balance'    => showAmount($user->balance),
                'method_name'     => $general->cur_text . ' Balance',
                'order_id'        => $order->trx,
            ]);

            $notify[] = ['success', 'Plan purchased successfully.'];

            return redirect()->route('user.plans.purchased')->withNotify($notify);
        } else {
            $order->status = Status::ORDER_UNPAID;
            $order->save();
            return redirect()->route('user.payment', encrypt($order->id));
        }
    }
    public function miningTracks() {
        $pageTitle = "Mining Tracks";
        $orders     = Order::where('user_id', auth()->id())->with('miner')->orderBy('id', 'desc')->where('status',1)->first();
        $isOrder    = Order::where('user_id', auth()->id())->with('miner')->orderBy('id', 'desc')->where('status',1)->count();
        $transactions        = Transaction::where('user_id', auth()->id())->where('remark','return_amount')->orWhere('remark','maintenance_cost')->orderByDesc('id')->paginate(getPaginate(4));
        return view($this->activeTemplate . 'user.plans.purchased', compact('pageTitle', 'orders','isOrder','transactions'));
    }

    function sendRefNetwork($user,$trx,$network_lv,$planPrice){
        $i = 1;
        while ($i<= $network_lv) { 
            $refUser[1] = $user->ref_by;
            $user[$i] = User::with('network')->find($refUser[$i]);
            if (!$user[$i]) {
                break;
            }
            if (!$user[$i]->network?->network_lv >= $network_lv) {
                break;
            }
            $getBonus   = $planPrice * $i/100;
            
            $user[$i]->balance -= $getBonus;
            $user[$i]->save();

            $refLog[] = [
                'user_id'    => $user[$i]->id,
                'amount'     => $getBonus,
                'level'      => $i,
                'percent'    => $i,
            ];

            $referralNetwork = ReferralNetworkLog::where('user_id',  $user[$i]->id)->sum('amount');
            $transactions[] = [
                'user_id'      => $user[$i]->id,
                'amount'       => $getBonus,
                'post_balance' => $referralNetwork + $getBonus,
                'charge'       => 0,
                'trx_type'     => '+',
                'details'      => 'You have received referral network commission from ' . $user->username,
                'trx'          => $trx,
                'remark'       => 'referral_commission',
                'currency'     => gs()->cur_text,
                'created_at'   => now(),
            ];
            $refUser[$i+1] =  $user[$i]->ref_by ?? 0;
            $i++;
        }
        if ($transactions) {
            Transaction::insert($transactions);
        }
        if ($refLog) {
            ReferralNetworkLog::insert($refLog);
        }
    }
}
