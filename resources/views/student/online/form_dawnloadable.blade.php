@extends('student.printable')
@section('section')
    <div class="py-3">
        <div class="bg-white px-3 py-1">
            <div style="border-radius: 2.3rem;" class="w-75 mx-auto my-3 border border-2 border-dark py-4 px-3 text-center">Admission Number:_______________________________________</div>
            <div style="width: 90%; border-radius: 8rem 8rem 0 0;" class="mx-auto py-5 px-3">
                @if(in_array($application->degree_id, [2, 3, 6]))
                    <div class="w-75 mx-auto my-3 py-3 px-3 shadow text-center" style="border-radius: 7rem 7rem 0 0; font-size: 2rem; font-weight: 700;">
                        <span class="text-uppercase">in affiliation with the university of bamenda</span> (UBa)
                    </div>
                @endif
                <div class="text-center py-3">
                    <span class="d-block" style="font-size: 1.4rem">PO BOX 462 BUEA</span>
                    <span class="d-block" style="font-size: 1.2rem">South West Region, Republic of Cameroon</span>
                    <span class="d-block" style="font-size: 1rem">Tel +237 679 821 672 / 677 962 333</span>
                    <span class="d-block mt-3 text-primary" style="font-size: 1rem">E-mail: info@himsbuea.org/registrar@himsbuea.org</span>
                    <span class="d-block text-primary" style="font-size: 1rem">Website: www.himsbuea.org</span>
                </div>
                <div class="py-4 my-3 text-center shadow">
                    <h4 class="text-uppercase text-dark">undergraduate admission form</h4><br><span class="text-primary">IN</span>
                    <label class="d-block py-2">Programme: <b>{{ $program->name }}</b></label>
                    <label class="d-block py-2">Level: <span style="border-bottom: 1px solid gray; padding-inline: 2rem;">{{ $application->level??'' }}</span> </label>
                </div>
                    <label class="d-block py-4 text-center">Academic Year<br> <b style="text-decoration: underline; padding-inline: 2rem;">{{ $application->year->name }}</b></label>
            </div>
            <div class="mx-auto py-2 justify-content-between text-primary" style=" font-style: italic; width: 80%; margin-inline: auto; display: flex; justify-content: between">
                <span style="width: 30%; display: inline-block;" class="shadow px-3 py-3 text-center">
                    Name of admission officer: <br> _____________________ <br> Signature: <br> _____________________ <br> Date: <br> _____________________
                </span>
                <span style="width: 25%; display: inline-block;" class="shadow px-3 py-3 text-center">
                    Affix a <br> passport size <br> photo here
                </span>
                <span style="width: 30%; display: inline-block;" class="shadow px-3 py-3 text-center">
                    <span class="text-dark">ADMISSION DECISSION</span><br>ADMITTED: <input type="checkbox" style="width:2rem; height: 2rem;"> <br> NOT ADMITTED: <input type="checkbox" style="width:2rem; height: 2rem;"> <br> OBSERVATION <br> _____________________
                </span>
            </div>
        </div>
    </div>
    <div class="py-2 page-break">
        <div class="bg-white px-3 py-1">
            <h4 class="text-uppercase py-1" style="font-weight: 700;">admission requirements</h4>
            <h4 class="text-capitalize" style="font-weight: 700;">Ensure to attach the following documents:</h4>
            <h5 class="text-capitalize" style="font-weight: 700;">for HND</h5>
            <ul style="list-style-type: circle; padding-left: 1rem;">
                <li class="text-capitalize">2 photocopies of GCE ordinary level slip/certificate or probatoire</li>
                <li class="text-capitalize">2 photocopies of GCE advanced level slip/certificate or Baccalaureat</li>
                <li class="text-capitalize">2 photocopies of birth certificate</li>
                <li class="text-capitalize">2 photocopies of valid national ID card</li>
                <li class="text-capitalize">2 passport sized photographs</li>
            </ul>
            <h5 class="text-capitalize" style="font-weight: 700;">for B.TECH/BBA</h5>
            <ul style="list-style-type: circle; padding-left: 1rem;">
                <li class="text-capitalize">Certified copy of HND result slip or certificate (By the ministry of higher education)</li>
                <li class="text-capitalize">photocopy of advanced level certificate or equivalent</li>
                <li class="text-capitalize">photocopy of ordinary level certificate or equivalent</li>
                <li class="text-capitalize">certified copy of birth certificate</li>
                <li class="text-capitalize">medical certificate of fitness from a government hospital</li>
                <li class="text-capitalize">4 coloured passport sized photographs</li>
                <li class="text-capitalize">Signed year 1&2 or HND school transcript (from your institution)</li>
                <li class="text-capitalize">photocopy of valid national ID card</li>
            </ul>
        </div> 
    </div>
    <h5 style="font-weight: 700; font-style: italic;">NB: upon admission, candidate(s) may be required to present originals for the purpose of authentification.</h5>
    <div class="py-2 mt-4">
        <div class="bg-white px-3 py-1 d-flex justify-content-between">
            <h4 class="text-uppercase py-1 w-100" style="font-weight: 700;">personal details</h4>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 94%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">name (as on birth certificate)<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->name }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 29%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">date of birth<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->dob }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 28%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">place of birth<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->pob }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 28%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">sex<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->gender }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 29%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">ID card number<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->id_card_number }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 28%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">date of issue<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->id_date_of_issue }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 28%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">place of issue<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->id_place_of_issue }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 29%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">Nationality<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->nationality }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 28%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">region of origin<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->_region->region }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 28%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">country of birth<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->country_of_birth }}<span></div>
        </div> 
    </div>
    <div class="py-2">
        <div class="bg-white px-3 py-1 d-flex flex-wrap justify-content-between">
            <h4 class="text-uppercase py-1 w-100" style="font-weight: 700;">address details</h4>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 45%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">Residential address<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->residence }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 42%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">personal phone number<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->phone }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 42%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">home/business numbers<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->extra_phone }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 45%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">email address<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->emai??'' }}<span></div>
            <h5 style="font-weight: 700; padding-block: 0.5rem; width:100%;">Parent's or Guardian's complete address</h5>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 45%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">name of parent/guardian<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->guardian }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 18%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">contact<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->guardian_contact }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 22%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">address<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->guardian_address }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 45%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">name of sponsor<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->sponsor }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 18%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">contact<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->sponsor_contact }}<span></div>
            <div class="text-capitalize py-1 mx-1 my-2 px-3 rounded border" style="width: 22%; display: inline-block;"><span class="text-secondary" style="font-weight: 700;">address<span> : <span class="text-dark" style="font-weight: 700;">{{ $application->sponsor_address }}<span></div>
        </div> 
    </div>
    <div class="py-2 page-break">
        <div class="bg-white px-3 py-1">
            <h4 class="text-uppercase py-1 w-100" style="font-weight: 700;">qualifications / academic records</h4>
            <div class="bg-white px-3 py-1 d-flex justify-content-between">
                <div class="text-capitalize my-2 p-0 rounded border w-100" style="width: 100%; display: inline-block;">
                    <table >
                        <thead class="text-dark border border-2 border-dark py-3" style="font-weight: 700; font-size: 1.6rem;">
                            <tr>
                                <th class="border border-2 border-dark text-center text-uppercase" colspan="5">GCE O/L or equivalent</th>
                            </tr>
                            <tr>
                                <th class="border-left border-right border-2 border-dark">Subject attempted</th>
                                <th class="border-left border-right border-2 border-dark">Grade</th>
                                <th class="border-left border-right w-25 text-center" colspan="3">School where the qualification was earned</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 1.3rem">
                            @php $k = 0; @endphp
                            @foreach (json_decode($application->gce_ol_record) as $rec)
                                @php $k++; @endphp
                                <tr class="border border-2">
                                    <td class="border border-2 border-dark">{{ $rec->subject }}</td>
                                    <td class="border border-2 border-dark">{{ $rec->grade }}</td>
                                    @switch($k)
                                        @case(1)
                                            <td class="border text-center" colspan="3">{{ $application->secondary_school }}</td>
                                            @break
                                        @case(2)
                                            <th class="border-left border-right">Exam Center</th>
                                            <th class="border-left border-right">Candidate No</th>
                                            <th class="border-left border-right">Year</th>
                                            @break
                                        @case(3)
                                            <td class="border">{{ $application->secondary_exam_center }}</td>
                                            <td class="border">{{ $application->secondary_candidate_number }}</td>
                                            <td class="border">{{ $application->secondary_exam_year }}</td>
                                            @break
                                        @default
                                            <td class="border"></td>
                                            <td class="border"></td>
                                            <td class="border"></td>
                                            @break;
                                    @endswitch
                                </tr>
                            @endforeach

                            @if(json_decode($application->gce_al_record) != null)
                                <tr>
                                    <th class="border border-2 border-dark text-center text-uppercase" colspan="5">GCE A/L or equivalent</th>
                                </tr>
                                <tr>
                                    <th class="border-left border-right border-2 border-dark">Subject attempted</th>
                                    <th class="border-left border-right border-2 border-dark">Grade</th>
                                    <th class="border-left border-right w-25 text-center" colspan="3">School where the qualification was earned</th>
                                </tr>
                                @php $k = 0; @endphp
                                @foreach (json_decode($application->gce_al_record) as $rec)
                                    @php $k++; @endphp
                                    <tr class="border border-2">
                                        <td class="border border-2 border-dark">{{ $rec->subject }}</td>
                                        <td class="border border-2 border-dark">{{ $rec->grade }}</td>
                                        @switch($k)
                                            @case(1)
                                                <td class="border text-center" colspan="3">{{ $application->high_school }}</td>
                                                @break
                                            @case(2)
                                                <th class="border-left border-right">Exam Center</th>
                                                <th class="border-left border-right">Candidate No</th>
                                                <th class="border-left border-right">Year</th>
                                                @break
                                            @case(3)
                                                <td class="border">{{ $application->high_school_exam_center }}</td>
                                                <td class="border">{{ $application->high_school_candidate_number }}</td>
                                                <td class="border">{{ $application->high_school_exam_year }}</td>
                                                @break
                                            @default
                                                <td class="border"></td>
                                                <td class="border"></td>
                                                <td class="border"></td>
                                                @break;
                                        @endswitch
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- <div class="bg-white px-3 py-1 d-flex justify-content-between">
                <div class="text-capitalize my-2 p-0 rounded border w-100" style="width: 100%; display: inline-block;">
                    <table >
                        <thead class="text-dark border border-2 border-dark py-3" style="font-weight: 700; font-size: 1.6rem;">
                        </thead>
                        <tbody style="font-size: 1.3rem">
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </div> 
    </div>
    <div class="py-2">
        <div class="bg-white px-3 py-1">
            <h4 class="text-uppercase py-1" style="font-weight: 700;">Declaration and signature</h4>
            <div class=" py-2">
                I <span style="font-weight : 700;">{{ $application->name }}</span>, certify that the information given in this application, to the best of my knowledge, is complete and accurate.
                I further understand that falsification or failure to supply correct information may lead to disqualification of my application or my admission to the programme.
                I confirm that I have adequate resources to meet the financial obligations throughout my studies.<br>
                <span style="font-weight : 700; display: block; padding-top: 2.5rem;">Signature: _________________________ Date: __________________________ </span>
            </div>
        </div> 
    </div>
    <div class="py-2">
        <div class="bg-white px-3 py-1">
            <h4 class="text-uppercase py-1" style="font-weight: 700;">Source of information</h4>
            <h5>Where did you learn of admission into HIMS Buea.</h5>
            <label class="border fs-4 text-center w-100 " style="font-weight : 600; padding: 0.5rem 1rem;">{{ $application->referer }}</label>
            {{-- <table>
                <thead>
                    <th class="border fs-4" style="font-weight : 600; padding: 0.5rem 1rem;"><input type="checkbox" style="height: 1.5rem; width: 1.5rem; margin-right: 1rem" {{ ($application->referer == 'POSTER OR NEWS PAPER') ? 'checked' : '' }}>POSTER OR NEWS PAPER</th>
                    <th class="border fs-4" style="font-weight : 600; padding: 0.5rem 1rem;"><input type="checkbox" style="height: 1.5rem; width: 1.5rem; margin-right: 1rem" {{ ($application->referer == 'FLYER OR BANNER') ? 'checked' : '' }}>FLYER OR BANNER</th>
                    <th class="border fs-4" style="font-weight : 600; padding: 0.5rem 1rem;"><input type="checkbox" style="height: 1.5rem; width: 1.5rem; margin-right: 1rem" {{ ($application->referer == 'HIMS STUDENT OR EX-STUDENT') ? 'checked' : '' }}>HIMS STUDENT OR EX-STUDENT</th>
                    <th class="border fs-4" style="font-weight : 600; padding: 0.5rem 1rem;"><input type="checkbox" style="height: 1.5rem; width: 1.5rem; margin-right: 1rem" {{ ($application->referer == 'HIMS STAFF') ? 'checked' : '' }}>HIMS STAFF</th>
                    <th class="border fs-4" style="font-weight : 600; padding: 0.5rem 1rem;"><input type="checkbox" style="height: 1.5rem; width: 1.5rem; margin-right: 1rem" {{ ($application->referer == 'INTERNET OR ADVERTISEMENT') ? 'checked' : '' }}>INTERNET OR ADVERTISEMENT</th>
                </thead>
            </table> --}}
        </div> 
    </div>
    <div class="py-2">
        <div class="bg-white px-3 py-1">
            <h4 class="text-uppercase py-1" style="font-weight: 700;">school/programme chosen</h4>
            <div style="display: flex; flex-wrap: wrap; margin-block: 0.7rem">
                <table class="w-100 border rounded my-4">
                    <thead>
                        <tr>
                            <th class="border">SCHOOL</th>
                            <th class="border">DEGREE</th>
                            <th class="border">PROGRAM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-top border-bottom py-2">
                            <td class="border-left border-right py-4">{{ $department->school }}</td>
                            <td class="border-left border-right py-4">{{ $degree->deg_name }}</td>
                            <td class="border-left border-right py-4">{{ $program->name }}</td>
                        </tr>
                        <tr  class="py-2">
                            <td colspan="2" class="text-capitalize py-1 px-3" >
                                <h4 class="text-uppercase py-1" style="font-weight: 700;">student admission number:</h4>
                                <label  class="form-control w-75 mx-auto"></label>
                            </td>
                            <td class="text-capitalize py-1 px-3">
                                <div class="text-center py-3">
                                    <h6>signature</h6> __________________________
                                </div>
                                <div class="text-center py-3">
                                    <h6>date</h6> __________________________
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <div>
        </div>
    </div>
    {{-- <div class="py-2">
        <div class="bg-white px-3 py-1">
            <h4 class="text-uppercase py-1" style="font-weight: 700;">school/programme chosen</h4>
            <div style="display: flex; flex-wrap: wrap; margin-block: 0.7rem">
                <table class="w-100 border rounded my-4">
                    <thead>
                        <tr>
                            <th class="border">SCHOOL</th>
                            <th class="border">DEGREE</th>
                            <th class="border">PROGRAM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hnd_progs = \App\Models\Degree::where('name', 'HND')->first()->programs()->where('type', 'BUSINESS MANAGEMENT')->get();
                            $btech_progs = \App\Models\Degree::where('name', 'B.TECH')->first()->programs()->where('type', 'BUSINESS MANAGEMENT')->get();
                            $hnd_btech_programs = $hnd_progs->whereIn('id', $btech_progs->pluck('id')->toArray())->toArray();
                            $bba_progs = \App\Models\Degree::where('name', 'BBA')->first()->programs()->where('type', 'BUSINESS MANAGEMENT')->get()->toArray();
                        @endphp
                        @for($i = 0; $i < count($hnd_btech_programs); $i++)
                            <tr>
                                <td class="border-left border-right">{{ $hnd_btech_programs[$i]['name'] }}</td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_btech_programs[$i]['id'] == $application->program) && ($application->degree->name == 'HND') ? 'checked' : '' }}></td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_btech_programs[$i]['id'] == $application->program) && ($application->degree->name == 'B.TECH') ? 'checked' : '' }}></td>
                                <td class="border-left border-right">{{ $bba_progs[$i]['name']??null }}</td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($bba_progs[$i]['id']??null == $application->program) && ($application->degree->name == 'BBA') ? 'checked' : '' }}></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div style="display: flex; flex-wrap: wrap; margin-block: 0.7rem">
                <table class="w-100 border rounded my-4">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-transform: uppercase; font-weight: 700; padding-block: 0.4rem;" class="border-top border-bottom border-2 text-primary w-50 text-center my-0">school of engineering and technology</th>
                            <th colspan="2" style="text-transform: uppercase; font-weight: 700; padding-block: 0.4rem;" class="border-top border-bottom border-2 text-primary w-50 text-center my-0">school of medical and biomedical sciences</th>
                        </tr>
                        <tr>
                            <th class="border"></th>
                            <th class="border">HND</th>
                            <th class="border">B.TECH</th>
                            <th class="border"></th>
                            <th class="border">HND</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hnd_progs = \App\Models\Degree::where('name', 'HND')->first()->programs()->where('type', 'ENGINEERING AND TECHNOLOGY')->get();
                            $btech_progs = \App\Models\Degree::where('name', 'B.TECH')->first()->programs()->where('type', 'ENGINEERING AND TECHNOLOGY')->get();
                            $hnd_btech_programs = $hnd_progs->whereIn('id', $btech_progs->pluck('id')->toArray())->toArray();
                            $hnd_only_progs = \App\Models\Program::where('type', 'MEDICAL AND BIOMEDICAL SCIENCES')->get()->toArray();
                        @endphp
                        @for($i = 0; $i < count($hnd_btech_programs); $i++)
                            <tr>
                                <td class="border-left border-right">{{ $hnd_btech_programs[$i]['name'] }}</td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_btech_programs[$i]['id'] == $application->program) && ($application->degree->name == 'HND') ? 'checked' : '' }}></td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_btech_programs[$i]['id'] == $application->program) && ($application->degree->name == 'B.TECH') ? 'checked' : '' }}></td>
                                <td class="border-left border-right">{{ $hnd_only_progs[$i]['name']??null }}</td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_only_progs[$i]['id']??null == $application->program) && ($application->degree->name == 'HND') ? 'checked' : '' }}></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div style="display: flex; flex-wrap: wrap; margin-block: 0.7rem">
                <table class="w-100 border rounded my-4">
                    <thead>
                        <tr>
                            <th colspan="5" style="text-transform: uppercase; font-weight: 700; padding-block: 0.4rem;" class="border-top border-bottom border-2 text-primary w-100 text-center my-0">school of tourism, logistics and transport management</th>
                        </tr>
                        <tr>
                            <th class="border"></th>
                            <th class="border">HND</th>
                            <th class="border">B.TECH</th>
                            <th class="border"></th>
                            <th class="border">BBA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hnd_progs = \App\Models\Degree::where('name', 'HND')->first()->programs()->where('type', 'TOURISM, LOGISTICS AND TRANSPORT MANAGEMENT')->get();
                            $btech_progs = \App\Models\Degree::where('name', 'B.TECH')->first()->programs()->where('type', 'TOURISM, LOGISTICS AND TRANSPORT MANAGEMENT')->get();
                            $hnd_btech_programs = $hnd_progs->whereIn('id', $btech_progs->pluck('id')->toArray())->toArray();
                            $bba_progs = \App\Models\Degree::where('name', 'BBA')->first()->programs()->where('type', 'TOURISM, LOGISTICS AND TRANSPORT MANAGEMENT')->get()->toArray();
                        @endphp
                        @for($i = 0; $i < count($hnd_btech_programs); $i++)
                            <tr>
                                <td class="border-left border-right">{{ $hnd_btech_programs[$i]['name'] }}</td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_btech_programs[$i]['id'] == $application->program) && ($application->degree->name == 'HND') ? 'checked' : '' }}></td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($hnd_btech_programs[$i]['id'] == $application->program) && ($application->degree->name == 'B.TECH') ? 'checked' : '' }}></td>
                                <td class="border-left border-right">{{ $bba_progs[$i]['name']??null }}</td>
                                <td class="border-left border-right"><input type="checkbox" style="height:1.4rem; width: 1.4rem;" {{ ($bba_progs[$i]['id']??null == $application->program) && ($application->degree->name == 'BBA') ? 'checked' : '' }}></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div> 
    </div> --}}
    {{-- <div class="py-2">
        <div class="bg-white w-100 px-3 py-1">
            <table>
                    
                <thead>
                    <th></th>
                    <th></th>
                <thead>
                <tbody>
                </tbody>
            </table>
        </div> 
    </div> --}}
@endsection