@extends('admin.layouts.app')
@section('panel')
    @if (@json_decode($general->system_info)->version > systemDetails()['version'])
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-warning mb-3 text-white">
                    <div class="card-header">
                        <h3 class="card-title"> @lang('New Version Available') <button class="btn btn--dark float-end">@lang('Version') {{ json_decode($general->system_info)->version }}</button> </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-dark">@lang('What is the Update?')</h5>
                        <p>
                            <pre class="f-size--24">{{ json_decode($general->system_info)->details }}</pre>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (@json_decode($general->system_info)->message)
        <div class="row">
            @foreach (json_decode($general->system_info)->message as $msg)
                <div class="col-md-12">
                    <div class="alert border--primary border" role="alert">
                        <div class="alert__icon bg--primary">
                            <i class="far fa-bell"></i>
                        </div>
                        <p class="alert__message">@php echo $msg; @endphp</p>
                        <button class="close" data-bs-dismiss="alert" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_users'] }}" title="Total Users" style="2" link="{{ route('admin.users.all') }}" icon="las la-users f-size--56" icon_style="solid" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['verified_users'] }}" title="Active Users" style="2" color="success" link="{{ route('admin.users.active') }}" icon="las la-user-check f-size--56" icon_style="solid" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['email_unverified_users'] }}" title="Email Unverified Users" style="2" color="danger" link="{{ route('admin.users.email.unverified') }}" icon="lar la-envelope f-size--56" icon_style="solid" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['mobile_unverified_users'] }}" title="Mobile Unverified Users" style="2" color="red" link="{{ route('admin.users.mobile.unverified') }}" icon="las la-comment-slash f-size--56" icon_style="solid" />
        </div>
    </div><!-- row end-->

    <!-- Miner widget -->
    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_miner'] }}" title="Total Miner" style="2" color="info" link="{{ route('admin.miner.index') }}" icon="la la-hammer f-size--56" icon_style="solid" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_plan'] }}" title="Total Mining Plan" style="2" color="primary" link="{{ route('admin.plan.index') }}" icon="la la-list f-size--56" icon_style="solid" />
        </div>

        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_sale_count'] }}" title="Total Sale" style="2" color="success" link="{{ route('admin.order.index') }}" icon="la la-list-alt f-size--56" icon_style="solid" />
        </div>

        <div class="col-xxl-3 col-sm-6">

            <x-widget value="{{ $general->cur_sym }}{{ showAmount($widget['total_sale_amount']) }}" title="Total Sale Amount" style="2" color="dark" link="{{ route('admin.order.index') }}" icon="la la-money-bill f-size--56" icon_style="solid" />

        </div>
    </div><!-- row end-->

    <div class="row mb-none-30 mt-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title">@lang('Returned Amount') (@lang('Last 12 Month'))</h5>
                        <select class="form-control w-auto" name="currency" data-type="return_amount">
                            @foreach ($coinCodes as $coinCode)
                                <option value="{{ $coinCode }}">{{ strtoupper($coinCode) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="returnedAmountChart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title">@lang('Transactions Report') (@lang('Last 30 Days'))</h5>
                        <select class="form-control w-auto" name="currency" data-type="transaction">
                            @foreach ($transactionCurrencies as $transactionCurrency)
                                <option value="{{ $transactionCurrency }}" @selected($transactionCurrency == gs()->cur_text)>{{ strtoupper($transactionCurrency) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="transactionChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-none-30 mt-5">
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Browser') (@lang('Last 30 days'))</h5>
                    <canvas id="userBrowserChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By OS') (@lang('Last 30 days'))</h5>
                    <canvas id="userOsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Country') (@lang('Last 30 days'))</h5>
                    <canvas id="userCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.cron_instruction')
@endsection

@push('breadcrumb-plugins')

    @if ($general->last_cron)
        <a class="btn @if (Carbon\Carbon::parse($general->last_cron)->diffInSeconds() < 600) btn--success @elseif(Carbon\Carbon::parse($general->last_cron)->diffInSeconds() < 1200) btn--warning @else
        btn--danger @endif" href="javascript:void(0)"><i class="fa fa-fw fa-clock"></i>@lang('Last Cron Run') :
            {{ Carbon\Carbon::parse($general->last_cron)->difFforHumans() }}</a>
    @endif

@endpush

@push('script')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>

    <script>
        "use strict";

        $('[name=currency]').on('change', function() {
            let currency = $(this).val();
            let type = $(this).data('type');
            $.ajax({
                type: "get",
                url: "{{ route('admin.get_chart_data') }}",
                data: {
                    currency: currency,
                    type: type
                },
                success: function(response) {
                    if (type == 'return_amount') {
                        let trxReport = response.trxReport;
                        var options = {
                            series: [{
                                name: 'Total Returned',
                                data: trxReport.amount
                            }],
                            chart: {
                                type: 'bar',
                                height: 450,
                                toolbar: {
                                    show: false
                                }
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '50%',
                                    endingShape: 'rounded'
                                },
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: ['transparent']
                            },
                            xaxis: {
                                categories: response.months,
                            },
                            yaxis: {
                                title: {
                                    text: response.currency,
                                    style: {
                                        color: '#7c97bb'
                                    }
                                }
                            },
                            grid: {
                                xaxis: {
                                    lines: {
                                        show: false
                                    }
                                },
                                yaxis: {
                                    lines: {
                                        show: false
                                    }
                                },
                            },
                            fill: {
                                opacity: 1
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + ` ${response.currency}` + " "
                                    }
                                }
                            }
                        };
                        $('.returnedAmountChart').html(`<div id="apex-bar-chart"></div>`);
                        var chart = new ApexCharts(document.querySelector("#apex-bar-chart"), options);
                        chart.render();
                    }

                    if (type == 'transaction') {
                        let trxReport = response.trxReport;
                        var options = {
                            chart: {
                                height: 450,
                                type: "area",
                                toolbar: {
                                    show: false
                                },
                                dropShadow: {
                                    enabled: true,
                                    enabledSeries: [0],
                                    top: -2,
                                    left: 0,
                                    blur: 10,
                                    opacity: 0.08
                                },
                                animations: {
                                    enabled: true,
                                    easing: 'linear',
                                    dynamicAnimation: {
                                        speed: 1000
                                    }
                                },
                            },
                            dataLabels: {
                                enabled: false
                            },
                            series: [{
                                    name: "Plus Transactions",
                                    data: trxReport.plus_trx_amount
                                },
                                {
                                    name: "Minus Transactions",
                                    data: trxReport.minus_trx_amount
                                }
                            ],
                            fill: {
                                type: "gradient",
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.7,
                                    opacityTo: 0.9,
                                    stops: [0, 90, 100]
                                }
                            },
                            xaxis: {
                                categories: trxReport.date
                            },
                            grid: {
                                padding: {
                                    left: 5,
                                    right: 5
                                },
                                xaxis: {
                                    lines: {
                                        show: false
                                    }
                                },
                                yaxis: {
                                    lines: {
                                        show: false
                                    }
                                },
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + ` ${response.currency}` + " "
                                    }
                                }
                            },
                            yaxis: {
                                title: {
                                    text: ` ${response.currency}`,
                                    style: {
                                        color: '#7c97bb'
                                    }
                                }
                            },
                        };

                        $('.transactionChart').html(`<div id="apex-line"></div>`);

                        var chart = new ApexCharts(document.querySelector("#apex-line"), options);
                        chart.render();
                    }
                }
            });
        }).change();





        var ctx = document.getElementById('userBrowserChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_browser_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_browser_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                maintainAspectRatio: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });



        var ctx = document.getElementById('userOsChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_os_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_os_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(0, 0, 0, 0.05)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            },
        });


        // Donut chart
        var ctx = document.getElementById('userCountryChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_country_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_country_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });
    </script>
@endpush
