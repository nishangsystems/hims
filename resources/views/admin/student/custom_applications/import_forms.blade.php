@extends('admin.layout')
@section('section')
    <div class="container-fluid">
        <div style="display: flex; justify-content: end; margin: 2rem 1rem;">
            <div style="width: auto;">
                <table>
                    <thead class="alert-primary">
                        <tr><th colspan="8" style="border-bottom: 1px solid seashell;" class="text-capitalize text-center">@lang('text.file_format_csv')</th></tr>
                        <tr>
                            <th>Name *</th>
                            <th>Sex *</th>
                            <th>Phone *</th>
                            <th>Whatsapp *</th>
                            <th>Email *</th>
                            <th>Date of birth *</th>
                            <th>Place of birth *</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 4; $i++)
                            <tr>
                                <td style="border: 1px solid skyblue;">Madingo Firman</td>
                                <td style="border: 1px solid skyblue;">Female</td>
                                <td style="border: 1px solid skyblue;">679746598</td>
                                <td style="border: 1px solid skyblue;">674746708</td>
                                <td style="border: 1px solid skyblue;">curukianki23@gmail.com</td>
                                <td style="border: 1px solid skyblue;">22/12/1934</td>
                                <td style="border: 1px solid skyblue;">Mount Frikna Hospital</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
        <form method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 col-xl-4" style="margin-block: 0.7rem;">
                    <select name="degree_id" id="" class="form-control" required>
                        <option value="">@lang('text.word_degree')</option>
                        @foreach ($degrees as $degree)
                            <option value="{{ $degree->id }}">{{ $degree->deg_name }}</option>
                        @endforeach
                    </select>
                    <span class="text-info text-capitalize">@lang('text.word_degree') <span class="text-danger">*</span> </span>
                </div>
                <div class="col-md-6 col-xl-4" style="margin-block: 0.7rem;">
                    <select name="campus_id" id="" class="form-control" required>
                        <option value="">@lang('text.select_campus')</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-info text-capitalize">@lang('text.word_campus') <span class="text-danger">*</span> </span>
                </div>
                <div class="col-md-6 col-xl-4" style="margin-block: 0.7rem;">
                    <select name="program_id" id="" class="form-control" required>
                        <option value="">@lang('text.select_program')</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-info text-capitalize">@lang('text.word_program') <span class="text-danger">*</span> </span>
                </div>
                <div class="col-md-6 col-xl-4" style="margin-block: 0.7rem;">
                    <select name="level" id="" class="form-control" required>
                        <option value="">@lang('text.select_program')</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->level }}">{{ $level->level }}</option>
                        @endforeach
                    </select>
                    <span class="text-info text-capitalize">@lang('text.word_level') <span class="text-danger">*</span> </span>
                </div>
                <div class="col-md-6 col-xl-4" style="margin-block: 0.7rem;">
                    <input type="file" name="file" required class="form-control">
                    <span class="text-info text-capitalize">@lang('text.word_file') <span class="text-danger text-capitalize">*</span></span>
                </div>
                <div class="col-md-12 col-xl-4" style="margin-block: 0.7rem;">
                    <input type="submit" value="{{ __('text.word_import') }}" class="form-control btn btn-primary btn-md rounded">
                </div>
            </div>
        </form>
    </div>
@endsection