@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">


        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card b-radius--10 box--shadow1 overflow-hidden">
                <div class="card-body">
                    <h5 class="text-muted mb-20">@lang('Withdraw from - ') <span class="fw-bold">{{ strtoupper($withdrawal->userCoinBalance->miner->coin_code) }} @lang('Wallet')</span></h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($withdrawal->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Trx Number')
                            <span class="fw-bold">{{ $withdrawal->trx }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="fw-bold">
                                <a href="{{ route('admin.users.detail', $withdrawal->user_id) }}">{{ @$withdrawal->user->username }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Payable Amount')
                            <span class="fw-bold">{{ showAmount($withdrawal->amount, 8, exceptZeros: true) }} {{ __(strtoupper($withdrawal->userCoinBalance->miner->coin_code)) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php echo $withdrawal->statusBadge @endphp
                        </li>

                        @if ($withdrawal->admin_feedback)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @lang('Admin Response')
                                <p>{{ $withdrawal->admin_feedback }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-md-6 mb-30">

            <div class="card b-radius--10 box--shadow1 overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('User Withdraw Information')</h5>


                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>@lang('Wallet Address'):</h6>
                            <p>{{ __($withdrawal->userCoinBalance->wallet) }}</p>
                        </div>
                    </div>

                    @if ($withdrawal->status == Status::PAYMENT_PENDING)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button class="btn btn-outline--success approveBtn ms-1" data-id="{{ $withdrawal->id }}" data-amount="{{ showAmount($withdrawal->final_amount, 8, exceptZeros: true) }} {{ $withdrawal->currency }}">
                                    <i class="fas la-check"></i> @lang('Approve')
                                </button>

                                <button class="btn btn-outline--danger rejectBtn ms-1" data-id="{{ $withdrawal->id }}">
                                    <i class="fas fa-ban"></i> @lang('Reject')
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>



    {{-- APPROVE MODAL --}}
    <div class="modal fade" id="approveModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Approve Withdrawal Confirmation')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.withdraw.approve') }}" method="POST">
                    @csrf
                    <input name="id" type="hidden">
                    <div class="modal-body">
                        <p>@lang('Have you sent') <span class="fw-bold withdraw-amount text-success"></span>?</p>
                        <p class="withdraw-detail"></p>
                        <textarea class="form-control pt-3" name="details" value="{{ old('details') }}" rows="3" placeholder="@lang('Provide the details. eg: transaction number')" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
    <div class="modal fade" id="rejectModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Withdrawal Confirmation')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.withdraw.reject') }}" method="POST">
                    @csrf
                    <input name="id" type="hidden">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Reason of Rejection')</label>
                            <textarea class="form-control pt-3" name="details" value="{{ old('details') }}" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.approveBtn').on('click', function() {
                var modal = $('#approveModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('.withdraw-amount').text($(this).data('amount'));
                modal.modal('show');
            });

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
