@extends('student.layout')
@section('section')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead class="text-capitalize">
                        <th>@lang('text.sn')</th>
                        <th>@lang('text.word_purpose')</th>
                        <th>@lang('text.word_amount')</th>
                        <th>@lang('text.word_year')</th>
                        <th>@lang('text.word_date')</th>
                        <th></th>
                    </thead>
                    <tbody>
                        @php
                            $sn = 1;
                        @endphp
                        @foreach ($payments as $payment)
                            @if($payment instanceof \App\Models\TranzakTransaction)
                                <tr>
                                    <td>{{ $sn++ }}</td>
                                    <td>{{ "Application Fee" }}</td>
                                    <td>{{ $payment->amount??'' }}@lang('text.currency_cfa')</td>
                                    <td>{{ $payment->form?->year?->name??'' }}</td>
                                    <td>{{ $payment->created_at?->format('l dS M Y')??'' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary rounded text-capitalize" onclick="printPaymentReceipt('#APPLICATION_{{ $payment->id }}')">@lang('text.print_receipt')</button>
                                        <div class="hidden" id="APPLICATION_{{ $payment->id }}">
                                            <div>
                                                <div style="margin-block: 5rem;">
                                                    <img src="{{ asset('assets/images/header2.png') }}" alt="" style="height: auto; width: 100%;">
                                                    <h5 class="text-center text-capitalize"><b>@lang('text.momo_payment_reciept')</b></h5>
                                                    <p class="text-center">Ref: <span class="text-uppercase">tranzak{{ $payment->transaction_id??'----' }}</span></p>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.word_purpose'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize">Application Fee For {{ $programs->where('id', $payment->form?->program??0)->first()?->name??'' }}</span>
                                                    </div>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.academic_year'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize"> {{ $payment->form?->year?->name??'' }}</span>
                                                    </div>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.word_date'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize"> {{ $payment->created_at?->format('l dS M Y')??'' }}</span>
                                                    </div>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.paid_through'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize"> MOMO(TRANZAK) - {{ $payment->transaction_id }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            @if($payment instanceof \App\Models\Charge)
                                <tr>
                                    <td>{{ $sn++ }}</td>
                                    <td>{{ "Platform Charge" }}</td>
                                    <td>{{ $payment->amount }} @lang('text.currency_cfa')</td>
                                    <td>{{ $payment->year?->name??'' }}</td>
                                    <td>{{ $payment->created_at?->format("l dS M Y") }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary rounded text-capitalize" onclick="printPaymentReceipt('#PLATFORM_{{ $payment->id }}')">@lang('text.print_receipt')</button>
                                        <div class="hidden" id="PLATFORM_{{ $payment->id }}">
                                            <div>
                                                <div style="margin-block: 5rem;">
                                                    <img src="{{ asset('assets/images/header2.png') }}" style="height: auto; width: 100%;">
                                                    <h5 class="text-center text-capitalize"><b>@lang('text.momo_payment_reciept')</b></h5>
                                                    <p class="text-center">Ref: <span class="text-uppercase">momo{{ $payment->financialTransactionId??'----' }}</span></p>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.word_purpose'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize">Platform Charges</span>
                                                    </div>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.academic_year'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize"> {{ $payment->year?->name??'' }}</span>
                                                    </div>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.word_date'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize"> {{ $payment->created_at?->format('l dS M Y')??'' }}</span>
                                                    </div>
                                                    <div style="display: flex;">
                                                        <span style="width: 25%;" class="text-capitalize">@lang('text.paid_through'):</span>
                                                        <span style="width: 75%; border-bottom: 1px solid lightgray;" class="text-capitalize"> MOMO - {{ $payment->financialTransactionId }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let printPaymentReceipt = (selector)=>{
            let receipt_frame = document.querySelector(selector).innerHTML;
            let doc = document.body.innerHTML;

            document.body.innerHTML = receipt_frame;
            window.print();
            document.body.innerHTML = doc;
        }
    </script>
@endsection