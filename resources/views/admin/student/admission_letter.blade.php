@extends('admin.printable2')
@section('section')
    <div class="py-2" style="line-height: 2.3rem; font-size:larger; border-block: 2px solid gray; font-family: 'Times New Roman', Times, serif;">
        
        <div class="my-2">Our Ref: .....................<span class="text-uppercase">PRE/REC/REG/BU/{{ now()->format('m/Y') }}</span></div>
        <div class="my-2">Your Ref: .....................<span class="text-uppercase"></span></div>
        <div class="my-4 text-uppercase"><b><h4>{{ $name }}<br>{{ $matric }}</h4></b></div>
        <div class="my-4"><h4>Dear <b class="text-uppercase">{{ $first_name??explode($name, ' ')[0] }}</b>,</h4></div>
        <div class="my-4 text-center font-semibold"><h4>OFFER OF ADMISSION INTO THE 16<sup>TH</sup> BATCH OF THE HND PROGRAM FOR THE ACADEMIC YEAR {{ $batch }}</h4></div>
        <p class="py-2 text-justify">We are delighted to inform you that the Higher Institute of Management Studies, Buea has admitted you for the <b>{{ $batch }} Batch</b> of its <b>One Year Full- time {{ $degree }}</b> Program in {{ $program }}, which will commence on the {{ $start_of_lectures }}. Effective lectures will begin on that same date at 7:30Am.This is the beginning of an important Life-Changing Journey. You are therefore strongly advised to attend our orientation sessions when the dates are fixed so that you would be enlightened on important issues concerning the Institution, your chosen course of study and your career.</p>
        <p class="py-2 text-justify">You will be issued a copy of ‘‘HIMS Student Handbook’’ and a ‘‘Fee Payment Schedule’’. Take time to read through these documents as they will help you understand how we function.  </p>
        <p class="py-2 text-capitalize text-justify">Once again congratulations on your Admission. We look forward to welcoming you into the ‘‘TOP MOST BUSINESS SCHOOL’’ in the Central Africa Region. <br>Sincerely</p>
        <span class="d-block  my-4 py-2" style="margin-top: 6rem !important;"> Registrar</span>
    </div>
@endsection