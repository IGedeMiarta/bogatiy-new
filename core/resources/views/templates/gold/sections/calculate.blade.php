@php
    $calculate = getContent('calculate.content', true);
    $miners = App\Models\Miner::with('plans')
        ->whereHas('plans', function ($query) {
            $query->where('status', 1);
        })
        ->orderBy('name', 'ASC')
        ->get();
    // dd($miners);
@endphp


<section class="calculator pt-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mb-5">
                <div class="calculator-content" style="height: 250px">
                    <h3 class="calculator-content__title text-center">{{ __(@$calculate->data_values->heading) }}</h3>
                    <div class="calculator-content__inner">
                        <form action="#" class="">
                            <div class="row ">
                                <div class="col-sm-6 col-xsm-6">
                                    <label for="select-coin" class="form--label">@lang('Select Coin')</label>
                                    <select class="select form--control" id="select-coin" name="miner">
                                        <option value="" disabled>@lang('Select Coin')</option>
                                        @foreach ($miners as $miner)
                                            <option data-coin_code="{{ strtoupper($miner->coin_code) }}"
                                                data-plans="{{ $miner->plans }}" value="{{ $miner->id }}"
                                                @selected($loop->first)>{{ __($miner->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6 col-xsm-6">
                                    <label for="nutanminers" class="form--label">@lang('Select Plan')</label>
                                    <select class="select form--control revenue-calculate plans" id="nutanminers">
                                        <option value="" disabled>@lang('Select Plan')</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                        {{-- <div class="calculator-content__revenue  revenue-area">
                            <span class="text text--gradient fw-bold banner-calculator__text"></span>
                            <h2 class="mb-0 banner-calculator__number">0</h2>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="calculator-content" style="height: 250px">
                    <h4 class="calculator-content__title text-center ">@lang('Estimated Daily Revenue')</h4>
                    <hr>
                    <div class="calculator-content__inner mt-5">
                        <div class="revenue-area text-center">
                            <h3 class="mb-0 banner-calculator__number text--gradient">0</h3>
                            <h3 class="mb-0 banner-calculator__coin_code text--gradient">BTC</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('script')
    <script>
        'use strict';
        (function($) {
            $('select[name="miner"]').on('change', function() {
                var plans = $(this).find(':selected').data('plans');
                var coin_code = $(this).find(':selected').data('coin_code');
                var output =
                    `<select class="revenue-calculate"> <option value="" disabled>@lang('Select Plan')</option>`;

                if (plans?.length != 0) {
                    $.each(plans, function(key, plan) {
                        var period = totalPeriodInDay(plan.period, plan.period_unit);
                        var per_day = 0;
                        if (plan.max_return_per_day) {
                            let a = trimTrailingZeros(plan.min_return_per_day)
                            let b = trimTrailingZeros(plan.max_return_per_day)
                            per_day = a + ' - ' + b

                        } else {
                            per_day = trimTrailingZeros(plan.max_return_per_day)

                        }
                        output += `<option value="${per_day}"> ${plan.title} </option>`;
                    });

                    output += '</select>'

                    $('.plans').html(output);
                }

                // $('.revenue-area .sub-title').hide('slow')
                $('.revenue-area .title').hide('slow')
                $('.revenue-calculate').change();
            }).change();

            let revenue = $('.revenue-calculate').find(":selected").val();
            if (revenue) {
                $('.revenue-area .banner-calculator__number').text(revenue).hide().show();
            }

            function totalPeriodInDay(time_limit, type) {
                if (type == 0)
                    return time_limit;

                else if (type == 1)
                    return time_limit * 30;

                else if (type == 2)
                    return time_limit * 365;
            }

            $(document).on('change', '.revenue-calculate', function() {
                $('.revenue-area .banner-calculator__text').text(`@lang('Estimated Daily Revenue')`).show();
                $('.revenue-area .banner-calculator__number').text($(this).val()).hide().show();
            });

            function trimTrailingZeros(number) {
                // Convert the number to a string to remove trailing zeros
                let trimmedNumber = number.toString();

                // Remove trailing zeros after the decimal point
                if (trimmedNumber.includes('.')) {
                    trimmedNumber = trimmedNumber.replace(/0+$/, '');
                }

                return trimmedNumber;
            }

        })(jQuery)
    </script>
@endpush
