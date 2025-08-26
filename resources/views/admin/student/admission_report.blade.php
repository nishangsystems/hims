@extends('admin.layout')
@section('section')
    <div class="container-fluid">
        <div class="d-flex justify-content-end py-2">
            <a href="{{ request()->url() }}?action=excel" class="btn btn-sm btn-primary rounded text-capitalize">@lang('text.word_download')</a>
        </div>
        <table class="table">
            <thead class="text-capitalize">
                <th>@lang('text.sn')</th>
                <th>@lang('text.word_name')</th>
                <th>@lang('text.word_gender')</th>
                <th>@lang('text.registration_number')</th>
                <th>@lang('text.word_department')</th>
                <th>@lang('text.place_of_birth')</th>
                <th>@lang('text.word_nationality')</th>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                @endphp
                @foreach($applications as $appl)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $appl->name }}</td>
                        <td>{{ $appl->gender }}</td>
                        <td>{{ $appl->matric }}</td>
                        <td>{{ $appl->department??'' }}</td>
                        <td>{{ $appl->dob?->format('d-m-Y') }} <br> {{ $appl->pob }} </td>
                        <td>{{ $appl->nationality }} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection