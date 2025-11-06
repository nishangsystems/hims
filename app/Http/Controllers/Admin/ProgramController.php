<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\HomeController;
use App\Models\ApplicationForm;
use App\Models\Batch;
use App\Models\ClassSubject;
use App\Models\Config;
use App\Models\EntryQualification;
use App\Models\Level;
use App\Models\Program;
use App\Models\ProgramLevel;
use App\Models\School;
use App\Models\SchoolUnits;
use App\Models\StudentClass;
use App\Models\Students;
use App\Models\Subjects;
use App\Models\Transaction;
use App\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\Environment\Console;
use Throwable;

class ProgramController extends Controller
{

    public function open_admission(Request $request)
    {
        # code...
        $data['title'] = "Configure Admission Session.";
        $data['sessions'] = Config::all();
        $data['current_session'] = Config::where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
        // return $data;
        return view('admin.setting.config_admission', $data);
    }

    public function set_open_admission(Request $request)
    {
        # code...
        $validity = Validator::make($request->all(), ['start_date'=>'required|date', 'end_date'=>'required|date']);
        if($validity->fails()){return back()->with('error', $validity->errors()->first());}

        // return $request->all();
        $config = ['start_date'=>$request->start_date, 'end_date'=>$request->end_date];
        Config::updateOrInsert(['year_id'=>Helpers::instance()->getCurrentAccademicYear()], $config);
        return back()->with('success', __('text.word_done'));
    }

    public function applicants_report_by_degree(Request $request)
    {
        # code...
    }

    public function applicants_report_by_program(Request $request)
    {
        # code...
    }

    public function finance_report_general()
    {
        # code...
    }

    public function config_programs(Request $request, $cid = null)
    {
        # code...
        $data['title'] = "Configure Programs Per Entry Qualification";
        // return $data;

        
        $qlf = json_decode($this->api_service->certificates());
        if($qlf != null){
            $data['certs'] = $qlf->data;
            $data['cert'] = collect($qlf->data)->where('id', $cid)->first();
            if($data['cert'] != null){
                $data['cert_programs'] = collect(json_decode($this->api_service->certificatePrograms($cid))->data)->pluck('id')->toArray();
                $progs = json_decode($this->api_service->programs());
                // return $progs;
                if($progs != null){
                    $data['programs'] = $progs->data;
                }
            }
        }
        // return $data;
        return view('admin.setting.config_program', $data);
    }

    public function set_config_programs(Request $request, $entry_id)
    {
        # code...
        $validity = Validator::make($request->all(), ['programs'=>'required|array']);
        if($validity->fails()){return back()->with('error', $validity->errors()->first());}

        // save program configuration
        $programs = $request->programs;
        $response = $this->api_service->setCertificatePrograms($entry_id, $programs);
        return back()->with('message', $response);
    }

    public function config_degrees(Request $request, $campus_id = null)
    {
        # code...
        $data['title'] = "Configure Campus Degrees";
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['degrees'] = json_decode($this->api_service->degrees())->data;
        if($campus_id != null){
            $degs = $this->api_service->campusDegrees($campus_id);
            if($degs != null){
                $data['campus_degrees'] = collect(json_decode($degs)->data)->pluck('id')->toArray();
            }
        }
           
        return view('admin.setting.configure_campus_degrees', $data);
    }

    public function set_config_degrees(Request $request, $cid)
    {
        # code...
        $validity = Validator::make($request->all(), ['campus_degrees'=>'array']);
        if($validity->fails()){return back()->with('error', $validity->errors()->first());}
        // return $request->all();
        if(($resp = json_decode($this->api_service->setCampusDegrees($cid, $request->campus_degrees??[]))->data) == '1'){
            return back()->with('success', 'Updated successfully');
        }else{
            return back()->with('error', $resp);
        };
    }


    public function applications()
    {
        # code...
        $data['title'] = "All Application Forms";
        $data['_this'] = $this;
        $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
        $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('submitted', 1)->get();
        return view('admin.student.applications', $data);
    }

    // public function admit_student(Request $request, $id = null)
    // {
    //     # code...
    //     ApplicationForm::find($id)->update(['admitted', true]);
    //     return back()->with('success', __('text.word_done'));
    // }

