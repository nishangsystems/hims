@extends('student.layout')
@section('section')
    <div class="py-3">
        <table class="table">
            <thead><tr class="text-capitalize">
                <th>{{ __('text.sn') }}</th>
                <th>{{ __('text.academic_year') }}</th>
                <th>{{ __('text.word_program') }}</th>
                <th></th>
            </tr></thead>
            <tbody>
                @php($k = 1)
                @foreach ($applications as $appl)
                    <tr class="border-bottom">
                        <td class="border-left border-right">{{ $k++ }}</td>
                        <td class="border-left border-right">{{ $appl->year->name }}</td>
                        <td class="border-left border-right">{{ $programs->where('id', $appl->program)->first()?->name??'' }}</td>
                        <td class="border-left border-right d-flex flex-wrap">
                            <a class="btn btn-xs btn-primary mx-2" href="{{ route('student.application.start', [0, $appl->id]) }}">{{ __('text.word_fill') }}</a>
                            <a class="btn btn-xs btn-success mx-2" href="{{ route('student.application.start', [5, $appl->id]) }}">{{ __('text.word_preview') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection