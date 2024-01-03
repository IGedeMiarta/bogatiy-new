<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->name('api.')->group(function () {
    Route::controller('AppController')->group(function(){
        Route::get('general-setting', 'generalSetting');
        Route::get('get-countries', 'getCountries');
        Route::get('policy/pages', 'policyPages');
    });

    Route::namespace('Auth')->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
            Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            Route::post('password/reset', 'reset')->name('password.update');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {

        //authorization
        Route::controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });

        Route::middleware(['check.status'])->group(function () {
            Route::post('user-data-submit', 'UserController@userDataSubmit')->name('data.submit');

            Route::middleware('registration.complete')->group(function () {

                Route::get('user-info', function () {
                    $notify[] = 'User information';
                    return response()->json([
                        'remark' => 'user_info',
                        'status' => 'success',
                        'message' => ['success' => $notify],
                        'data' => [
                            'user' => auth()->user()
                        ]
                    ]);
                });

                Route::controller('UserController')->group(function () {
                    Route::get('dashboard', 'dashboard');

                    //KYC
                    Route::get('kyc-form', 'kycForm')->name('kyc.form');
                    Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                    //profile setting
                    Route::post('profile-setting', 'submitProfile');
                    Route::post('change-password', 'submitPassword');
                    Route::get('wallets', 'wallets');
                    Route::post('wallets/update', 'walletUpdate');

                    //referral
                    Route::get('referral', 'referral');
                    Route::get('referral/log', 'referralLog');

                    //Report
                    Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                    Route::get('transactions', 'transactions')->name('transactions');
                });

                Route::controller('OrderPlanController')->group(function () {
                    Route::get('plans', 'plans');
                    Route::post('plan/purchase', 'orderPlan');
                    Route::get('purchased-plan', 'purchasedPlan')->name('plan.purchased');
                    Route::get('plans/active', 'activePlan')->name('plans.active');
                    Route::get('mining-track', 'miningTrack')->name('order.mining.track');
                });

                // Withdraw
                Route::controller('WithdrawController')->name('withdraw.')->prefix('withdraw')->group(function () {
                    Route::get('method', 'withdrawMethod')->name('method')->middleware('kyc');
                    Route::post('request', 'withdrawStore')->name('money')->middleware('kyc');
                    Route::get('history', 'withdrawLog')->name('history');
                });

                // Payment
                Route::controller('PaymentController')->group(function () {
                    Route::prefix('deposit')->group(function () {
                        Route::get('methods', 'methods')->name('deposit');
                        Route::post('insert', 'depositInsert')->name('deposit.insert');
                        Route::get('confirm', 'depositConfirm')->name('deposit.confirm');
                        Route::get('manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
                        Route::post('manual', 'manualDepositUpdate')->name('deposit.manual.update');
                    });
                    Route::get('payment/methods', 'paymentMethods')->name('payment.methods');
                });
            });
        });

        Route::get('logout', 'Auth\LoginController@logout');
    });
});
