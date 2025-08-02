<?php
namespace App\Http\Services;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Http;
use App\Http\Services\ApiService;
use App\Models\AdmissionLetterPage2;
use App\Models\ApplicationForm;
use App\Models\Config;
use App\Models\ProgramAdmin;
use Barryvdh\DomPDF\Facade\Pdf;

class AppService{

    protected $api_service;
    public function __construct(ApiService $apiServce) {
        $this->api_service = $apiServce;
    }

    public function admission_letter($appl_id)
    {
        # code...
        $appl = ApplicationForm::find($appl_id);
        if($appl != null){
            $programs = collect(json_decode($this->api_service->programs())->data);
            $campus = collect(json_decode($this->api_service->campuses())->data)->where('id', $appl->campus_id)->first()??null;
            $program = $programs->where('id', $appl->program_first_choice)->first()??null;
            $degree = collect(json_decode($this->api_service->degrees())->data)->where('id', $appl->degree_id)->first()??null;
            $config = Config::where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
            $department = collect(json_decode($this->api_service->school_program_structure())->data)->where('program_id', $appl->program)->first();
            
           
            // dd($fees);

            $data['platform_links'] = [];
           
            $data['year'] = substr($appl->year->name, -4);
            $data['_year'] = substr($appl->year->name, 2, 2);
            // dd($data);
            // $data['title'] = "ADMISSION LETTER";
            $data['name'] = $appl->name;
            $data['first_name'] = explode(' ', $appl->name)[0];
            $data['matric'] =  $appl->matric;
            $data['auth_no'] =  time().'/'.random_int(150553, 998545).'/XGS4';
            $data['batch'] = \App\Models\Batch::find(\App\Helpers\Helpers::instance()->getCurrentAccademicYear())->name;
            $data['fee2_dateline'] = $config->fee2_latest_date;
            $data['help_email'] =  $config->help_email;
            $data['campus'] = $campus->name??null;
            $data['degree'] = ($program->deg_name??null) == null ? ("NOT SET") : $program->deg_name;
            $data['program'] = str_replace($data['degree'], ' ', $program->name??"");
            $data['_program'] = $program;
            $data['matric_sn'] = substr($appl->matric, -3);
            $data['department'] = $department->name??'-------';
            $data['start_of_lectures'] = Config::where('year_id', Helpers::instance()->getCurrentAccademicYear())->first()->start_of_lectures??'';
            // dd($program);
            if($data['degree'] ==  null){
                session()->flash('error', 'Program Degree Name not set');
                return back()->withInput();
            }
    
            // return view('admin.student.admission_letter', $data);
            $pdf = Pdf::loadView('admin.student.admission_letter', $data);
            return $pdf->download($appl->matric.'_ADMISSION_LETTER.pdf');            
        }
    }

    public function application_form($application_id){
        $application = ApplicationForm::find($application_id);
        $programs = collect(json_decode($this->api_service->programs())->data);
            $data['campuses'] = json_decode($this->api_service->campuses())->data;
            $data['application'] = ApplicationForm::find($application_id);
            $data['degree'] = collect(json_decode($this->api_service->degrees())->data??[])->where('id', $data['application']->degree_id)->first();
            $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
            $data['certs'] = json_decode($this->api_service->certificates())->data;
            
            $data['department'] = collect(json_decode($this->api_service->school_program_structure())->data)->where('program_id', $application->program)->first();
            $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
            $data['program'] = $programs->where('id', $data['application']->program)->first();
            
            // $title = $application->degree??''.' APPLICATION FOR '.$application->campus->name??' --- '.' CAMPUS';
            $title = __('text.inst_tapplication_form', ['degree'=>$data['degree']->deg_name]);
            $data['title'] = $title;

            // dd($data);
            // if(in_array(null, array_values($data))){ return redirect(route('student.application.start', [0, $application_id]))->with('message', "Make sure your form is correctly filled and try again.");}
            // return view('student.online.form_dawnloadable', $data);
            $pdf = PDF::loadView('student.online.form_dawnloadable', $data);
            $filename = $title.' - '.$application->name.'.pdf';
            return $pdf->download($filename);
    }
}
