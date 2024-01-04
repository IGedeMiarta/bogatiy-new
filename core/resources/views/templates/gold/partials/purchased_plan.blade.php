 <style>
     @import url('https://fonts.googleapis.com/css2?family=Orbitron&display=swap');

     *,
     *:before,
     *:after {
         box-sizing: border-box;
     }

     .range {
         position: relative;
         background-color: #333;
         width: 300px;
         height: 30px;
         transform: skew(30deg);
         font-family: 'Orbitron', monospace;

         &:before {
             --width: calc(var(--p) * 1%);

             content: '';
             position: absolute;
             top: 0;
             left: 0;
             width: 0;
             height: 100%;
             background-color: #F3E600;
             z-index: 0;
             animation: load .5s forwards linear, glitch 2s infinite linear;
         }

         &:after {
             counter-reset: progress var(--p);
             content: counter(progress) '%';
             color: #000;
             position: absolute;
             left: 5%;
             top: 50%;
             transform: translateY(-50%) skewX(-30deg);
             z-index: 1;
         }

         &__label {
             transform: skew(-30deg) translateY(-100%);
             line-height: 1.5;
         }
     }

     @keyframes load {
         to {
             width: var(--width);
         }
     }

     @keyframes glitch {

         0%,
         5% {
             transform: translate(0, 0);
         }

         1% {
             transform: translate(-5%, -10%);
         }

         2% {
             transform: translate(10%, 30%);
         }

         3% {
             transform: translate(-43%, 10%);
         }

         4% {
             transform: translate(2%, -23%);
         }
     }
 </style>
 <table class="table table--responsive--md">
     <thead>
         <tr>
             <th>@lang('Plan')</th>
             <th>@lang('Price')</th>
             <th>@lang('Return /Day')</th>
             <th>@lang('Total Days')</th>
             <th>@lang('Remaining Days')</th>
             @if (!request()->routeIs('user.plans.active'))
                 <th> @lang('Status')</th>
             @endif
             <th> @lang('Action')</th>
         </tr>
     </thead>
     <tbody>
         @forelse($orders as $data)
             <tr>
                 <td>{{ $data->plan_details->title }}</td>
                 <td>
                     <small><strong>{{ showAmount($data->amount) }} {{ __($general->cur_text) }}</strong></small>
                 </td>
                 <td>
                     @if ($data->min_return_per_day == $data->max_return_per_day)
                         {{ showAmount($data->min_return_per_day, 8, exceptZeros: true) }}
                     @else
                         {{ showAmount($data->min_return_per_day, 8, exceptZeros: true) . ' - ' . showAmount($data->max_return_per_day, 8, exceptZeros: true) }}
                     @endif
                     {{ strtoupper($data->miner->coin_code) }}
                 </td>

                 <td>{{ $data->period }}</td>
                 <td>
                     {{ $data->period_remain }}
                 </td>
                 @if (!request()->routeIs('user.plans.active'))
                     <td>
                         @php
                             echo $data->statusBadge;
                         @endphp
                     </td>
                 @endif
                 <td>
                     <button class="btn btn--base btn--sm viewBtn"
                         data-date="{{ __(showDateTime($data->created_at, 'd M, Y')) }}" data-trx="{{ $data->trx }}"
                         data-plan="{{ $data->plan_details->title }}" data-miner="{{ $data->plan_details->miner }}"
                         data-speed="{{ $data->plan_details->speed }}"
                         data-price="{{ showAmount($data->amount) }} {{ __($general->cur_text) }}"
                         data-last_paid="{{ $data->last_paid }}"
                         data-next_paid="{{ getNextPaid($data->last_paid, 'next') }}"
                         data-percent="{{ getNextPaid($data->last_paid, 'percent') }}"
                         data-rpd="@if ($data->min_return_per_day == $data->max_return_per_day) {{ showAmount($data->min_return_per_day, 8, exceptZeros: true) }} @else {{ showAmount($data->min_return_per_day, 8, exceptZeros: true) . ' - ' . showAmount($data->max_return_per_day, 8, exceptZeros: true) }} @endif {{ strtoupper($data->miner->coin_code) }}"
                         data-period={{ $data->period }} data-period_r={{ $data->period_remain }}
                         data-status="{{ $data->status }}"
                         @if ($data->status == 0) data-order_id="{{ encrypt($data->id) }}" @endif><i
                             class="las la-desktop"></i>
                     </button>
                 </td>
             </tr>
         @empty
             <tr>
                 <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
             </tr>
         @endforelse
     </tbody>
 </table>

 @if ($paginate)
     {{ paginateLinks($orders) }}
 @endif
 <div class="modal custom--modal fade" id="viewModal" role="dialog">
     <div class="modal-dialog" role="document">
         <div class="modal-content rounded-0">
             <div class="modal-header rounded-0">
                 <h4 class="modal-title text-white">@lang('Track Details')</h4>
                 <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <div class="modal-body">
                 <ul class="list-group">
                     <li class="list-group-item d-flex justify-content-center" style="border-bottom: 0 none;">
                         <span class="font-weight-bold">@lang('Payout in')</span>
                         <span class=" ml-3">&nbsp;&nbsp;&nbsp;</span>
                         <span class="p-payIn ml-3">00:00:00</span>
                     </li>
                     <li class="list-group-item d-flex justify-content-center">

                         <div class="range" style="--p:0">
                             <!--<div class="range__label">Progress</div>-->
                         </div>
                     </li>
                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Created At')</span>
                         <span class="p-date"></span>
                     </li>

                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Plan Title')</span>
                         <span class="plan-title"></span>
                     </li>
                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Plan Price')</span>
                         <span class="plan-price"></span>
                     </li>

                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Miner')</span>
                         <span class="miner-name"></span>
                     </li>

                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Speed')</span>
                         <span class="speed"></span>
                     </li>

                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Return /Day')</span>
                         <span class="plan-rpd"></span>
                     </li>

                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Total Days')</span>
                         <span class="plan-period"></span>
                     </li>

                     <li class="list-group-item d-flex justify-content-between">
                         <span class="font-weight-bold">@lang('Remaining Days')</span>
                         <span class="plan-period-r"></span>
                     </li>
                 </ul>
             </div>
             <div class="modal-footer">
                 <a class="btn btn--base w-100" href="">@lang('Pay Now')</a>
             </div>
         </div>
     </div>
 </div>

 @push('script')
     <script>
         'use strict';
         (function($) {

             $('.viewBtn').on('click', function() {
                 var modal = $('#viewModal');

                 let data = $(this).data();
                 let percent = data.percent;
                 let style = '--p:' + percent;
                 $('.range').attr('style', style);
                 const nextPaid = data.next_paid;

                 var intervalId = setInterval(function() {
                     payOut(nextPaid);
                 }, 1000); // 1000 milliseconds = 1 second

                 // Update modal content
                 modal.find('.p-date').text(data.date);
                 modal.find('.plan-title').text(data.plan);
                 modal.find('.plan-price').text(data.price);
                 modal.find('.miner-name').text(data.miner);
                 modal.find('.speed').text(data.speed);
                 modal.find('.plan-rpd').text(data.rpd);
                 modal.find('.plan-period').text(data.period);
                 modal.find('.plan-period-r').text(data.period_r);

                 if (data.status == 0) {
                     modal.find('.modal-footer').show();
                     modal.find('.modal-footer a').attr('href',
                         `{{ route('user.payment', '') }}/${data.order_id}`);
                 } else {
                     modal.find('.modal-footer').hide();
                 }

                 // Attach event listener to modal close event
                 modal.on('hidden.bs.modal', function() {
                     // Clear the interval when the modal is closed
                     $('.p-payIn').html('00:00:00');
                     clearInterval(intervalId);
                 });

                 // Show the modal
                 modal.modal('show');
             });


             function payOut(date) {
                 // Parse the input date string
                 let nextPayDate = new Date(date);

                 // Get the current date and time
                 var currentDate = new Date();


                 // If the current time is past the payout time for today, set the payout time for tomorrow
                 if (currentDate > nextPayDate) {
                     nextPayDate.setDate(nextPayDate.getDate() + 1);
                 }

                 // Calculate the remaining time until the payout time
                 var timeUntilPayout = nextPayDate - currentDate;
                 var hours = Math.floor(timeUntilPayout / (60 * 60 * 1000));
                 var minutes = Math.floor((timeUntilPayout % (60 * 60 * 1000)) / (60 * 1000));
                 var seconds = Math.floor((timeUntilPayout % (60 * 1000)) / 1000);

                 // Format the output date
                 var outputDate = currentDate.getFullYear() + '/' +
                     (currentDate.getMonth() + 1).toString().padStart(2, '0') + '/' +
                     currentDate.getDate().toString().padStart(2, '0') + ', ' +
                     currentDate.getHours().toString().padStart(2, '0') + ':' +
                     currentDate.getMinutes().toString().padStart(2, '0') + ':' +
                     currentDate.getSeconds().toString().padStart(2, '0');

                 // Format the remaining time until payout
                 var remainingTime = hours.toString().padStart(2, '0') + ':' +
                     minutes.toString().padStart(2, '0') + ':' +
                     seconds.toString().padStart(2, '0');

                 $('.p-payIn').html(remainingTime);

             }

         })(jQuery)
     </script>
 @endpush
