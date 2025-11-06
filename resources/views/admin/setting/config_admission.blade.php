@extends('admin.layout')
@section('section')
    <div class="py-3">
        <form enctype="multipart/form-data" method="post">
            @csrf
            <div class="row py-5">
                <div class="col-sm-12 col-md-4 col-lg-3 py-2 text-uppercase">
                    <span class="form-control text-danger" style="font-weight:800;">{{ \App\Models\Batch::find(\App\Helpers\Helpers::instance()->getCurrentAccademicYear())->name }}</span>
                    <label class="text-secondary text-capitalize">{{ __('text.word_year') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="start_date" required type="datetime-local" value="{{ $current_session != null ? $current_session->start_date : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.start_date') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="end_date" required type="datetime-local" value="{{ $current_session != null ? $current_session->end_date : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.end_date') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="start_of_lectures" required type="date" value="{{ $current_session != null ? $current_session->start_of_lectures?->format('Y-m-d') : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.start_of_lectures') }}</label>
                </div>
                {{-- <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="fee1_latest_date" required type="datetime-local" value="{{ $current_session != null ? $current_session->fee1_latest_date : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.first_instalment_latest_date') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="fee2_latest_date" required type="datetime-local" value="{{ $current_session != null ? $current_session->fee2_latest_date : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.finish_fee_latest_date') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="director" required type="text" value="{{ $current_session != null ? $current_session->director : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.word_director') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="dean" required type="text" value="{{ $current_session != null ? $current_session->dean : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.word_dean') }}</label>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 py-2">
                    <input class="form-control" name="help_email" required type="email" value="{{ $current_session != null ? $current_session->help_email : null }}">
                    <label class="text-secondary text-capitalize">{{ __('text.help_email') }}</label>
                </div> --}}
                <div class="col-sm-12 col-md-12 col-lg-12 py-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary text-uppercase">{{ __('text.word_update') }}</button>
                </div>
            </div>
        </form>
        <div class="py-3">
            <table class="table bg-light">
                <thead class="text-uppercase bg-light">
                    <th class="border-left border-right">{{ __('text.sn') }}</th>
                    <th class="border-left border-right">{{ __('text.word_year') }}</th>
                    <th class="border-left border-right">{{ __('text.start_date') }}</th>
                    <th class="border-left border-right">{{ __('text.end_date') }}</th>
                </thead>
                <tbody>
                    @php($k = 1)
                    @foreach ($sessions as $ssn)
                        <tr class="border-bottom">
                            <td class="border-left border-right">{{ $k++ }}</td>
                            <td class="border-left border-right">{{ $ssn->batch->name }}</td>
                            <td class="border-left border-right">{{ $ssn->start_date }}</td>
                            <td class="border-left border-right">{{ $ssn->end_date }}</td>
                        </tr>
                        
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection