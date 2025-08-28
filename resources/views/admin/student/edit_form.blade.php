@extends('admin.layout')
@php
    $ol_key = time().random_int(1000, 1099);
    $al_key = time().random_int(2000, 2099);
@endphp
@section('section')
    <div class="py-4">
        <form enctype="multipart/form-data" id="application_form" method="post">
            @csrf
            <div class="py-2 row text-capitalize bg-light">
                <!-- STAGE 1 PREVIEW -->
                    <div class="py-2 row bg-light border-top shadow">
                        <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;">{{ __('text.word_stage') }} 1: {{ __('text.personal_details') }} : <span class="text-danger">APPLYING FOR A/AN {{ $degree->name??null }} PROGRAM</span></h4>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-5">
                            <label class="text-secondary  text-capitalize">{{ __('text.word_name_bilang') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="name" value="{{ $application->name??'' }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.date_of_birth_bilang') }}</label>
                            <div class="">
                                <input type="date" class="form-control text-primary"  name="dob" value="{{ $application->dob->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.place_of_birth_bilang') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="pob" value="{{ $application->pob }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.word_gender_bilang') }}</label>
                            <div class="">
                                <select class="form-control text-primary"  name="gender" required>
                                    <option value="male" {{ $application->gender == 'male' ? 'selected' : '' }}>{{ __('text.word_male') }}</option>
                                    <option value="female" {{ $application->gender == 'female' ? 'selected' : '' }}>{{ __('text.word_female') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.ID_card_number') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="id_card_number" value="{{ $application->id_card_number }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.date_of_issue') }}</label>
                            <div class="">
                                <input type="date" class="form-control text-primary"  name="id_date_of_issue" value="{{ $application->id_date_of_issue }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.place_of_issue') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="id_place_of_issue" value="{{ $application->id_place_of_issue }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.word_nationality') }}</label>
                            <div class="">
                                <select class="form-control text-primary"  name="nationality" required>
                                    <option></option>
                                    @foreach(config('all_countries.list') as $key=>$value)
                                        <option value="{{ $value['name'] }}" {{ $application->nationality== $value['name'] ? 'selected' : ($value['name'] == 'Cameroon' ? 'selected' : '') }}>{{ $value['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.region_of_origin') }}</label>
                            <div class="">
                                <select class="form-control text-primary"  name="region" required oninput="loadDivisions(event)">
                                    <option value=""></option>
                                    @foreach(\App\Models\Region::all() as $value)
                                        <option value="{{ $value->id }}" {{ $application->region == $value->id ? 'selected' : '' }}>{{ $value->region }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.country_of_birth') }}</label>
                            <div class="">
                                <select class="form-control text-primary"  name="country_of_birth" required>
                                    <option></option>
                                    @foreach(config('all_countries.list') as $key=>$value)
                                        <option value="{{ $value['name'] }}" {{ $application->country_of_birth == $value['name'] ? 'selected' : ($value['name'] == 'Cameroon' ? 'selected' : '') }}>{{ $value['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.where_did_you_hear_about_us') }}</label>
                            <div class="">
                                <select class="form-control text-primary"  name="referer" required onchange="specify_source(event)">
                                    <option value=""></option>
                                    <option value="POSTER OR NEWS PAPER" {{ $application->referer== 'POSTER OR NEWS PAPER' ? 'selected' : '' }}>POSTER OR NEWS PAPER</option>
                                    <option value="FLYER OR BANNER" {{ $application->referer== 'FLYER OR BANNER' ? 'selected' : '' }}>FLYER OR BANNER</option>
                                    <option value="STUDENT OR EX-STUDENT" {{ $application->referer== 'STUDENT OR EX-STUDENT' ? 'selected' : '' }}>STUDENT OR EX-STUDENT</option>
                                    <option value="STAFF" {{ $application->referer== 'STAFF' ? 'selected' : '' }}>STAFF</option>
                                    <option value="INTERNET OR ADVERTISEMENT" {{ $application->referer== 'INTERNET OR ADVERTISEMENT' ? 'selected' : '' }}>INTERNET OR ADVERTISEMENT</option>
                                    <option value="OTHERS" {{ $application->referer== 'OTHERS' ? 'selected' : '' }}  >OTHERS</option>
                                </select>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3" id="specify_source"></div>
                    </div>
                    
                <!-- STAGE 2 -->
                    <div class="py-2 row bg-light border-top shadow">
                        <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;">{{ __('text.word_stage') }} 2: {{ __('text.address_details') }} : <span class="text-danger">APPLYING FOR A/AN {{ $degree->name ?? null }} PROGRAM</span></h4>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.word_residence_bilang') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="residence" value="{{ $application->residence }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.telephone_number_bilang') }}</label>
                            <div class="">
                                <input type="tel" class="form-control text-primary"  name="phone" value="{{ $application->phone??'' }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.home_slash_business_phone') }}</label>
                            <div class="">
                                <input type="tel" class="form-control text-primary"  name="extra_phone" value="{{ $application->extra_phone }}">
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.word_email_bilang') }}</label>
                            <div class="">
                                <input type="email" class="form-control text-primary"  name="email" value="{{ $application->email ?? '' }}" {{ $application->email != null ? 'readonly' : '' }}>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-5">
                            <label class="text-secondary  text-capitalize">{{ __('text.guardian_slash_parent_name') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="guardian" value="{{ $application->guardian }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.guardian_contact') }}</label>
                            <div class="">
                                <input type="tel" class="form-control text-primary"  name="guardian_phone" value="{{ $application->guardian_phone }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.guardian_address') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="guardian_address" value="{{ $application->guardian_address }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-5">
                            <label class="text-secondary  text-capitalize">{{ __('text.sponsor_name') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="sponsor" value="{{ $application->sponsor }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.sponsor_contact') }}</label>
                            <div class="">
                                <input type="tel" class="form-control text-primary"  name="sponsor_phone" value="{{ $application->sponsor_phone }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.sponsor_address') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="sponsor_address" value="{{ $application->sponsor_address }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.entry_qualification') }}</label>
                            <div class="">
                                <select class="form-control text-primary"  name="entry_qualification" value="{{ $application->sponsor_address }}" required>
                                    <option><option>
                                    @foreach ($certificates as $key => $cert)
                                        <option value="{{ $cert->id }}" {{ old('entry_qualification', $application->entry_qualification) == $cert->id ? 'selected' : '' }}>{{ $cert->certi??'----' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                <!-- STAGE 3 -->
                    <div class="py-2 row bg-light border-top shadow">
                        <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;">{{ __('text.word_stage') }} 3: {{ __('text.academic_records') }} : <span class="text-danger">APPLYING FOR A/AN {{ $degree->name ?? '' }} PROGRAM</span></h4>
                        <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;"> {{ __('text.GCE_OL_or_equivalent') }} </h4>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.secondary_school_attended') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="secondary_school" value="{{ $application->secondary_school }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.exam_center') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="secondary_exam_center" value="{{ $application->secondary_exam_center }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-2">
                            <label class="text-secondary  text-capitalize">{{ __('text.candidate_number') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="secondary_candidate_number" value="{{ $application->secondary_candidate_number }}" required>
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-2">
                            <label class="text-secondary  text-capitalize">{{ __('text.academic_year') }}</label>
                            <div class="">
                                <select type="text" class="form-control text-primary"  name="secondary_exam_year" required>
                                    <option></option>
                                    @for($i = 2000; $i < (int)(now()->format('Y'))+1; $i++)
                                        @php
                                            $yr = $i.'/'.$i+1;
                                        @endphp
                                        <option value="{{ $yr }}" {{ $application->secondary_exam_year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                            <table class="border table-responsive" style="width: 100%;">
                                <thead>
                                    <tr class="text-capitalize">
                                        <th class="text-center border-0" colspan="3">
                                            <div class="d-flex justify-content-start py-2 w-100">
                                                <span class="btn btn-sm px-4 py-1 btn-primary rounded" onclick="addOlResult()">add subject</span> <br><span style="text-transform: lowercase; color: skyblue; font-weight: 600;">scroll right for more</span>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr class="text-capitalize">
                                        <th class="text-center border"></th>
                                        <th class="text-center border">{{ __('text.subject_attempted') }}</th>
                                        <th class="text-center border" style="width: 3rem;">{{ __('text.word_grade') }}</th>
                                        <th class="text-center border" style="width: 3rem;">Coef (CAP)</th>
                                        <th class="text-center border" style="width: 3rem;">Note * Coef (CAP)</th>
                                    <tr>
                                </thead>
                                <tbody id="gce_ol_record">
                                    @foreach (json_decode($application->gce_ol_record)??[] as $key=>$result)
                                        @php
                                            $ol_key++;
                                        @endphp
                                        <tr class="text-capitalize">
                                            <td class="border"><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropOlResult(event)">{{ __('text.word_drop') }}</span></td>
                                            <td class="border"><input class="form-control text-primary"  name="gce_ol_record[{{ $ol_key }}][subject]" required value="{{ $result->subject }}"></td>
                                            <td class="border">
                                                <select class="form-control text-primary input imput-sm"  name="gce_ol_record[{{ $ol_key }}][grade]">
                                                    <option value=""></option>
                                                    <option value="A" {{ $result->grade == 'A' ? 'selected' : '' }}>A</option>
                                                    <option value="B" {{ $result->grade == 'B' ? 'selected' : '' }}>B</option>
                                                    <option value="C" {{ $result->grade == 'C' ? 'selected' : '' }}>C</option>
                                                </select>
                                            </td>
                                            <td class="border"><input class="form-control text-primary"  name="gce_ol_record[{{ $ol_key }}][coef]" value="{{ $result->coef??'' }}"></td>
                                            <td class="border"><input class="form-control text-primary"  name="gce_ol_record[{{ $ol_key }}][nc]" value="{{ $result->nc??'' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>


                        <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;">{{ __('text.GCE_AL_BACC_or_equivalent') }}</h4>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                            <label class="text-secondary  text-capitalize">{{ __('text.high_school_attended') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="high_school" value="{{ $application->high_school }}">
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                            <label class="text-secondary  text-capitalize">{{ __('text.exam_center') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="high_school_exam_center" value="{{ $application->high_school_exam_center }}">
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-2">
                            <label class="text-secondary  text-capitalize">{{ __('text.candidate_number') }}</label>
                            <div class="">
                                <input type="text" class="form-control text-primary"  name="high_school_candidate_number" value="{{ $application->high_school_candidate_number }}">
                            </div>
                        </div>
                        <div class="py-2 col-sm-6 col-md-4 col-lg-2">
                            <label class="text-secondary  text-capitalize">{{ __('text.academic_year') }}</label>
                            <div class="">
                                <select type="text" class="form-control text-primary"  name="high_school_exam_year">
                                    <option></option>
                                    @for($i = 2000; $i < (int)(now()->format('Y'))+1; $i++)
                                        @php
                                            $yr = $i.'/'.$i+1;
                                        @endphp
                                        <option value="{{ $yr }}" {{ $application->high_school_exam_year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                            <table class="border table-responsive"  style="width: 100%;">
                                <thead>
                                    <tr class="text-capitalize">
                                        <th class="text-center border-0" colspan="3">
                                            <div class="d-flex justify-content-start py-2 w-100">
                                                <span class="btn btn-sm px-4 py-1 btn-primary rounded" onclick="addAlResult()">add subject</span> <br><span style="text-transform: lowercase; color: skyblue; font-weight: 600;">scroll right for more</span>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr class="text-capitalize">
                                        <th class="text-center border"></th>
                                        <th class="text-center border">{{ __('text.subject_attempted') }}</th>
                                        <th class="text-center border" style="width: 3rem;">{{ __('text.word_grade') }}</th>
                                        <th class="text-center border" style="width: 3rem;">Coef (BACC)</th>
                                        <th class="text-center border" style="width: 3rem;">Note * Coef (BACC)</th>
                                    <tr>
                                </thead>
                                <tbody id="gce_al_record">
                                    @foreach (json_decode($application->gce_al_record)??[] as $key=>$record)
                                        @php
                                            $al_key++;
                                        @endphp
                                        <tr class="text-capitalize">
                                            <td class="border"><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropAlResult(event)">{{ __('text.word_drop') }}</span></td>
                                            <td class="border"><input class="form-control text-primary"  name="gce_al_record[{{ $al_key }}][subject]" value="{{ $record->subject }}"></td>
                                            <td class="border">
                                                <select class="form-control text-primary input imput-sm"  name="gce_al_record[{{ $al_key }}][grade]">
                                                    <option value=""></option>
                                                    <option value="A" {{ $record->grade == 'A' ? 'selected' : '' }}>A</option>
                                                    <option value="B" {{ $record->grade == 'B' ? 'selected' : '' }}>B</option>
                                                    <option value="C" {{ $record->grade == 'C' ? 'selected' : '' }}>C</option>
                                                    <option value="D" {{ $record->grade == 'D' ? 'selected' : '' }}>D</option>
                                                    <option value="E" {{ $record->grade == 'E' ? 'selected' : '' }}>E</option>
                                                </select>
                                            </td>
                                            <td class="border"><input class="form-control text-primary"  name="gce_al_record[{{ $al_key }}][coef]" value="{{ $record->coef??'' }}"></td>
                                            <td class="border"><input class="form-control text-primary"  name="gce_al_record[{{ $al_key }}][nc]" value="{{ $record->nc??'' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>    
                    @if ($application->alternate())
                        <div class="py-2 row bg-light border-top shadow">
                            <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;"> @if ($application->degree_id == 6)  {{ __('text.bachelors_degree_bilang') }} @else  HND Result @endif </h4>
                            <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                                <table class="border">
                                    <thead>
                                        <tr class="text-capitalize">
                                            <th class="text-center border-0" colspan="5">
                                                <div class="d-flex justify-content-end py-2 w-100">
                                                    <span class="btn btn-sm px-4 py-1 btn-success rounded" onclick="addTraining()">{{ __('text.word_add') }}</span>
                                                </div>
                                            </th>
                                        </tr>
                                        <tr class="text-capitalize">
                                            <th class="text-center border">{{ __('text.word_school_bilang') }}</th>
                                            <th class="text-center border">{{ __('text.word_year_bilang') }}</th>
                                            <th class="text-center border">{{ __('text.word_course_bilang') }}</th>
                                            <th class="text-center border">{{ __('text.word_certificate_bilang') }}</th>
                                            <th class="text-center border">{{ __('text.gpa_slash_average') }}</th>
                                            <th class="text-center border"></th>
                                        <tr>
                                    </thead>
                                    <tbody id="previous_trainings">
                                        @foreach (json_decode($application->previous_training)??[] as $key=>$training)
                                            <tr class="text-capitalize">
                                                <td class="border"><input class="form-control text-primary"  name="previous_training[{{ $key }}][school]" required value="{{ $training->school }}" placeholder="institution"></td>
                                                <td class="border"><select class="form-control text-primary"  name="previous_training[{{ $key }}][year]" required aria-placeholder="year">
                                                    <option value=""></option>
                                                    @for($i = 1980; $i <= 2500; $i++)
                                                        <option value="{{ $i }}" {{ $training->year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select></td>
                                                <td class="border"><input class="form-control text-primary"  name="previous_training[{{ $key }}][course]" required value="{{ $training->course??'' }}" placeholder="course"></td>
                                                <td class="border"><input class="form-control text-primary"  name="previous_training[{{ $key }}][certificate]" required value="{{ $training->certificate??'' }}" placeholder="certificate"></td>
                                                <td class="border"><input class="form-control text-primary"  name="previous_training[{{ $key }}][gpa]" required value="{{ $training->gpa??'' }}" placeholder="GPA"></td>
                                                <td class="border"><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropTraining(event)">{{ __('text.word_drop') }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($application->degree_id == 6)
                                <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;">{{ __('text.employment_history_bilang') }}</h4>
                                <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                                    <table class="border">
                                        <thead>
                                            <tr class="text-capitalize">
                                                <th class="text-center border-0" colspan="6">
                                                    <div class="d-flex justify-content-end py-2 w-100">
                                                        <span class="btn btn-sm px-4 py-1 btn-success rounded" onclick="addEmployment()">{{ __('text.word_add') }}</span>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr class="text-capitalize">
                                                <th class="text-center border">{{ __('text.employer_name_and_address_bilang') }}</th>
                                                <th class="text-center border">{{ __('text.post_held_bilang') }}</th>
                                                <th class="text-center border">{{ __('text.word_from_bilang') }}</th>
                                                <th class="text-center border">{{ __('text.word_to_bilang') }}</th>
                                                <th class="text-center border">{{ __('text.full_or_parttime_bilang') }}</th>
                                                <th class="text-center border"></th>
                                            <tr>
                                        </thead>
                                        <tbody id="employments">
                                            @foreach (json_decode($application->employments)??[] as $key=>$emp)
                                                <tr class="text-capitalize">
                                                    <td class="border"><input class="form-control text-primary"  name="employments[$key][employer]" required value="{{ $emp->employer }}"></td>
                                                    <td class="border"><input class="form-control text-primary"  name="employments[$key][post]" required value="{{ $emp->post }}"></td>
                                                    <td class="border"><select class="form-control text-primary"  name="employments[$key][start]" required value="{{ $emp->start }}">
                                                        <option value=""></option>
                                                        @for($i = 1980; $i <= 2500; $i++)
                                                            <option value="{{ $i }}" {{ $emp->start == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                    </select></td>
                                                    <td class="border"><select class="form-control text-primary"  name="employments[$key][end]">
                                                        <option value=""></option>
                                                        @for($i = 1980; $i <= 2500; $i++)
                                                            <option value="{{ $i }}" {{ $emp->end == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                        @endfor
                                                    </select></td>
                                                    <td class="border">
                                                        <select class="form-control text-capitalize text-primary" name="employments[$key][type]" required>
                                                            <option selected></option>
                                                            <option value="full-time" {{ $emp->type =='full-time' ? 'selected' : '' }}>{{ __('text.full_time') }}</option>
                                                            <option value="part-time" {{ $emp->type =='part-time' ? 'selected' : '' }}>{{ __('text.part_time') }}</option>
                                                        </select>
                                                    </td>
                                                    <td class="border"><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropEmployment(event)">{{ __('text.word_drop') }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>                    
                    @endif

                <!-- STAGE 4 -->

                    <div class="py-2 row bg-light border-top shadow">
                        <h4 class="py-3 border-bottom border-top bg-white text-primary my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:800;">{{ __('text.word_stage') }} 4: {{ __('text.program_choice') }} : <span class="text-danger">APPLYING FOR A/AN {{ $degree->name??'' }} PROGRAM</span></h4>
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <label class="text-secondary  text-capitalize">{{ __('text.degree_type') }}</label>
                            <div class="">
                                <select class="form-control text-capitalize text-primary" disabled >
                                    <option></option>
                                    @foreach($degrees as $degree)
                                        <option value="{{ $degree->id }}" {{ $application->degree_id == $degree->id ? 'selected' : '' }}>{{ $degree->deg_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-sm-12 col-md-6 col-lg-6">
                            <label class="text-secondary  text-capitalize">{{ __('text.word_program') }}</label>
                            <div class="">
                                <select class="form-control text-capitalize text-primary" name="program" required onchange="loadCpLevels(event)">
                                    <option></option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('program', $application->program) == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="level" id="level_field">
                    </div>

                
                <div class="col-sm-12 col-md-12 col-lg-12 py-4 mt-5 d-flex justify-content-center text-uppercase">
                    <a href="{{ route('admin.applications.update') }}" class="px-4 py-1 btn btn-sm btn-dark text-uppercase">{{ __('text.word_back') }}</a>
                    <button type="submit" class="px-4 py-1 btn btn-sm btn-primary text-uppercase">{{ __('text.word_update') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>

        $(document).ready(function(){
            if("{{ $application->degree_id }}" != null){
                loadCampusDegrees('{{ $application->campus_id }}');
            }
            if("{{ $application->division }}" != null){
                setDivisions('{{ $application->region }}');
            }
            if("{{ $application->level }}" != null){
                setLevels("{{ $application->program_first_choice }}");
            }
        });

        // momo preview generator
        let momoPreview = function(event){
            let file = event.target.files[0];
            if(file != null){
                let url = URL.createObjectURL(file);
                $('#momo_image_preview').attr('src', url);
            }
        }


        // Add and drop previous trainings form table rows
        let addAlResult = function(){
            let key = '_key_'+Date.now()+'_'+Math.random()*10000;
            let html = `<tr class="text-capitalize">
                            <td><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropAlResult(event)">{{ __('text.word_drop') }}</span></td>
                            <td><input class="form-control text-primary"  name="gce_al_record[${key}][subject]" required value="" placeholder="SUBJECT"></td>
                            <td>
                                <select class="form-control text-primary"  name="gce_al_record[${key}][grade]">
                                    <option value=""></option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                </select>
                            </td>
                            <td><input class="form-control text-primary"  name="gce_al_record[${key}][coef]" value="" placeholder="COEF"></td>
                            <td><input class="form-control text-primary"  name="gce_al_record[${key}][nc]" value="" placeholder="NOTE*COEF"></td>
                        </tr>`;
            $('#gce_al_record').append(html);
        } 

        let dropAlResult = function(event){
            let training = $(event.target).parent().parent();
            // let training = $('#previous_trainings').children().last();
            if(training != null){
                training.remove();
            }
        }
        // Add and drop employment form table rows
        let addOlResult = function(){
            let key = '_key_'+Date.now()+'_'+Math.random()*10000;
            let html = `<tr class="text-capitalize">
                            <td><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropOlResult(event)">{{ __('text.word_drop') }}</span></td>
                            <td><input class="form-control text-primary"  name="gce_ol_record[${key}][subject]" required value="" placeholder="SUBJECT"></td>
                            <td>
                                <select class="form-control text-primary"  name="gce_ol_record[${key}][grade]">
                                    <option value=""></option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                </select>
                            </td>
                            <td><input class="form-control text-primary"  name="gce_ol_record[${key}][coef]" value="" placeholder="COEF"></td>
                            <td><input class="form-control text-primary"  name="gce_ol_record[${key}][nc]" value="" placeholder="NOTE * COEF"></td>
                        </tr>`;
            $('#gce_ol_record').append(html);
        } 

        let dropOlResult = function(event){
            let training = $(event.target).parent().parent();
            // let training = $('#previous_trainings').children().last();
            if(training != null){
                training.remove();
            }
        }



                // Add and drop previous trainings form table rows
        let addTraining = function(){
            let key = `_key_${ Date.now() }_${ Math.random()*1000000 }`;
            let html = `<tr class="text-capitalize">
                            <td class="border"><input class="form-control text-primary"  name="previous_training[${key}][school]" required value="" placeholder="SCHOOL"></td>
                            <td class="border"><select class="form-control text-primary"  name="previous_training[${key}][year]" required>
                                                    <option></option>
                                                    @for($i = 1980; $i <= 2500; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select></td>
                            <td class="border"><input class="form-control text-primary"  name="previous_training[${key}][course]" required value="" placeholder="COURSE"></td>
                            <td class="border"><input class="form-control text-primary"  name="previous_training[${key}][certificate]" required value="" placeholder="CERTIFICATE"></td>
                            <td class="border"><input class="form-control text-primary"  name="previous_training[${key}][gpa]" required value="" placeholder="GPA"></td>
                            <td class="border"><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropTraining(event)">{{ __('text.word_drop') }}</span></td>
                        </tr>`;
            $('#previous_trainings').append(html);
        } 

        let dropTraining = function(event){
            let training = $(event.target).parent().parent();
            // let training = $('#previous_trainings').children().last();
            if(training != null){
                training.remove();
            }
        }
        // Add and drop employment form table rows
        let addEmployment = function(){
            let key = `_key_${ Date.now() }_${ Math.random()*1000000 }`;
            let html = `<tr class="text-capitalize">
                            <td class="border"><input class="form-control text-primary"  name="employments[${key}][employer]" required value="" placeholder="EMPLOYER"></td>
                            <td class="border"><input class="form-control text-primary"  name="employments[${key}][post]" required value="" placeholder="POST"></td>
                            <td class="border"><select class="form-control text-primary"  name="employments[${key}][start]">
                                                    <option></option>
                                                    @for($i = 1980; $i <= 2500; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select></td>
                            <td class="border"><select class="form-control text-primary"  name="employments[${key}][end]">
                                                    <option></option>
                                                    @for($i = 1980; $i <= 2500; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select></td>
                            <td class="border">
                                <select class="form-control text-capitalize" name="employments[${key}][type]" required>
                                    <option selected></option>
                                    <option value="full-time">{{ __('text.full_time') }}</option>
                                    <option value="full-time">{{ __('text.part_time') }}</option>
                                </select>
                            </td>
                            <td class="border"><span class="btn btn-sm px-4 py-1 btn-danger rounded" onclick="dropEmployment(event)">{{ __('text.word_drop') }}</span></td>
                        </tr>`;
            $('#employments').append(html);
        } 

        let dropEmployment = function(event){
            let training = $(event.target).parent().parent();
            // let training = $('#previous_trainings').children().last();
            if(training != null){
                training.remove();
            }
        }

        let check_specify = function(event){
            if(event.target.value == '__SPECIFY__'){
                let name = $(event.target).attr('name');
                let html = `<input class="form-control text-primary text-uppercase"  placeholder="specify subject"  name="${name}" required>`;
                $(event.target).parent().html(html);
            }
        }

        let completeForm = function(){
            let confirmed = confirm('By clicking this button, you are confirming that every information supplied is correct.');
            if(confirmed){
                $('#application_form').submit();
            }
        }

        let setDegreeTypes = function(event){
            let campus = event.target.value;
            loadCampusDegrees(campus);
        }

        let loadCampusDegrees = function(campus){
            url = `{{ route('student.campus.degrees', '__CID__') }}`.replace('__CID__', campus);
            $.ajax({
                method: 'get', url: url,
                success: function(data){
                    console.log(data);
                    let html = `<option>{{ __('text.select_degree_type') }}</option>`;
                    data.forEach(element => {
                        html+=`<option value="${element.id}" ${ '{{ $application->degree_id }}' == element.id ? 'selected' : '' } >${element.deg_name}</option>`;
                    });
                    $('#degree_types').html(html);
                }
            })
        }

        let loadDivisions = function(event){
            let region = event.target.value;
            setDivisions(region);
        }

        let setDivisions = function(region){
            url = "{{ route('student.region.divisions', '__RID__') }}".replace('__RID__', region);
            $.ajax({
                method: 'get', url: url, 
                success: function(data){
                    let html = `<option>{{ __('text.select_division') }}</option>`
                    data.forEach(element => {
                        html+=`<option value="${element.id}" ${'{{ $application->division}}' == element.id ? 'selected' : '' }>${element.name}</option>`.replace('region_id', element.id)
                    });
                    $('#divisions').html(html);
                }
            })
        }

        let campusDegreeCertPorgrams = function(event){
            cert_id = event.target.value;
            campus_id = "{{ $application->campus_id }}";
            degree_id = "{{ $application->degree_id }}";

            url = "{{ route('student.campus.degree.cert.programs', ['__CmpID__', '__DegID__', '__CertID__']) }}".replace('__CmpID__', camus_id).replace('__DegID__').replace('__CertID__');
            $.ajax({
                method: 'get', url: url,
                success: function(data){
                    console.log(data);
                    let html = `<option></option>`;
                    data.forEach(element=>{
                        html += `<option value="${element.id}">${element.certi}</option>`;
                    })

                }
            })
        }

        let loadCplevels = function(event){
            campus_id = "{{ $application->campus_id }}";
            program_id = event.target.value;

            setLevels(program_id);
        }

        let setLevels = function(program_id){

            campus_id = "{{ $application->campus_id }}";

            url = "{{ route('student.campus.program.levels', ['__CmpID__', '__PrgID__']) }}".replace('__CmpID__', campus_id).replace('__PrgID__', program_id);
            $.ajax({
                method : 'get', url : url, 
                success : function(data){
                    console.log(data);
                    let html = `<option></option>`;
                    let  i = 1;
                    data.forEach(element=>{
                        if(i == 1){
                            $('#level_field').val(element.level);
                        }
                        html += `<option value="${element.level}" ${ "{{ $application->level }}" == element.level ? 'selected' : ''}>${element.level}</option>`;
                    });
                    $('#cplevels').html(html);
                }
            });
        }

        let specify_source = function(event){
            if(event.target.value == 'OTHERS'){
                let html = `
                    <label class="text-secondary  text-capitalize">{{ __('text.word_specify') }}</label>
                    <div class="">
                        <input type="text" class="form-control text-primary"  name="referer" required>
                    </div>`;
                $('#specify_source').html(html);
            }else{
                $('#specify_source').html('');
            }
        }

    </script>
@endsection