    public function application_details(Request $request, $id)
    {
        # code...
        $application = ApplicationForm::find($id);
    
        $data['certificates'] = collect(json_decode($this->api_service->certificates())->data);
        $data['application'] = $application;
        if($application->entry_qualification != null){
            // dd($application);
            // dd($this->api_service->campusDegreeCertificatePrograms($application->campus_id, $application->degree_id, $application->entry_qualification));
            $data['programs'] = collect(json_decode($this->api_service->campusDegreeCertificatePrograms($application->campus_id, $application->degree_id, $application->entry_qualification))->data??[]);
        }
        // $data['aux_programs'] = \App\Models\Program::where('type', 'auxiliary')->get();
        $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
        $data['degree'] = $application->degree_id == null ? null : $data['degrees']->where('id', $application->degree_id)->first();
        if($data['degree'] != null && (strstr($data['degree']->deg_name, "MBA") || strstr($data['degree']->deg_name, 'master'))){
            $data['is_master'] = 1;
        }
        $data['certificates'] = collect(json_decode($this->api_service->certificates())->data);
        // dd($data);
        $data['title'] = "Application Details For ".$data['application']->name;
        return view('admin.student.show_form', $data);

        
    }

    
    public function print_application_form(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Print Student Application Form";
            $data['_this'] = $this;
            $data['action'] = __('text.word_print');
            $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('submitted', 1)->get();
            return view('admin.student.applications', $data);
        }

        try{
            return $this->app_service->application_form($id);
        }catch(Throwable $th){
            return back()->with('message', $th->getMessage());
        }
    }

    public function edit_application_form(Request $request, $id = null)
    {
        # code...
        $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
        if($id == null){
            $data['title'] = "Edit Student Information";
            $data['_this'] = $this;
            $data['action'] = __('text.word_edit');
            $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where(['year_id'=> Helpers::instance()->getCurrentAccademicYear(), 'submitted'=>1])->get();
            return view('admin.student.applications', $data);
        }

        $application = ApplicationForm::find($id);
        $programs = collect(json_decode($this->api_service->programs())->data);
        $data['programs'] = $programs;
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['application'] = ApplicationForm::find($id);
        $data['degree'] = collect(json_decode($this->api_service->degrees())->data??[])->where('id', $data['application']->degree_id)->first();
        $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
        $data['certs'] = json_decode($this->api_service->certificates())->data;
        
        $data['department'] = collect(json_decode($this->api_service->school_program_structure())->data)->where('program_id', $application->program)->first();
        $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
        $data['program'] = $programs->where('id', $data['application']->program)->first();
        $data['levels'] = collect(json_decode($this->api_service->levels())->data);
        $data['certificates'] = collect(json_decode($this->api_service->certificates())->data);
        
        
        $al_records = collect(json_decode($application->gce_al_record));
        $ol_records = collect(json_decode($application->gce_ol_record));
        $data['ol_general'] = $ol_records->whereNotNull('grade')->count() > 0 ? 1 : 0;
        $data['ol_tech'] = $ol_records->whereNotNull('coef')->count() > 0 ? 2 : 0;
        $data['al_general'] = $al_records->whereNotNull('grade')->count() > 0 ? 1 : 0;
        $data['al_tech'] = $al_records->whereNotNull('coef')->count() > 0 ? 2 : 0;

        if($application->degree_id == 6){
            $data['is_master'] = 1;
        }

        # code...
        $data['title'] = "EDIT APPLICATION FORM FOR ".$data['degrees']->where('id', $data['application']->degree_id)->first()?->deg_name??'';
        return view('admin.student.edit_form', $data);
        
    }

    public function update_application_form(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['name'=>'required']);
        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }

        $application = ApplicationForm::find($id);
        $data = $request->all();

        if($request->gce_ol_record)
        $data['gce_ol_record'] = json_encode(array_values($request->gce_ol_record));
        if($request->gce_al_record)
        $data['gce_al_record'] = json_encode(array_values($request->gce_al_record));

        if($request->previous_trainings != null){
            $data_p1=[];
            $_data = $request->previous_training;
            // return $_data;
            if($_data != null){
                foreach ($_data as $key => $value) {
                    $data_p1[] = ['school'=>$value['school'], 'year'=>$value['year'], 'course'=>$value['course'], 'certificate'=>$value['certificate']];
                }
                $data['previous_training'] = json_encode($data_p1);
                // return $data;
            }
            $data_p2 = [];
            $e_data = $request->employments;
            if($e_data != null){
                foreach ($e_data as $key => $value) {
                    $data_p2[] = ['employer'=>$value['employer'], 'post'=>$value['post'], 'start'=>$value['start'], 'end'=>$value['end'], 'type'=>$value['type']];
                }
                $data['employments'] = json_encode($data_p2);
                // return $data;
            }
        }

        if($request->program != null){
            $levels = collect(json_decode($this->api_service->campusProgramLevels($application->campus_id, $request->program))->data);
            // dd($levels);
            $data['level'] = $levels->first()?->level??'';
        }
        $data = collect($data)->filter(function($value, $key){return $key != '_token';})->toArray();

        $application->update($data);
        if($application->admitted == 1){
            $update = [
                'name'=>$application->name??null, 
                'email'=>$application->email??null, 
                'phone'=>$application->phone??null,
                'address'=>$application->residence??null, 
                'gender'=>$application->gender??null,
                'dob'=>$application->dob??null, 
                'pob'=>$application->pob??null,
                'year_id'=>$application->year_id??null,
                'campus_id'=>$application->campus_id??null, 
                'admission_batch_id'=>$application->year_id??null,
                'fee_payer_name'=>$application->fee_payer_name??null, 
                'program_first_choice'=>$application->program??null, 
                'region'=>$application->_region->name??null,
                'fee_payer_tel'=>$application->fee_payer_tel??null, 
                'division'=>$application->_division->name??null,
                'level'=>$application->level??null
            ];
            $this->api_service->update_student($application->matric, $update);
        }
        return back()->with('success', __('text.word_done'));
    }

    public function uncompleted_application_form(Request $request, $id=null)
    {
        # code...
        $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
        if($id == null){
            $data['title'] = "Uncompleted Application Forms";
            $data['_this'] = $this;
            $data['action'] = __('text.word_show');
            // $data['bypass'] = 'bypass form';
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['applications'] = ApplicationForm::whereNull('transaction_id')->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();\

            // return $data;
            return view('admin.student.applications', $data);
        }
        
        // return $this->api_service->campuses();
        $data['application'] = ApplicationForm::find($id);
        $data['degree'] = $data['degrees']->where('id', $data['degrees']->where('id', $data['application']->degree_id))?->first();


        $data['title'] = "INCOMPLETE APPLICATION FORM FOR ".$data['degree']?->deg_name??'';
        return view('admin.student.show_form', $data);
    }

    public function distant_application_form(Request $request, $id)
    {
        # code...
    }

    public function admission_letter(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Download Admission Letter";
            $data['_this'] = $this;
            $data['action'] = __('text.word_print');
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('admitted', 1)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }
        // print admission letter
        $appl = ApplicationForm::find($id);
        $config = Config::where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
        $data['title'] = "ADMISSION LETTER";
        $data['name'] = $appl->name;
        $data['matric'] = $appl->matric;
        $data['director_name'] = $config->director??'';
        $data['dean_name'] = $config->dean??'';
        $data['fee1_dateline'] = $config->fee1_latest_date??'';
        $data['fee2_dateline'] = $config->fee2_latest_date??'';
        $data['help_email'] = $config->help_email??"admission@slui.org";
        
        // $data['campus'] = collect(json_decode($this->api_service->campuses())->data)->where('id', $appl->campus_id)->first();
        $data['program'] = Program::find($appl->program);
        return view('admin.student.admission_letter', $data);
        $pdf = Pdf::loadView('admin.student.admission_letter', $data);

        return $pdf->download("Admission_Letter_{$appl->matric}.pdf");
    }

    public function admit_application_form(Request $request, $id=null)
    {
        # code...
        if($id == null){
            $data['title'] = "Admit Student";
            $data['_this'] = $this;
            $data['action'] = __('text.word_admit');
            $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('submitted', 1)->where('admitted', 0)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            return view('admin.student.applications', $data);
        }
        if(!$request->has('matric') or ($request->matric == null)){
            // dd($request->matric);
            // GENERATE MATRICULE
            $application = ApplicationForm::find($id);
            if(($programs = json_decode($this->api_service->programs())->data) != null){
                $program = collect($programs)->where('id', $application->program)->first()??null;
                if($program != null){
                    // dd($program);
                    $year = substr(Batch::find(Helpers::instance()->getCurrentAccademicYear())->name, 2, 2);
                    $prefix = $program->prefix??null;//3 char length
                    $suffix = $program->suffix??null;//3 char length
                    $max_count = '';
                    if($prefix == null){
                        return back()->with('error', 'Matricule generation prefix not set.');
                    }

                    $max_matric = json_decode($this->api_service->max_matric($prefix, $year))->data; //matrics starting with '$prefix' sort
                    // dd($max_matric);
                    if($max_matric == null){
                        $max_count = 0;
                    }else{
                        $max_count = intval(substr($max_matric, -3));
                    }

                    NEXT_MATRIC:
                    $next_count = substr('000'.(++$max_count), -3);
                    $student_matric = $prefix.$year.$suffix.$next_count;
                    // dd($student_matric);
                    if(ApplicationForm::where('matric', $student_matric)->where('id', '!=', $id)->count() == 0){
                        $data['title'] = "Student Admission";
                        $data['application'] = $application;
                        $data['program'] = $program;
                        $data['matricule'] = $student_matric;
                        $data['campus'] = collect(json_decode($this->api_service->campuses())->data)->first();
                        // dd($data);
                        return view('admin.student.confirm_admission', $data);
                    }else{
                        # code...
                        goto NEXT_MATRIC;
                    }
                    return back()->with('error', 'Failed to generate matricule');
                }
            }
        }
    }


    public function admit_student(Request $request, $id)
    {

        
        $validity = Validator::make($request->all(), ['matric'=>'required']);
        if($validity->fails()){
            return back()->with('error', 'Missing matricule');
        }
        $application = ApplicationForm::find($id);

        // dd($application);
        // POST STUDENT TO SCHOOL SYSTEM
        // $application->update(['matric' => $request->matric]);

        // dd($request->matric);
        $student_data = [
            'name'=>$application->name??null, 
            'email'=>$application->email??null, 
            'phone'=>$application->phone??null,
            'address'=>$application->residence??null, 
            'gender'=>$application->gender??null,
            'matric'=>$request->matric??null, 
            'dob'=>$application->dob??null, 
            'pob'=>$application->pob??null,
            'year_id'=>$application->year_id??null,
            'campus_id'=>$application->campus_id??null, 
            'admission_batch_id'=>$application->year_id??null,
            'fee_payer_name'=>$application->fee_payer_name??null, 
            'program_first_choice'=>$application->program??null, 
            'region'=>$application->_region->name??null,
            'fee_payer_tel'=>$application->fee_payer_tel??null, 
            'division'=>$application->_division->name??null,
            'level'=>$application->level??null
        ];
        $resp = json_decode($this->api_service->store_student($student_data))->data??null;
        // dd($resp);
        if($resp != null and !is_string($resp)){
           if($resp->status == 1){
                $application->update(['matric'=>$request->matric, 'admitted'=>1, 'admitted_at'=>now()]);

                // Send sms/email notification
                $phone_number = $application->phone;
                if(str_starts_with($phone_number, '+')){
                    $phone_number = substr($phone_number, '1');
                }
                if(strlen($phone_number) <= 9){
                    $phone_number = '237'.$phone_number;
                }
                // dd($phone_number);
                $message="Congratulations {$application->name}. You have been admitted into HIMS for {$application->year->name} . Access your admission portal at https://apply.himsportal.org to download your admission letter";
                $sent = $this->sendSMS($phone_number, $message);

                // Send student admission letter to email
                // $this->send_admission_letter($application->id);

                return redirect(route('admin.applications.admit'))->with('success', "Student admitted successfully.");
           }else
           return back()->with('error', $resp);
       }else{
           return back()->with('error', $resp);
       }



    }

    public function application_form_change_program(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Change Student Program";
            $data['_this'] = $this;
            $data['action'] = __('text.change_program');
            $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['applications'] = ApplicationForm::where('admitted', true)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }

        // return $this->api_service->campuses();
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['application'] = ApplicationForm::find($id);

        if($data['application']->degree_id != null){
            $data['degree'] = collect(json_decode($this->api_service->degrees())->data)->where('id', $data['application']->degree_id)->first();
        }
        if($data['application']->campus_id != null){
            $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
        }
        if($data['application']->degree_id != null){
            $data['certs'] = json_decode($this->api_service->certificates())->data;
        }
        $data['programs'] = json_decode($this->api_service->programs())->data;
        if($data['application']->entry_qualification != null){
            $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
        }
        if($data['application']->program != null){
            $data['program'] = collect($data['programs'])->where('id', $data['application']->program)->first();
            $data['program2'] = collect($data['programs'])->where('id', $data['application']->program)->first();
            // return $data;
        }
        if($data['application']->level != null){
            $data['levels'] = json_decode($this->api_service->levels())->data;
        }
        
        $data['title'] = "CHANGE PROGRAM FOR ".$data['degree']->deg_name;
        return view('admin.student.change_program', $data);
    }

    public function change_program(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['new_program'=>'required', 'level'=>'required']);
        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }
        $data = ['program'=>$request->new_program, 'level'=>$request->level];
        // ApplicationForm::find($id)->update($data);

        // cacche('program_change_data', $data);
        // UPDATE STUDENT IN SCHOOL SYSTEM.
        // 
        // GENERATE MATRICULE
        $application = ApplicationForm::find($id);
        if(($programs = json_decode($this->api_service->programs())->data) != null){
            $program = collect($programs)->where('id', $request->new_program)->first()??null;
            if($program != null){

                $year = substr(Batch::find(Helpers::instance()->getCurrentAccademicYear())->name, 2, 2);
                $prefix = $program->prefix;//3 char length
                $suffix = $program->suffix??'';//3 char length
                $max_count = '';
                if($prefix == null){
                    return back()->with('error', 'Matricule generation prefix not set.');
                }
                $max_matric = json_decode($this->api_service->max_matric($prefix, $year))->data; //matrics starting with '$prefix' sort
                if($max_matric == null){
                    $max_count = 0;
                }else{
                    $max_count = intval(substr($max_matric, strlen($prefix)+4));
                }
                $next_count = substr('0000'.($max_count+1), -4);
                $student_matric = $prefix.$year.$suffix.$next_count;

                if(ApplicationForm::where('matric', $student_matric)->count() == 0){
                    $data['title'] = "Change Student Program";
                    $data['application'] = $application;
                    $data['program'] = $program;
                    $data['matricule'] = $student_matric;
                    $data['level'] = $request->level;
                    $data['campus'] = collect(json_decode($this->api_service->campuses())->data)->where('id', $application->campus_id)->first();
                    return view('admin.student.confirm_change_program', $data);
                }
                return back()->with('error', 'Failed to generate matricule');
            }
        }
        return back()->with('success', 'Done');
    }

    public function change_program_save(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['matric'=>'required']);
        if($validity->fails()){return back()->with('error', 'Missing matricule');}
        $application = ApplicationForm::find($id);
        // dd($application->toJson());
        // (new ApplicationForm())-
        
        
        // POST STUDENT TO SCHOOL SYSTEM
        $resp = json_decode($this->api_service->update_student($application->matric, ['program'=>$application->program_first_choice, 'level'=>$application->level, 'matric'=>$request->matric]))->data??null;
        // dd($resp);
        if($resp != null){
            if(is_array($resp) && $resp['status'] ==1){
                // $application->matric = $request->matric;
                $application->update(['matric'=>$request->matric, 'program'=>$request->program, 'level'=>$request->level, 'admitted'=>1]);
                // Send sms/email notification
                return redirect(route('admin.applications.admit'))->with('success', "Program changed successfully.");
            }else
            return back()->with('error', $resp);
        }
    }

    public function bypass_application_form(Request $request, $id)
    {
        # code...
        // create a relatively null transaction for the student
        

        $application = ApplicationForm::find($id);
        $data['degrees'] = collect(json_decode($this->api_service->degrees())->data);
        $application->update(['transaction_id'=>-1000000000, 'submitted'=>1]);
        return redirect(route('admin.applications.uncompleted'))->with('success', __('text.word_done'));
    }

    public function applications_per_program(Request $request, $program_id = null)
    {
        # code...
        if($program_id == null){
            // select program
            $data['title'] = "Select Program";
            $data['programs'] = json_decode($this->api_service->programs())->data??[];
            return view('admin.student.program_applications', $data);
        }else{
            $progs = collect(json_decode($this->api_service->programs())->data);
            $data['title'] = $progs->where('id', $program_id)->first()->name." Applications";
            $data['progs'] = $progs;
            $data['appls'] = ApplicationForm::where('program_first_choice', $program_id)->get();
            return view('admin.student.program_applications', $data);
        }
    }

    public function applications_per_degree(Request $request, $degree_id = null)
    {
        # code...
        if($degree_id == null){
            $data['title'] = "Select Degree type";
            $data['degrees'] = json_decode($this->api_service->degrees())->data??[];
            return view('admin.student.degree_applications', $data);
        }else{
            $progs = collect(json_decode($this->api_service->programs())->data);
            $degs = collect(json_decode($this->api_service->degrees())->data);
            $data['title'] = $degs->where('id', $degree_id)->first()->name.' Applications';
            $data['progs'] = $progs;
            $data['appls'] = ApplicationForm::where('degree_id', $degree_id)->get();
            return view('admin.student.degree_applications', $data);
        }
    }

    public function finance_general_report(Request $request)
    {
        # code...
        $data['title'] = "General Financial Reports";
        $data['appls'] = ApplicationForm::all();
        return view('admin.student.finance_general', $data);
    }

    public function downlaod_applications()
    {
        # code...
        $data['title'] = "Download Application List";
        $data['current_year'] = \App\Helpers\Helpers::instance()->getCurrentAccademicYear();
        $data['programs'] = Program::all();
        return view('admin.student.download_forms', $data);
    }

    public function download_forms(Request $request, $prog_id = null){
        $fname = '__'.time().'_'.random_int(100000, 999999).'.csv';
        $filename = public_path('uploads/applications');
        $file = $filename.'/'.$fname;
        $file_writer = fopen($file, 'w');
        $headings = ['Name', 'Date of Birth','Gender', 'Nationality', 'Phone number', 'Program'];
        fputcsv($file_writer, $headings, ',');
        $programs = collect(json_decode($this->api_service->programs())->data);
        $data = ApplicationForm::where('year_id', Helpers::instance()->getCurrentAccademicYear())->where('submitted', 1)->whereNotNull('transaction_id')->get();
        if($prog_id != null){
            $data = $data->where('program', $prog_id);
        }
        foreach ($data as $key => $appl) {
            # code...
            fputcsv($file_writer, [
                $appl->name, "{$appl->dob}", $appl->gender, $appl->nationality, "{$appl->phone}", $programs->where('id', $appl->program)->first()->name??''
            ]);
        }
        fclose($file_writer);
        $first = $data->first();
        $name = $prog_id == null ? ("ALL APPLIVATIONS FOR ".substr($first->year->name??'', 0, 4)).'.csv' : ($first->_program->type.' - '.$first->_program->name." ALL APPLIVATIONS FOR ".substr($first->year->name??'', 0, 4)).'.csv';
        return response()->download($file, $name);

    }

    public function application_bypass_report(Request $request){
        $data['title'] = "Application Bypass Report";
        $degrees = collect(json_decode($this->api_service->degrees())->data);
        $programs = collect(json_decode($this->api_service->programs())->data);
        $data['bypasses'] = \App\Models\TranzakTransaction::where(['payment_method'=>'BYPASS'])->join('application_forms', ['application_forms.transaction_id'=>'tranzak_transactions.id'])
            ->where(['application_forms.year_id'=>Helpers::instance()->getCurrentAccademicYear()])->select(['application_forms.*', 'tranzak_transactions.merchant_account_id as user_id', 'tranzak_transactions.transaction_ref'])->distinct()->get()->each(function($rec)use($degrees, $programs){
                $rec->degree_name = optional($degrees->where('id', $rec->degree_id)->first())->deg_name??'';
                $rec->program_name = optional($programs->where('id', $rec->program)->first())->name??'';
                $rec->user = optional(\App\Models\User::find($rec->user_id))->name??'';
                $rec->reason = str_replace('_', ' ', $rec->transaction_ref);
            });
        
        return view('admin.report.application_bypass', $data);
    }

    public function platform_bypass_report(Request $request){
        $data['title'] = "Platform Charges Bypass Report";
        $data['bypasses'] = \App\Models\Charge::where(['charges.type'=>'PLATFORM'])->where('charges.transaction_id', '<=', 0)->where('charges.year_id', Helpers::instance()->getCurrentAccademicYear())
            ->join('students', ['students.id'=>'charges.student_id'])->select(['students.*', 'charges.financialTransactionId'])
            ->distinct()->get()->each(function($rec){
                $rec->reason = str_replace('_', ' ', $rec->financialTransactionId);
            });
    }

    public function degree_certificates($degree_id = null)
    {
        # code...
        $data['title'] = __('text.configure_degree_certificates');
        $data['degrees'] = json_decode($this->api_service->degrees())->data;
        $data['certificates'] = json_decode($this->api_service->certificates())->data;
        if($degree_id != null){
            $data['degree_certificates'] = collect(json_decode($this->api_service->degree_certificates($degree_id))->data)->pluck('id')->toArray();
        }
        // dd($data);
        return view('admin.setting.degree_certs', $data);
    }

    public function set_degree_certificates(Request $request, $degree_id)
    {
        # code...
        $validator = Validator::make($request->all(), ['certificates'=>'required|array']);
        if($validator->fails()){
            return back()->with('error', $validator->errors()->first());
        }
        $certificate_ids = $request->certificates;
        $response = json_decode($this->api_service->set_degree_certificates($degree_id, $certificate_ids));
        if($response->status == 'success'){return back()->with('success', __('text.word_done'));}else{
            return back()->with('error', $response->message);
        }
    }


    public function admission_report(Request $request){
        $data['title'] = "Admission Report For ".Batch::find(Helpers::instance()->getCurrentAccademicYear())?->name??'';
        $data['structure'] = collect($this->api_service->school_program_structure()->get('data'));
        $gpa_classes = collect([
                ['lower' => '3.60', 'upper' => '4.00', 'class' => 'First Class', 'short' => 'First Class'],
                ['lower' => '3.00', 'upper' => '3.59', 'class' => 'Second Class Upper Division', 'short' => 'Second Class - UD'],
                ['lower' => '2.50', 'upper' => '2.99', 'class' => 'Second Class Lower Division', 'short' => 'Second Class - LD'],
                ['lower' => '2.25', 'upper' => '2.49', 'class' => 'Third Class', 'short' => 'Third Class'],
                ['lower' => '2.00', 'upper' => '2.24', 'class' => 'Pass', 'short' => 'Pass'],
            ]);
        $data['applications'] = ApplicationForm::whereNotNull('matric')->orderBy('name')->get()->each(function($rec) use($data, $gpa_classes){
            if($rec->previous_training != null){
                $training = collect(json_decode($rec->previous_training))->first();
            }
            $rec->department = $data['structure']->where('program_id', $rec->program)->first()['department']??'';
            $rec->certificate = $training?->certificate??'';
            $rec->school = $training?->school??'';
            $rec->gpa = $gpa_classes->where('lower', '<=', $training?->gpa??'')->where('upper', '>=', $training?->gpa??'')->first()['class']??'';
        });
        if($request->action){
            $fname = 'admisison_report'.time().'.csv';
            $handler = fopen(public_path('uploads/'.$fname), 'w');
            $headings = ['NAME', 'GENDER', 'REGISTRAtION NUMBER', 'DEPARTMENT', 'PLACE OF BIRHT', 'NATIONALITY', 'ENTRY CERTIFICATE', 'INSTITUTION', 'GRADE', 'HIMS DECISION', 'DECISION OF UBa-HIMS JOINED ADMISSION BOARD'];
            fputcsv($handler, $headings);
            foreach($data['applications'] as $appl){
                fputcsv($handler, [
                    $appl->name, 
                    $appl->gender, 
                    $appl->matric, 
                    $appl->department, 
                    ($appl->dob?->format('Y-m-d')??'').' | '.$appl->pob, 
                    $appl->nationality,
                    $appl->certificate,
                    $appl->school,
                    $appl->gpa,
                    'Favourable',
                    'Admitted'
                ]);
            }
            fclose($handler);

            return response()->download(public_path('uploads/'.$fname))->deleteFileAfterSend();

        }
        return view('admin.student.admission_report', $data);
    }

}
