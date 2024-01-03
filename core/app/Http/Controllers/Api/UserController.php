<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\Deposit;
use App\Models\Form;
use App\Models\GeneralSetting;
use App\Models\Miner;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserCoinBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $referralLink  = route('home') . "?ref" . $user->username;

        $widget['referral_bonus'] = showAmount(ReferralLog::where('referee_id', $user->id)->sum('amount'));
        $widget['balance'] = showAmount($user->balance);

        $miners     = Miner::with(['userCoinBalances' => function ($q) use ($user) {
            return $q->where('user_id', $user->id);
        }])->whereHas('userCoinBalances', function ($q) use ($user) {
            return $q->where('user_id', $user->id);
        })->get();

        $plan['pending'] = Order::where('user_id', $user->id)->pending()->count();
        $plan['approved'] = Order::where('user_id', $user->id)->approved()->count();
        $plan['rejected'] = Order::where('user_id', $user->id)->rejected()->count();
        $plan['unpaid'] = Order::where('user_id', $user->id)->unpaid()->count();

        $transactions =  $transactions = Transaction::where('user_id', $user->id)->orderBy('id', 'desc')->limit(10)->get();

        $coinImagePath = getFilePath('miner');


        return response()->json([
            'remark' => 'dashboard_data',
            'status' => 'success',
            'message' => ['success' => ['All dashboard data']],
            'data' => [
                'referral_link' => $referralLink,
                'coin_image_path' => $coinImagePath,
                'widget'  => $widget,
                'miners' => $miners,
                'transactions' => $transactions,
                'plan' => $plan
            ]
        ]);
    }

    public function userDataSubmit(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_complete == 1) {
            $notify[] = 'You\'ve already completed your profile';
            return response()->json([
                'remark' => 'already_completed',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }


        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'city' => $request->city,
        ];
        $user->profile_complete = 1;
        $user->save();

        $notify[] = 'Profile completed successfully';
        return response()->json([
            'remark' => 'profile_completed',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function kycForm()
    {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = 'Your KYC is under review';
            return response()->json([
                'remark' => 'under_review',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = 'You are already KYC verified';
            return response()->json([
                'remark' => 'already_verified',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $form = Form::where('act', 'kyc')->first();
        $notify[] = 'KYC field is below';
        return response()->json([
            'remark' => 'kyc_form',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'form' => $form->form_data
            ]
        ]);
    }

    public function kycSubmit(Request $request)
    {
        $form = Form::where('act', 'kyc')->first();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $validator = Validator::make($request->all(), $validationRule);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $userData = $formProcessor->processFormData($request, $formData);
        $user = auth()->user();
        $user->kyc_data = $userData;
        $user->kv = Status::KYC_PENDING;
        $user->save();

        $notify[] = 'KYC data submitted successfully';
        return response()->json([
            'remark' => 'kyc_submitted',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function depositHistory(Request $request)
    {
        $deposits = auth()->user()->deposits();
        if ($request->search) {
            $deposits = $deposits->where('trx', $request->search);
        }
        $deposits = $deposits->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        $notify[] = 'Deposit data';
        return response()->json([
            'remark' => 'deposits',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'deposits' => $deposits
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $remarks = Transaction::distinct('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->search) {
            $transactions = $transactions->where('trx', $request->search);
        }


        if ($request->type) {
            $type = $request->type == 'plus' ? '+' : '-';
            $transactions = $transactions->where('trx_type', $type);
        }

        if ($request->coin_code) {
            $transactions = $transactions->where('currency', $request->coin_code);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark', $request->remark);
        }

        $transactions = $transactions->orderBy('id', 'desc')->paginate(getPaginate());
        $notify[] = 'Transactions data';
        return response()->json([
            'remark' => 'transactions',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'transactions' => $transactions,
                'remarks' => $remarks,
            ]
        ]);
    }

    public function submitProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'city' => $request->city,
        ];
        $user->save();

        $notify[] = 'Profile updated successfully';
        return response()->json([
            'remark' => 'profile_updated',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        $general = GeneralSetting::first();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', $passwordValidation]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = 'Password changed successfully';
            return response()->json([
                'remark' => 'password_changed',
                'status' => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'The password doesn\'t match!';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }

    public function wallets()
    {
        $user  = User::where('id', auth()->id())->with('coinBalances:id,user_id,miner_id,wallet,balance', 'coinBalances.miner')->first();

        if (!$user) {
            $notify[] = 'User doesn\'t exist!';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        return response()->json([
            'remark' => 'wallets',
            'status' => 'success',
            'message' => ['success' => ['User coin wallets']],
            'data' => [
                'coin_balances' => $user->coinBalances
            ]
        ]);
    }

    public function walletUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "wallet" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()]
            ]);
        }
        $userCoinBalance = UserCoinBalance::where('user_id', auth()->id())->find($request->id);

        if (!$userCoinBalance) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => 'Wallet not found']
            ]);
        }

        $userCoinBalance->wallet = $request->wallet;
        $userCoinBalance->save();

        $notify[] = 'Wallet address updated successfully';

        return response()->json([
            'remark' => 'wallet_updated',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function referral()
    {
        $general = gs();

        if (!$general->referral_system) {
            $notify[] = 'Sorry, the referral system is currently unavailable';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $maxLevel = Referral::max('level');

        $relations = [];
        for ($label = 1; $label <= $maxLevel; $label++) {
            $relations[$label] = (@$relations[$label - 1] ? $relations[$label - 1] . '.allReferrals' : 'allReferrals');
        }
        $user = auth()->user()->load($relations);

        $referrals = getReferees($user, $maxLevel);

        return response()->json([
            'remark' => 'referrals',
            'status' => 'success',
            'message' => ['success' => ['Referral']],
            'data' => [
                'referral_link' => route('home') . '?ref=' . auth()->user()->username,
                'maxLevel' => $maxLevel,
                'referrals' => $referrals
            ]
        ]);
    }

    public function referralLog()
    {
        $general = gs();

        if (!$general->referral_system) {
            $notify[] = 'Sorry, the referral system is currently unavailable';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $logs = ReferralLog::where('referee_id', auth()->id())->with('referee')->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'remark' => 'referral_logs',
            'status' => 'success',
            'message' => ['success' => ['Referral Logs']],
            'data' => [
                'logs' => $logs
            ]
        ]);
    }
}
