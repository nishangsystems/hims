@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <table class="table">
            <thead class="text-capitalize">
                <th>@lang('text.sn')</th>
                <th>@lang('text.word_name')</th>
                <th>@lang('text.word_matricule')</th>
                <th>@lang('text.word_phone')</th>
                <th>@lang('text.word_program')</th>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                @endphp
                @foreach ($admissions as $admission)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $admission->name }}</td>
                        <td>{{ $admission->matric }}</td>
                        <td>{{ $admission->phone }}</td>
                        <td>{{ $admission->program_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection