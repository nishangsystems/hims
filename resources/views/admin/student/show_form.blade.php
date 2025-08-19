@extends('admin.layout')
@section('section')
    <div class="py-4">
        <div class="py-2 row text-capitalize bg-light">
            
            <!-- STAGE 1 PREVIEW -->
            <h4 class="py-1 border-bottom border-top border-warning bg-white text-danger my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;">{{ __('text.word_stage') }} 1: {{ __('text.personal_details_bilang') }}</h4>
            <div class="py-2 col-sm-6 col-md-4 col-lg-5">
                <label class="text-secondary  text-capitalize">{{ __('text.word_name') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->name ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.word_gender_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->gender ?? '' }}</select>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                <label class="text-secondary  text-capitalize">{{ __('text.date_of_birth_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->dob ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.place_of_birth_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->pob ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.ID_card_number') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->id_card_number ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.date_of_issue') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->id_date_of_issue ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.place_of_issue') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->id_place_of_issue ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.word_region_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->nationality ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.region_of_origin') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->_region->region ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.country_of_birth') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->country_of_birth ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                <label class="text-secondary  text-capitalize">{{ __('text.where_did_you_hear_about_us') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->referer ?? '' }}</label>
                </div>
            </div>


            <!-- STAGE 2 -->
            <h4 class="py-1 border-bottom border-top border-warning bg-white text-danger my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;">{{ __('text.word_stage') }} 2: {{ __('text.address_details') }}</h4>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.word_residence_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->residence ?? '' }}<label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-5">
                <label class="text-secondary  text-capitalize">{{ __('text.telephone_number_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->phone ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-5">
                <label class="text-secondary  text-capitalize">{{ __('text.home_slash_business_phone') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->extra_phone ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                <label class="text-secondary  text-capitalize">{{ __('text.word_email_bilang') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0 ">{{ $application->email ?? '' }}</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.word_guardian') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $application->guardian ?? '' }}</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <label class=" text-secondary text-capitalize">{{ __('text.guardian_contact') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $application->guardian_phone ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.guardian_address') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $application->guardian_address ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.word_sponsor') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $application->sponsor ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.sponsor_contact') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $application->sponsor_phone ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.sponsor_address') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $application->sponsor_address ?? '' }}</label>
                </div>
            </div>
            <div class="py-2 col-sm-6 col-md-4 col-lg-3">
                <label class="text-secondary  text-capitalize">{{ __('text.entry_qualification') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $certificates->where('id', $application->entry_qualification)->first()->certi ?? '' }}</label>
                </div>
            </div>

            <!-- STAGE 3 -->
            <h4 class="py-1 border-bottom border-top border-warning bg-white text-danger my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;">{{ __('text.word_stage') }} 3: {{ __('text.academic_records') }}</h4>
            @if (!isset($is_master))
                <h4 class="py-3 border-bottom border-top bg-white text-dark my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;"> {{ __('text.GCE_OL_or_equivalent') }}</h4>
                <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                    <label class="text-secondary  text-capitalize">{{ __('text.secondary_school_attended') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->secondary_school ?? '' }}</label>
                    </div>
                </div>
                <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                    <label class="text-secondary  text-capitalize">{{ __('text.exam_center') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->secondary_exam_center ?? '' }}</label>
                    </div>
                </div>
                <div class="py-2 col-sm-6 col-md-2 col-lg-2">
                    <label class="text-secondary  text-capitalize">{{ __('text.candidate_number') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->secondary_candidate_number ?? '' }}</label>
                    </div>
                </div>
                <div class="py-2 col-sm-6 col-md-2 col-lg-2">
                    <label class="text-secondary  text-capitalize">{{ __('text.academic_year') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->secondary_exam_year ?? '' }}</label>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                    <table class="border">
                        <thead>
                            <tr class="text-capitalize">
                                <th class="text-center border">{{ __('text.word_subject') }}</th>
                                <th class="text-center border">{{ __('text.word_grade') }}</th>
                            <tr>
                        </thead>
                        <tbody id="previous_trainings">
                            @foreach (json_decode($application->gce_ol_record)??[] as $key=>$rec)
                                <tr class="text-capitalize">
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->subject }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->grade }}</label></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 class="py-3 border-bottom border-top bg-white text-dark my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;"> {{ __('text.GCE_AL_BACC_or_equivalent') }}</h4>
                <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                    <label class="text-secondary  text-capitalize">{{ __('text.high_school_attended') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->high_school ?? '' }}</label>
                    </div>
                </div>
                <div class="py-2 col-sm-6 col-md-4 col-lg-4">
                    <label class="text-secondary  text-capitalize">{{ __('text.exam_center') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->high_school_exam_center ?? '' }}</label>
                    </div>
                </div>
                <div class="py-2 col-sm-6 col-md-2 col-lg-2">
                    <label class="text-secondary  text-capitalize">{{ __('text.candidate_number') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->high_school_candidate_number ?? '' }}</label>
                    </div>
                </div>
                <div class="py-2 col-sm-6 col-md-2 col-lg-2">
                    <label class="text-secondary  text-capitalize">{{ __('text.academic_year') }}</label>
                    <div class="">
                        <label class="form-control text-primary border-0">{{ $application->high_school_exam_year ?? '' }}</label>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                    <table class="border">
                        <thead>
                            <tr class="text-capitalize">
                                <th class="text-center border">{{ __('text.word_subject') }}</th>
                                <th class="text-center border">{{ __('text.word_grade') }}</th>
                            <tr>
                        </thead>
                        <tbody id="employments">
                            @foreach (json_decode($application->gce_al_record)??[] as $key=>$rec)
                                <tr class="text-capitalize">
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->subject }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->grade }}</label></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
            @else
                <h4 class="py-3 border-bottom border-top bg-white text-dark my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;"> {{ __('text.previous_higher_education_training_bilang') }}</h4>
                
                <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                    <table class="border">
                        <thead>
                            <tr class="text-capitalize">
                                <th class="text-center border">{{ __('text.word_school') }}</th>
                                <th class="text-center border">{{ __('text.word_year') }}</th>
                                <th class="text-center border">{{ __('text.word_course') }}</th>
                                <th class="text-center border">{{ __('text.word_certificate') }}</th>
                            <tr>
                        </thead>
                        <tbody id="previous_trainings">
                            @foreach (json_decode($application->previous_training)??[] as $key=>$rec)
                                <tr class="text-capitalize">
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->school }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->year }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->course }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->certificate }}</label></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 class="py-3 border-bottom border-top bg-white text-dark my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;"> {{ __('text.employment_history_bilang') }}</h4>
                
                <div class="col-sm-12 col-md-12 col-lg-12 py-2">
                    <table class="border">
                        <thead>
                            <tr class="text-capitalize">
                                <th class="text-center border">{{ __('text.word_employer') }}</th>
                                <th class="text-center border">{{ __('text.word_position') }}</th>
                                <th class="text-center border">{{ __('text.word_from') }}</th>
                                <th class="text-center border">{{ __('text.word_to') }}</th>
                                <th class="text-center border">{{ __('text.part_time') }} | {{ __('text.full_time') }}</th>
                            <tr>
                        </thead>
                        <tbody id="employments">
                            @foreach (json_decode($application->employments)??[] as $key=>$rec)
                                <tr class="text-capitalize">
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->employer }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->post }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->start }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->end }}</label></td>
                                    <td class="border"><label class="form-control text-primary border-0">{{ $rec->type }}</label></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- STAGE 4 -->

            <h4 class="py-1 border-bottom border-top border-warning bg-white text-danger my-4 text-uppercase col-sm-12 col-md-12 col-lg-12" style="font-weight:500;">{{ __('text.word_stage') }} 4: {{ __('text.program_choice') }}</h4>
            <div class="col-sm-12 col-md-6 col-lg-6">
                <label class="text-secondary  text-capitalize">{{ __('text.degree_type') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $degree->deg_name ?? '' }}</label>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6">
                <label class="text-secondary text-capitalize">{{ __('text.word_program') }}</label>
                <div class="">
                    <label class="form-control text-primary border-0">{{ $programs->where('id', $application->program)->first()->name ?? '' }}</label>
                </div>
            </div>
        </div>
    </div>
@endsection