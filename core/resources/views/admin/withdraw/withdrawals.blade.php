@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">

                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Transaction Number')</th>
                                    @if (!request()->routeIs('admin.users.withdrawals'))
                                        <th>@lang('Username')</th>
                                    @endif
                                    <th>@lang('Wallet')</th>
                                    <th>@lang('Amount')</th>
                                    @if (request()->routeIs('admin.withdraw.log'))
                                        <th>@lang('Status')</th>
                                    @endif
                                    <th>@lang('Action')</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdraw)
                                    <tr>
                                        <td>
                                            {{ showDateTime($withdraw->created_at) }}
                                        </td>
                                        <td class="fw-bold">
                                            {{ $withdraw->trx }}
                                        </td>

                                        @if (!request()->routeIs('admin.users.withdrawals'))
                                            <td>
                                                <a href="{{ route('admin.users.detail', $withdraw->user_id) }}">{{ @$withdraw->user->username }}</a>
                                            </td>
                                        @endif

                                        <td>
                                            <span class="fw-bold">{{ @$withdraw->userCoinBalance->wallet }}</span>
                                        </td>

                                        <td>
                                            <strong>{{ showAmount($withdraw->amount, 8, exceptZeros:true) }} {{ __(strtoupper($withdraw->userCoinBalance->miner->coin_code)) }}</strong>

                                        </td>

                                        @if (request()->routeIs('admin.withdraw.log'))
                                            <td>
                                                @php echo $withdraw->statusBadge @endphp
                                            </td>
                                        @endif

                                        <td>
                                            <a href="{{ route('admin.withdraw.details', $withdraw->id) }}" class="btn btn-sm btn-outline--primary ms-1">
                                                <i class="la la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($withdrawals->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($withdrawals) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection


@push('breadcrumb-plugins')
    <x-search-form dateSearch='yes' />
@endpush
