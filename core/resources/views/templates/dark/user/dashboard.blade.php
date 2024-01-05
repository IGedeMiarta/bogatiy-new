@extends($activeTemplate . 'layouts.master')
@section('content')
    @if ($general->kv && auth()->user()->kv != Status::KYC_VERIFIED)
        @php
            $kycInstruction = getContent('kyc_instruction.content', true);
        @endphp
        <div class="row mb-3">
            <div class="container">
                <div class="row">
                    @if (auth()->user()->kv == Status::KYC_UNVERIFIED)
                        <div class="col-12">
                            <div class="alert alert-info mb-0" role="alert">
                                <h5 class="alert-heading m-0">@lang('KYC Verification Required')</h5>
                                <hr>
                                <p class="mb-0"> {{ __($kycInstruction->data_values->verification_instruction) }} <a class="text--base" href="{{ route('user.kyc.form') }}">@lang('Click Here to Verify')</a></p>
                            </div>
                        </div>
                    @elseif(auth()->user()->kv == Status::KYC_PENDING)
                        <div class="col-12">
                            <div class="alert alert-warning mb-0" role="alert">
                                <h5 class="alert-heading m-0">@lang('KYC Verification pending')</h5>
                                <hr>
                                <p class="mb-0"> {{ __($kycInstruction->data_values->pending_instruction) }} <a class="text--base" href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- dashboard-section start -->
    <div class="row gy-4 dashboard-card-wrapper">
        <div class="col-xl-6 col-sm-6">
            <div class="dashboard-card border-bottom-info">
                <div class="dashboard-card__thumb-title">
                    <div class="dashboard-card__thumb rounded-0 border-0">
                        <i class="las la-money-bill fa-4x"></i>
                    </div>
                    <h5 class="dashboard-card__title"> @lang('Balance')</h5>
                </div>
                <div class="dashboard-card__content">
                    <h4 class="dashboard-card__Status">{{ showAmount(auth()->user()->balance) }} {{ __($general->cur_text) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-sm-6">
            <div class="dashboard-card border-bottom-violet">
                <div class="dashboard-card__thumb-title">
                    <div class="dashboard-card__thumb rounded-0 border-0">
                        <i class="las la-wallet fa-4x"></i>
                    </div>
                    <h5 class="dashboard-card__title"> @lang('Referral Bonus')</h5>
                </div>
                <div class="dashboard-card__content">
                    <h4 class="dashboard-card__Status">{{ showAmount($referralBonus) }} {{ __($general->cur_text) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex mt-4 flex-wrap gap-3">

        @foreach ($miners as $item)
            <div class="dashboard-card border-bottom-violet">
                <div class="dashboard-card__thumb-title">
                    <div class="dashboard-card__thumb">
                        <img src="{{ getImage(getFilePath('miner') . '/' . $item->coin_image, getFileSize('miner')) }}" alt="@lang('Image')">
                    </div>
                    <h5 class="dashboard-card__title"> <span>{{ strtoupper($item->coin_code) }}</span> @lang('Wallet')</h5>
                </div>
                <div class="dashboard-card__content">
                    <h4 class="dashboard-card__Status">{{ showAmount($item->userCoinBalances->balance, 8, exceptZeros: true) }} {{ strtoupper($item->coin_code) }}</h4>
                </div>
            </div>
        @endforeach
    </div>
    <!-- dashboard-section end -->

    <div class="pt-40">
        <h5>@lang('Latest Transactions')</h5>
        <div class="dashboard-table">
            @include($activeTemplate . 'partials.transaction_table', ['transactions' => $transactions])
        </div>
    </div>
@endsection
