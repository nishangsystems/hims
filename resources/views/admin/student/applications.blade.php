@extends('admin.layout')
@section('section')
    <div class="py-3">
        <div class="py-2">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-light table-stripped" id="hidden-table-info">
                <thead>
                    <tr class="text-capitalize border-bottom border-dark">
                        <th class="border-left border-right">#</th>
                        <th class="border-left border-right">{{__('text.word_name')}}</th>
                        <th class="border-left border-right">{{__('text.word_email')}}</th>
                        <th class="border-left border-right">{{__('text.word_phone')}}</th> 
                        <th class="border-left border-right">{{__('text.word_degree')}}</th> 
                        <th class="border-left border-right">{{__('text.word_programs')}}</th> 
                        <th class="border-left border-right"></th>
                    </tr>
                </thead>
                <tbody id="table_body">
                    @php($k = 1)
                    @foreach ($applications as $appl)
                        <tr class="border-bottom">
                            <td class="border-left border-right">{{ $k++ }}</td>
                            <td class="border-left border-right">{{ $appl?->name??null == null ? $appl?->student?->name??'' : $appl?->name??'' }}</td>
                            <td class="border-left border-right">{{ $appl?->email??null == null ? $appl?->student?->email??'' : $appl?->email??'' }}</td>
                            <td class="border-left border-right">{{ $appl?->phone??null == null ? $appl?->student?->phone??'' : $appl?->phone??'' }}</td>
                            <td class="border-left border-right">{{ $degrees?->where('id', $appl?->degree_id??'')->first()?->deg_name??null }}</td>
                            <td class="border-left border-right">{{ $programs?->where('id', $appl?->program??'')->first()->name??'' }}</td>
                            <td class="border-left border-right">
                                @if(isset($action))
                                    <a href="{{ Request::url().'/'.$appl?->id??'' }}" class="btn mt-1 btn-xs btn-primary">{{ $action }}</a>|
                                    <a href="{{ route('admin.admission.show', $appl?->id??'') }}" class="btn mt-1 btn-xs btn-success">@lang('text.word_show')</a>
                                @endif
                                @if(isset($bypass))
                                    |<a href="{{ route('admin.applications.bypass', $appl?->id??'') }}" class="btn mt-1 btn-xs btn-primary">{{ $bypass }}</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-end">

            </div>
        </div>
    </div>
@endsection