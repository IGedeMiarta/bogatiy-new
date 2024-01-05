@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light table">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Interval')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schedules as $schedule)
                                    <tr>
                                        <td>{{ __($schedule->name) }}</td>
                                        <td>{{ __($schedule->interval) }} @lang('Seconds')</td>
                                        <td> @php echo $schedule->statusBadge; @endphp </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary updateSchedule" data-id="{{ $schedule->id }}" data-name="{{ $schedule->name }}" data-interval="{{ $schedule->interval }}" type="button"><i
                                                   class="las la-pen"></i>
                                                @lang('Edit')</button>

                                            @if (!$schedule->status)
                                                <button class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.cron.schedule.status', $schedule->id) }}" data-question="@lang('Are you sure to enable this schedule?')" type="button">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.cron.schedule.status', $schedule->id) }}" data-question="@lang('Are you sure to disable this schedule?')" type="button">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
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
                @if ($schedules->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($schedules) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>


    <x-confirmation-modal />

    <div class="modal fade" id="addSchedule" role="dialog" aria-hidden="true" tabindex="-1" a>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('Add Cron Schedule')</h4>
                    <button class="close" data-bs-dismiss="modal" type="button"><i class="las la-times"></i></button>
                </div>
                <form class="form-horizontal resetForm" method="post" action="{{ route('admin.cron.schedule.store') }}">
                    @csrf
                    <input name="id" type="hidden">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <div class="col-sm-12">
                                <input class="form-control" name="name" type="text" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Interval')</label>
                            <div class="col-sm-12">
                                <div class="input-group">
                                    <input class="form-control" name="interval" type="number" required>
                                    <span class="input-group-text">@lang('Seconds')</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary h-45 w-100" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('breadcrumb-plugins')
    <button class="btn btn-sm btn-outline--primary addSchedule"><i class="las la-plus"></i> @lang('Add New')</button>
    <x-back route="{{ route('admin.cron.index') }}" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.updateSchedule').on('click', function() {
                let title = "@lang('Update Schedule')";
                var modal = $('#addSchedule');
                let id = $(this).data('id');
                let name = $(this).data('name');
                let interval = $(this).data('interval');
                modal.find('input[name=id]').val(id);
                modal.find('input[name=name]').val(name);
                modal.find('input[name=interval]').val(interval);
                modal.find('.modal-title').text(title)
                modal.modal('show');
            });

            $('.addSchedule').on('click', function() {
                let title = "@lang('Add Schedule')";
                let modal = $('#addSchedule');
                $('.resetForm').trigger('reset');
                modal.find('input[name=id]').val('');
                modal.find('.modal-title').text(title)
                modal.modal('show');
            })
        })(jQuery);
    </script>
@endpush
