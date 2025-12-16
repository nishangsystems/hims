@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="container-fluid">
            <input type="search" name="" class="form-control" id="" oninput="searchStudent(this)" placeholder="search by name or phone number">
        </div>
        <table class="table">
            <thead class="text-capitalize">
                <th>@lang('text.sn')</th>
                <th>@lang('text.word_name')</th>
                <th>@lang('text.word_matricule')</th>
                <th>@lang('text.word_phone')</th>
                <th>@lang('text.word_program')</th>
            </thead>
            <tbody id="admissions_list">
            </tbody>
        </table>
    </div>
@endsection
@section('script')
    <script>
        let searchStudent = (element)=>{
            let search = $(element).val();
            let route = "{{ route('student_admissions') }}";
            $.ajax({
                method: 'GET', url: route, data: {"search" : search},
                success: (response)=>{
                    console.log(response);
                    let dom_node = '';
                    let counter = 1;
                    response.forEach(item => {
                        dom_node += `<tr>
                            <td>${counter++}</td>
                            <td>${item.name}</td>
                            <td>${item.matric}</td>
                            <td>${item.phone}</td>
                            <td>${item.program_name}</td>
                        </tr>`;
                    })

                    $('#admissions_list').html(dom_node);
                    
                }
            })
        }
    </script>
@endsection