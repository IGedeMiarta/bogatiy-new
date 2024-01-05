@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row d-flex justify-content-center" style="margin-top: -100px">
        <div class="col-lg-4 col-sm-6 col-xsm-6 mb-3">
            <div class="dashboard-widget flex-align">
                <span class="dashboard-widget__icon flex-center before-shadow"><span class="icon-Money"></span></span>
                <div class="dashboard-widget__content">
                    <span class="dashboard-widget__text">@lang('Referral Bonuses')</span>
                    <h4 class="dashboard-widget__title">{{ showAmount($referral) }}
                        {{ __($general->cur_text) }}</h4>
                </div>
            </div>
        </div>
        <div class="card custom--card col-md-12">
            <div class="card-body">
                <div class="form-group mb-4">
                    <label class="d-flex justify-content-between">
                        <span class="form--label">@lang('Referral Link')</span>
                        @if (auth()->user()->referrer)
                            <span class="text--info form--label">@lang('You are referred by')
                                {{ auth()->user()->referrer->fullname }}</span>
                        @endif
                    </label>
                    <div class="input-group">
                        <input class="form-control form--control referralURL" name="text" type="text"
                            value="{{ route('home') }}?ref={{ auth()->user()->username }}" readonly="">
                        <button class="input-group-text btn btn--base btn--sm copytext copyBoard" id="copyBoard"> <i
                                class="fa fa-copy"></i> </button>
                    </div>
                </div>
                @if ($user->allReferrals->count() > 0 && $maxLevel > 0)
                    <label>@lang('My Referrals')</label>
                    <div class="treeview-container">
                        <ul class="treeview">
                            <li class="items-expanded"> {{ $user->fullname }} ( {{ $user->username }} )
                                @include($activeTemplate . 'partials.under_tree', [
                                    'user' => $user,
                                    'layer' => 0,
                                    'isFirst' => true,
                                ])
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="card custom--card col-md-12 mt-3">
            <div class="card-body">
                <h2>Logs</h2>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table--responsive--md">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Level')</th>
                                    <th>@lang('Percent')</th>
                                    <th>@lang('Time')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $k => $data)
                                    <tr>
                                        <td>{{ $data->referee->username }}</td>
                                        <td>
                                            {{ showAmount($data->amount) }} {{ __($general->cur_text) }}
                                        </td>
                                        <td>
                                            {{ $data->level }}
                                        </td>
                                        <td>
                                            {{ showAmount($data->percent) }}%
                                        </td>

                                        <td>
                                            {{ showDateTime($data->created_at) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ paginateLinks($logs) }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@push('style')
    <link type="text/css" href="{{ asset('assets/global/css/jquery.treeView.css') }}" rel="stylesheet">
@endpush
@push('script')
    <script src="{{ asset('assets/global/js/jquery.treeView.js') }}"></script>
    <script>
        (function($) {
            "use strict";

            $('.treeview').treeView();
            $('.copyBoard').click(function() {
                var copyText = document.getElementsByClassName("referralURL");
                copyText = copyText[0];
                copyText.select();
                copyText.setSelectionRange(0, 99999);

                /*For mobile devices*/
                document.execCommand("copy");
                notify('success', "Copied: " + copyText.value);
            });
        })(jQuery);
    </script>
@endpush
