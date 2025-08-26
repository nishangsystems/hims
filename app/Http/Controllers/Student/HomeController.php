<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionController;
use App\Http\Services\ApiService;
use App\Http\Services\SMSHelpers;
use App\Models\ApplicationForm;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\CampusProgram;
use App\Models\CampusSemesterConfig;
use App\Models\Charge;
use App\Models\ClassSubject;
use App\Models\Config;
use App\Models\CourseNotification;
use App\Models\Degree;
use App\Models\Income;
use App\Models\Material;
use App\Models\NonGPACourse;
use App\Models\Notification;
use App\Models\PayIncome;
use App\Models\Payments;
use App\Models\PlatformCharge;
use App\Models\ProgramLevel;
use App\Models\Resit;
use App\Models\Result;
use App\Models\SchoolUnits;
use App\Models\Semester;
use App\Models\Sequence;
use App\Models\StudentClass;
use App\Models\Students;
use App\Models\StudentStock;
use App\Models\StudentSubject;
use App\Models\SubjectNotes;
use App\Models\Subjects;
use App\Models\Topic;
use App\Models\Transaction;
use App\Models\Transcript;
use App\Models\TranzakCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    private $years;
    private $batch_id;
    private $select = [
        'students.id as student_id',
        'collect_boarding_fees.id',
        'students.name',
        'students.matric',
        'collect_boarding_fees.amount_payable',
        'collect_boarding_fees.status',
        'school_units.name as class_name'
    ];

    private $select_boarding = [
        'students.id as student_id',
        'students.name',
        'students.matric',
        'collect_boarding_fees.id',
        'boarding_amounts.created_at',
        'boarding_amounts.amount_payable',
        'boarding_amounts.total_amount',
        'boarding_amounts.status',
        'boarding_amounts.balance'
    ];

    public function index()
    {
        return view('student.dashboard');
    }

    public function fee()
    {
        $data['title'] = "Tution Report";
        return view('student.fee')->with($data);
    }

    public function other_incomes()
    {
        $data['title'] = "Other Payments Report";
        return view('student.other_incomes', $data);
    }

    public function result(Request $request)
    {
        # code...
        $data['title'] = "My Result";
        return view('student.result')->with($data);
    }

    public function subject()
    {
        $data['title'] = "My Subjects";
        //     dd($data);
        return view('student.subject')->with($data);
    }

    public function profile()
    {
        return view('student.edit_profile');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:9|max:15',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->with(['e' => $validator->errors()->first()]);
        }

        $data['success'] = 200;
        $user = auth('student')->user();
        $user->email = $request->email??null;
        $user->phone = $request->phone;
        $user->save();
        $data['user'] = auth('student')->user();
        return redirect()->back()->with(['s' => 'Updated Successful']);
    }


    public function __construct( ApiService $service, \App\Http\Services\AppService $app_service)
    {
        // $this->middleware('isStudent');
        // $this->boarding_fee =  BoardingFee::first();
        //  $this->year = Batch::find(Helpers::instance()->getCurrentAccademicYear())->name;
        $this->batch_id = Batch::find(Helpers::instance()->getCurrentAccademicYear())->id;
        $this->years = Batch::all();
        $this->api_service = $service;
        $this->app_service = $app_service;
    }


    
    public function edit_profile()
    {
        # code...
        $data['title'] = "Edit Profile";
        return view('student.edit_profile', $data);
    }
    public function update_profile(Request $request)
    {
        # code...
        if(
            Students::where([
                'email' => $request->email??null, 'phone' => $request->phone
            ])->count() > 0 && (auth('student')->user()->phone != $request->phone || auth('student')->user()->email != $request->email)
        ){
            return back()->with('error', __('text.validation_phrase1'));
        }
        
        $data = $request->all();
        Students::find(auth('student')->id())->update($data);
        return redirect(route('student.home'))->with('success', __('text.word_Done'));
    }
 

    /* ______________________________________________________________________________________
    ONLINE APPLICATION SPECIFIC ACTIONS
    _______________________________________________________________________________________ */
    public function all_programs (Request $request)
    {
        # code...
        $data['title'] = "Our programs";
        $degrees = collect(json_decode($this->api_service->degrees())->data);
        $data['degrees'] = $degrees;
        $data['programs'] = collect(json_decode($this->api_service->programs())->data)
            ->each(function($rec)use($degrees){
                $rec->degree = $degrees->where('id', $rec->degree_id)->first()->deg_name??'OTHERS';
            })->groupBy('degree');
        // return $data;
        // dd($data);
        return view('student.online.programs', $data);
    }

    public function start_application (Request $request, $step, $application_id = null)
    {
        try {

            if(auth('student')->user()->applicationForms()->whereNotNull('transaction_id')->where('submitted', true)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->count() > 0){
                return redirect(route('student.home'))->with('error', "You are allowed to submit only one application form per year");
            }

            // check if application is open now
            if(!(Helpers::instance()->application_open())){
                return redirect(route('student.home'))->with('error', 'Application closed for '.Batch::find(Config::all()->last()->year_id)->name);
            }
            # code...
            $data['step'] = $step;
            // return $this->api_service->campuses();
            $application = ApplicationForm::where(['student_id'=>auth('student')->id(), 'year_id'=>Helpers::instance()->getCurrentAccademicYear()])->first();
            if($application == null){
                $application = new ApplicationForm();
                $application->student_id = auth('student')->id();
                $application->year_id = Helpers::instance()->getCurrentAccademicYear();
                // $application->id = 5;
                $application->save();
            }
            // $application->update(['campus_id'=>1]);
            // if($application->degree_id != null and ($application->tranzak_transaction == null || $application->tranzak_transaction->payment_id != $application->degree_id) and $step != 0 ){
            //     $data['step'] = 6;
            // }else
            if($application->degree_id != null and ($application->tranzak_transaction != null and $application->tranzak_transaction->payment_id == $application->degree_id) and $step == 6){
                return redirect()->route('student.home')->with('error', "Payment has been made for this application instance");
            }
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
            $data['title'] = (isset($data['degree']) and ($data['degree'] != null)) ? $data['degree']->deg_name." APPLICATION" : "APPLICATION";
            // dd($data);
            return view('student.online.fill_form', $data);
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('error', $th->getMessage());
        }
    }

    public function persist_application(Request $request, $step, $application_id)
    {
        # code...
        // return $request->all();
        
        // check if application is open now
        if(!(Helpers::instance()->application_open())){
            return redirect(route('student.home'))->with('error', 'Application closed for '.Batch::find(Config::all()->last()->year_id)->name);
        }
        switch ($step) {
            case 1:
                # code...
                $validity = Validator::make($request->all(), [
                    'degree_id'=>'required'
                ]);
                break;
            
            case 2:
                # code...
                // return $request->all();
                $validity = Validator::make($request->all(), [
                    "name"=>'required', "dob"=>'required|date', "pob"=>'required',
                    "gender"=>'required', "id_card_number"=>'required', 
                    "id_date_of_issue"=>'required|date', "id_place_of_issue"=>'required', 
                    "nationality"=>'required', "region"=>'required', 
                    "country_of_birth"=>'required', "referer"=>'required'
                ]);
                break;
            
            case 3:
                # code...
                $validity = Validator::make($request->all(), [
                    "residence"=>'required', "phone"=>'required',  "guardian"=>'required', "guardian_phone"=>'required', 
                    "guardian_address"=>'required', "sponsor"=>'required', "sponsor_phone"=>'required', "sponsor_address"=>'required'
                ]);
                break;
            
            case 4:
                # code...
                
                $validity = Validator::make($request->all(), [
                    // "high_school"=>'string', 
                    // "high_school_exam_center"=>,
                    // "high_school_candidate_number"=>'required', 
                    // "high_school_exam_year"=>'required', 
                    // "gce_al_record"=>'required'
                    "secondary_school"=>'nullable', "secondary_exam_center"=>'nullable', "secondary_candidate_number"=>'nullable', "secondary_exam_year"=>'nullable', "gce_ol_record"=>'array',
                    'previous_training'=>'array', 'employments'=>'array'
                ]);
                break;
                
            case 5:
                # code...
                $validity = Validator::make($request->all(), [
                    'program'=>'required'
                ]);
                break;
            case 6:
                # code...
                $validity = Validator::make($request->all(), []);
                break;

            case 7:
                $validity = Validator::make($request->all(), ['momo_number'=>'required', 'amount'=>'required']);
                # code...
                break;
            
        }

        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }
        // return $request->all();
        $application = \App\Models\ApplicationForm::find($application_id);
        if($application->degree_id != null and ($application->tranzak_transaction != null and $application->tranzak_transaction->payment_id == $application->degree_id) and $step == 7){
            return redirect()->route('student.home')->with('error', "Payment has been made for this application instance");
        }

        // persist data
        $data = $request->all();
        if($step == 4){
            // dd($request->collect());

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
            $data = collect($data)->filter(function($value, $key){return $key != '_token';})->toArray();
            $application = ApplicationForm::updateOrInsert(['id'=> $application_id, 'student_id'=>auth('student')->id()], $data);
        }elseif($step == 7){
            // dd($request->all());
            $tk_counter = 0;
            $application = auth('student')->user()->applicationForms()->where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
            if($application->degree_id == null){ goto SKIP;}
            $tranzak_credentials = TranzakCredential::where('campus_id', $application->campus_id)->first();
            if(cache($tranzak_credentials->cache_token_key) == null or Carbon::parse(cache($tranzak_credentials->cache_token_expiry_key))->isAfter(now())){
                // get and cache different token
                // dd($request->all());
                REQUEST_TOKEN:
                $tk_counter++;
                $response = Http::post(config('tranzak.base').config('tranzak.token'), ['appId'=>$tranzak_credentials->app_id, 'appKey'=>$tranzak_credentials->api_key]);
                if($response->status() == 200){
                    // return json_decode($response->body())->data;
                    // return Carbon::createFromTimestamp(time() + json_decode($response->body())->data->expiresIn);
                    // cache token and token expiration to session
                    cache([$tranzak_credentials->cache_token_key => json_decode($response->body())->data->token]);
                    cache([$tranzak_credentials->cache_token_expiry_key=>Carbon::createFromTimestamp(time() + json_decode($response->body())->data->expiresIn)]);
                }
            }
            $headers = ['Authorization'=>'Bearer '.cache($tranzak_credentials->cache_token_key)];
            // dd($headers);
            if($request->channel == 'bank'){
                // $return_url = "192.168.2.196/NISHANG/ssp2_univ_apl_port/api/tranzak/web_redirect/return_callback";
                // $request_data = ['mchTransactionRef'=>'_apl_fee_'.time().'_'.random_int(1, 9999), "amount"=> $request->amount, "currencyCode"=> "XAF", "description"=>"Payment for application fee into HIMS UNIVERSITY INSTITUTE", 'returnUrl'=>$return_url, 'cancelUrl'=>$return_url];
                $request_data = ['mchTransactionRef'=>'_apl_fee_'.time().'_'.random_int(1, 9999), "amount"=> $request->amount, "currencyCode"=> "XAF", "description"=>"Payment for application fee into HIMS UNIVERSITY INSTITUTE", 'returnUrl'=>route('tranzak.return_url'), 'cancelUrl'=>route('tranzak.return_url')];
                $_response = Http::withHeaders($headers)->post(config('tranzak.base').config('tranzak.web_redirect_payment'), $request_data);
                if($_response->status() == 200){
                    \Illuminate\Support\Facades\Log::info("_____________REQUEST_TO_PAY___".json_encode($_response->collect()->toArray())."______________.");
                    
                    session()->put('processing_tranzak_transaction_details', json_encode(json_decode($_response->body())->data));
                    session()->put('tranzak_credentials', json_encode($tranzak_credentials));
                    $applxn = ApplicationForm::find($application_id);
                    $data = ['student_id'=>auth('student')->id(), 'form_id'=>$application_id, 'requestId'=>$_response->collect()['data']['requestId'], 'payment_id'=>$applxn->degree_id??null, 'year_id'=>$applxn->year_id, 'campus_id'=>$applxn->campus_id, 'purpose'=>'APPLICATION', 'transaction'=>json_encode($_response->collect()['data'])];
                    \App\Models\PendingTranzakTransaction::create($data);
                    $payment_url = $_response->collect()['data']['links']['paymentAuthUrl'];
                    return redirect()->to(route('student.application.payment.processing', $application_id)."?payment_url=".$payment_url);
                }
            }else{
                $request_data = ['mobileWalletNumber'=>str_replace('+', '', strlen($request->momo_number) == 9 ? '237'.$request->momo_number : $request->momo_number), 'mchTransactionRef'=>'_apl_fee_'.time().'_'.random_int(1, 9999), "amount"=> $request->amount, "currencyCode"=> "XAF", "description"=>"Payment for application fee into HIMS"];
                $_response = Http::withHeaders($headers)->post(config('tranzak.base').config('tranzak.direct_payment_request'), $request_data);
                // dd($_response->collect());
                if($_response->status() == 200){
                    
                    // $_data = $_response->collect();
                    // dd($_data);
                    session()->put('processing_tranzak_transaction_details', json_encode(json_decode($_response->body())->data));
                    session()->put('tranzak_credentials', json_encode($tranzak_credentials));
                    // create pending transaction
                    $applxn = ApplicationForm::find($application_id);
                    $data = [
                        'student_id'=>auth('student')->id(), 
                        'form_id'=>$application_id, 
                        'requestId'=>$_response->collect()['data']['requestId'], 
                        'payment_id'=>$applxn->degree_id??null, 
                        'year_id'=>$applxn->year_id, 
                        'campus_id'=>$applxn->campus_id, 
                        'purpose'=>'APPLICATION', 
                        'transaction'=>json_encode($_response->collect()['data'])
                    ];
                    \App\Models\PendingTranzakTransaction::create($data);
                    return redirect()->to(route('student.application.payment.processing', $application_id));
                }
                // dd($_response->collect());
                if(count($_response->collect()['data']) == 0 and $tk_counter == 0){
                    goto REQUEST_TOKEN;
                }
            }

        }else{
            // $data = $request->all();
            if($request->program != null){
                $levels = collect(json_decode($this->api_service->campusProgramLevels($application->campus_id, $request->program))->data);
                dd($levels);
                $data['level'] = $levels->first()?->level??'';
            }
            $data = collect($data)->filter(function($value, $key){return $key != '_token';})->toArray();
            $application = ApplicationForm::updateOrInsert(['id'=> $application_id, 'student_id'=>auth('student')->id()], $data);
        }
        
        SKIP:

        $step = $request->step;

        $application = \App\Models\ApplicationForm::find($application_id);
        if($step == 6){
            if($application->degree_id != null and ($application->tranzak_transaction != null and $application->tranzak_transaction->payment_id == $application->degree_id)){
                $application->update(['submitted'=>true]);
                $batch = Batch::find(Helpers::instance()->getCurrentAccademicYear())->name;
                $school_name = \App\Models\School::first()->name??'';
                $message = "Hello ".(auth('student')->user()->name??'').", You have successfully submitted application into ".$school_name." for the ".$batch." academic year. Your application is under processing.";
                $this->sendSmsNotificaition($message, [auth('student')->user()->phone]);
                
                return redirect(route("student.home"))->with('success', "Application completed successfully");
            }
        }
        
        return redirect(route('student.application.start', [$step, $application_id]));
    }

    public function pending_payment(Request $request, $application_id)
    {
        # code...
        // dd(123);
        // check if application is open now
        if(!(Helpers::instance()->application_open())){
            return redirect(route('student.home'))->with('error', 'Application closed for '.Helpers::instance()->getYear()->name);
        }
        $data['title'] = "Processing Transaction";
        $data['form_id'] = $application_id;
        $data['tranzak_credentials'] = json_decode(session()->get('tranzak_credentials'));
        $data['transaction'] = json_decode(session()->get('processing_tranzak_transaction_details'));
        // return $data;
        return view('student.online.processing_payment', $data);
        
    }

    public function pending_complete(Request $request, $appl_id)
    {
        # code...
        try {
            // dd(123);
            // check if application is open now
            if(!(Helpers::instance()->application_open())){
                return redirect(route('student.home'))->with('error', 'Application closed for '.Helpers::instance()->getYear()->name);
            }
            //code...
            $transaction_status = (object) $request->all();
            // return $transaction_status;
            switch ($transaction_status->status) {
                case 'SUCCESSFUL':
                    # code...
                    // save transaction and update application_form
                    $pending = \App\Models\PendingTranzakTransaction::where('requestId', $transaction_status->requestId)->first();
                    
                    $transaction = [
                        'request_id'=>$request->requestId??'', 'payment_id'=>$pending->payment_id, 
                        'amount'=>$request->amount??'', 'currency_code'=>$request->currencyCode??'', 
                        'purpose'=>$request->payment_purpose??'APPLICATION', 'mobile_wallet_number'=>$request->mobileWalletNumber??'', 
                        'transaction_ref'=>$request->mchTransactionRef??'', 'app_id'=>$request->appId??'', 
                        'transaction_time'=>$request->transactionTime??'', 
                        'payment_method'=>$request->payer['paymentMethod']??'', 
                        'payer_user_id'=>$request->payer['userId']??'', 
                        'payer_name'=>$request->payer['name']??'', 
                        'payer_account_id'=>$request->payer['accountId']??'', 
                        'merchant_fee'=>$request->merchant['fee']??'', 
                        'merchant_account_id'=>$request->merchant['accountId']??'', 
                        'net_amount_recieved'=>$request->merchant['netAmountReceived']??''
                    ];
                    
                    \App\Models\TranzakTransaction::insert($transaction);
                    $transaction_instance =  \App\Models\TranzakTransaction::where($transaction)->first();
    
                    $appl = ApplicationForm::find($appl_id);
                    $appl->transaction_id = $transaction_instance->id;
                    $appl->update(['submitted'=>true, 'transaction_id'=>$transaction_instance->id]);
                    $batch = Batch::find(Helpers::instance()->getCurrentAccademicYear())->name;
                    $school_name = \App\Models\School::first()->name??'';
                    $message = "Hello ".(auth('student')->user()->name??'').", You have successfully submitted application into ".$school_name." for the ".$batch." academic year. Your application is under processing.";
                    $this->sendSmsNotificaition($message, [auth('student')->user()->phone]);
                    
                    return redirect(route("student.home"))->with('success', "Application completed successfully");
    
                    break;
                
                case 'CANCELLED':
                    # code...
                    // notify user
                    return redirect(route('student.home'))->with('message', 'Payment Not Made. The request was cancelled.');
                    break;
                
                case 'FAILED':
                    # code...
                    return redirect(route('student.home'))->with('error', 'Payment failed.');
                    break;
                
                case 'REVERSED':
                    # code...
                    return redirect(route('student.home'))->with('message', 'Payment failed. The request was reversed.');
                    break;
                
                default:
                    # code...
                    break;
            }
            return redirect(route('student.home'))->with('error', 'Payment failed. Unrecognised transaction status.');
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('error', $th->getMessage());
        }
    }

    public function submit_application(Request $request){
        
        // check if application is open now
        if(!(Helpers::instance()->application_open())){
            return redirect(route('student.home'))->with('error', 'Application closed for '.Batch::find(Helpers::instance()->getYear())->name);
        }
        $applications = auth('student')->user()->currentApplicationForms()->where('submitted', 0)->get();
        $data['title'] = "Submit Application";
        $data['applications'] = $applications;
        return view('student.online.submit_form', $data);
    }

    public function submit_application_save(Request $request, $appl_id)
    {
        # code...
        $application = ApplicationForm::find($appl_id);
        if($application != null){
            $application->submitted = 1;
            $application->save();
            return back()->with('success', 'Application submitted.');
        }
        return back()->with('error', 'Application could not be found.');
    }

    public function download_application_form()
    {
        # code...
        $data['title'] = "Download Application Form";
        $data['_this'] = $this;
        $data['applications'] = auth('student')->user()->applicationForms->whereNotNull('transaction_id')->where('submitted', true);
        $data['programs'] = collect(json_decode($this->api_service->programs())->data);
        return view('student.online.download_form', $data);
    }

    public function download_form(Request $request, $application_id)
    {
        # code...
        try{
            $application = ApplicationForm::find($application_id);
            return $this->app_service->application_form($application_id);
            // $data['application'] = $application;
            
            // $title = "APPLICATION FORM FOR ".$application->degree->name;
            // $data['title'] = $title;

            // // if(in_array(null, array_values($data))){ return redirect(route('student.application.start', [0, $application_id]))->with('message', "Make sure your form is correctly filled and try again.");}
            // // return view('student.online.form_dawnloadable', $data);
            // $pdf = PDF::loadView('student.online.form_dawnloadable', $data);
            // $filename = $title.' - '.$application->name.'.pdf';
            // return $pdf->download($filename);
        }catch(Throwable $th){
            throw $th;
            // if(in_array(null, array_values($data))){ return redirect(route('student.application.start', [0, $application_id]))->with('message', "Make sure your form is correctly filled and try again.");}
        }
    }

    
    //---------
    public function pay_platform_charges(Request $request)
    {
        # code...
        $student = auth('student')->user();
        $charge = PlatformCharge::where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
        if($charge == null || $charge->yearly_amount == null || $charge->yearly_amount == 0){return back()->with('error', 'Platform charges not set.');}
        if($student->hasPaidPlatformCharges($request->year_id)){return redirect(route('student.home'))->with('message', 'Platform charges already paid for this year.');}
        $data['title'] = "Pay Platform Charges";
        $data['amount'] = $charge->yearly_amount;
        $data['purpose'] = 'PLATFORM';
        $data['year_id'] = $request->year_id ?? null;
        $data['payment_id'] = $charge->id;
        return view('student.platform.charges', $data);
    }

    //---------
    public function pay_charges_save(Request $request)
    {
        # code...
        // dd(153543);
        $validator = Validator::make($request->all(),
        [
            'tel'=>'required|numeric|min:9',
            'amount'=>'required|numeric',
            // 'callback_url'=>'required|url',
            'student_id'=>'required|numeric',
            'year_id'=>'required|numeric',
            'payment_purpose'=>'required',
            'payment_id'=>'required|numeric'
        ]);
        try {
            //code...
            if ($validator->fails()) {
                # code...
                return back()->with('error', $validator->errors()->first());
            }
            // return $request->all();
    
            // BRIDGE PROCESS BY PAYING WITH TRANZAK
            {
                $data = $request->all();
                $data_key = $request->payment_purpose == '_TRANSCRIPT_' ? config('tranzak._transcript_data') : config('tranzak.platform_data');
                session()->put($data_key, $data);
                // dd($data);
                return $this->tranzak_pay($request->payment_purpose, $request);
            }
    
            try {
                //code...
                $data = $request->all();
                $response = Http::post(env('CHARGES_PAYMENT_URL'), $data);
                // dd($response->body());
                if(!$response->ok()){
                    // throw $response;
                    return back()->with('error', 'Operation failed. '.$response->body());
                    // dd($response->body());
                }
                
                if($response->ok()){
                
                    $_data['title'] = "Pending Confirmation";
                    $_data['transaction_id'] = $response->collect()->first();
                    // return $_data;
                    return view('student.platform.payment_waiter', $_data);
                }
            } 
            catch(ConnectException $e){
                return back()->with('error', $e->getMessage());
            }
        } catch (\Throwable $th) {
            // throw $th;
            session()->flash('error', "F::{$th->getFile()}, L::{$th->getLine()}, M::{$th->getMessage()}");
            return back();
        }
        
    }

    //----------
    public function complete_charges_transaction(Request $request, $ts_id)
    {
        # code...

        $transaction = Transaction::where(['transaction_id'=>$ts_id])->first();
        if($transaction != null){
            // update transaction
            $transaction->status = "SUCCESSFUL";
            $transaction->is_charges = true;
            $transaction->financialTransactionId = $request->financialTransactionId;
            $transaction->save();
            // return $transaction;
            // update payment record
            // CHECK PAYMENT PURPOSE, EITHER 
            switch($transaction->payment_purpose){
                case 'PLATFORM':
                case 'RESULTS':
                    $charge = new Charge();
                    $data = [
                        "student_id"=>$transaction->student_id,
                        "year_id"=>$transaction->year_id,
                        'semester_id'=>$transaction->semester_id??0,
                        'type'=>$transaction->payment_purpose,
                        "item_id"=>$transaction->payment_id,
                        "amount"=>$transaction->amount,
                        "financialTransactionId"=>$request->financialTransactionId,
                    ];
                    $charge->fill($data);
                    $charge->save();
                    return redirect($transaction->payment_purpose == 'PLATFORM' ? route('student.transcript.apply') : route('student.result.exam'))->with('success', 'Payment complete');
                    break;

                case 'TRANSCRIPT':
                    // set used to 0 on transactions to indicate that the transcript associated to the transaction is not yet done.


                    $charge = new Charge();
                    $data = [
                        "student_id"=>$transaction->student_id,
                        "year_id"=>$transaction->year_id,
                        'semester_id'=>$transaction->semester_id ?? null,
                        'type'=>$transaction->payment_purpose,
                        "item_id"=>$transaction->payment_id,
                        "amount"=>$transaction->amount,
                        "financialTransactionId"=>$request->financialTransactionId,
                        'used'=>false
                    ];
                    $charge->fill($data);
                    $charge->save();
                    $_data['title'] = "Apply For Transcript";
                    $_data['charge_id'] = $charge->id;
                    return view('student.transcript.apply', $_data)->with('success', 'Payment complete');
                    break;

            }
        }
    }

    //-----------
    public function failed_charges_transaction(Request $request, $ts_id)
    {
        # code...
        $transaction = Transaction::where(['transaction_id'=>$ts_id])->first();
        if($transaction != null){
            // update transaction
            $transaction->status = "FAILED";
            $transaction->financialTransactionId = $request->financialTransactionId;
            $transaction->is_charges = 'true';
            $transaction->save();
            switch($transaction->payment_purpose){
                case 'TRANSCRIPT':
                case 'RESULTS':
                case 'PLATFORM':
                    // DB::table('transcripts')->where(['student_id'=>auth('student')->id(), 'paid'=>0])->delete();
                    return redirect(route('student.home'))->with('error', 'Operation Failed');
                    break;
            }

            // redirect user
            return redirect(route('student.home'))->with('error', 'Operation failed.');
        }
    }

    
    //--------------    
    public function tranzak_pay(string $purpose, Request $request){

        $validator = Validator::make($request->all(),
        [
            'tel'=>'required|numeric|min:9',
            'amount'=>'required|numeric',
            // 'callback_url'=>'required|url',
            'student_id'=>'required|numeric',
            'year_id'=>'required|numeric',
            'payment_purpose'=>'required',
            'payment_id'=>'required|numeric'
        ]);
        
        
        // MAKE API CALL TO PERFORM PAYMENT OF APPLICATION FEE
        // check if token exist and hasn't expired or get new token otherwise
        $application = auth('student')->user()->applicationForms()->where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
        $tranzak_credentials = \App\Models\TranzakCredential::where('campus_id', 0)->first();
        if(cache($tranzak_credentials->cache_token_key) == null or Carbon::parse(cache($tranzak_credentials->cache_token_expiry_key))->isAfter(now())){
            GEN_TOKEN:
            $response = Http::post(config('tranzak.base').config('tranzak.token'), ['appId'=>$tranzak_credentials->app_id, 'appKey'=>$tranzak_credentials->api_key]);
            if($response->status() == 200){
                cache([$tranzak_credentials->cache_token_key => json_decode($response->body())->data->token]);
                cache([$tranzak_credentials->cache_token_expiry_key=>Carbon::createFromTimestamp(time() + json_decode($response->body())->data->expiresIn)]);
            }
        }

        $tel = strlen($request->tel) >= 12 ? $request->tel : '237'.$request->tel;
        $headers = ['Authorization'=>'Bearer '.cache($tranzak_credentials->cache_token_key)];
        $request_data = ['mobileWalletNumber'=>$tel, 'mchTransactionRef'=>'_'.str_replace(' ', '_', $request->payment_purpose).'_payment_'.time().'_'.random_int(1, 9999), "amount"=> $request->amount, "currencyCode"=> "XAF", "description"=>"Payment for {$request->payment_purpose} - HIMS UNIVERSITY INSTITUTE OF BUEA."];
        $_response = Http::withHeaders($headers)->post(config('tranzak.base').config('tranzak.direct_payment_request'), $request_data);
        // dd($_response->collect());
        if($_response->collect()['success'] == true){

            // create pending transaction
            $resp_data = $_response->collect()['data'];
            
            $data = [
                'student_id'=>auth('student')->id(), 'requestId'=>$resp_data['requestId'], 'payment_id'=>$request->payment_id??null, 
                'year_id'=>$request->year_id, 'purpose'=>$request->payment_purpose, 'transaction'=>json_encode($resp_data)
            ];

            $pt_instance = new \App\Models\PendingTranzakTransaction($data);
            $pt_instance->save();

            session()->put('processing_tranzak_transaction_details', json_encode($_response->collect()['data']));
            session()->put('tranzak_credentials', json_encode($tranzak_credentials));
            return redirect()->to(route('student.tranzak.processing', $request->payment_purpose));
        }else {
            goto GEN_TOKEN;
        }
        return back()->with('error', 'Unknown error occured');

    }

    //-----------------
    public function tranzak_payment_processing()
    {
        # code...
        $data['title'] = "Processing Payment Request";
        $data['tranzak_credentials'] = TranzakCredential::where('campus_id', 0)->first();
        $data['transaction'] = json_decode(session('processing_tranzak_transaction_details'));
        // dd(1573);
        return view('student.momo.processing', $data);
    }

    //----------------
    public function tranzak_complete(Request $request)
    {
        # code...
        try {
            //code...
            // return $request;
            // dd(session()->get('processing_tranzak_transaction_details'));
            // dd($request->all());
            switch ($request->status) {
                case 'SUCCESSFUL':
                    # code...
                    // save transaction and update application_form
                    DB::beginTransaction();
                    $pending = \App\Models\PendingTranzakTransaction::where('requestId', $request->requestId)->first();
                    $transaction = ['request_id'=>$request->requestId??'', 'payment_id'=>$pending->payment_id, 'amount'=>$request->amount??'', 'currency_code'=>$request->currencyCode??'', 'purpose'=>$request->payment_purpose??'', 'mobile_wallet_number'=>$request->mobileWalletNumber??'', 'transaction_ref'=>$request->mchTransactionRef??'', 'app_id'=>$request->appId??'', 'transaction_id'=>$request->transactionId??'', 'transaction_time'=>$request->transactionTime??'', 'payment_method'=>$request->payer['paymentMethod']??'', 'payer_user_id'=>$request->payer['userId']??'', 'payer_name'=>$request->payer['name']??'', 'payer_account_id'=>$request->payer['accountId']??'', 'merchant_fee'=>$request->merchant['fee']??'', 'merchant_account_id'=>$request->merchant['accountId']??'', 'net_amount_recieved'=>$request->merchant['netAmountReceived']??''];
                    if(\App\Models\TranzakTransaction::where($transaction)->count() == 0){
                        $transaction_instance = new \App\Models\TranzakTransaction($transaction);
                        $transaction_instance->save();
                    }else{
                        $transaction_instance = \App\Models\TranzakTransaction::where($transaction)->first();
                    }
                        
                    $data = ['student_id'=>$pending->student_id, 'year_id'=>$pending->year_id, 'type'=>$pending->purpose, 'item_id'=>$pending->payment_id, 'amount'=>$transaction_instance->amount, 'financialTransactionId'=>$transaction_instance->transaction_id, 'used'=>1];
                    $instance = new \App\Models\Charge($data);
                    $instance->save();
                    $message = "Hello ".(auth('student')->user()->name??'').", You have successfully paid a sum of ".($transaction_instance->amount??'')." as ".($pending->purpose??'')." for ".($transaction_instance->year->name??'')." HIMS.";
                    $this->sendSmsNotificaition($message, [auth('student')->user()->phone]);

                    ($pending = \App\Models\PendingTranzakTransaction::where('requestId', $request->requestId)->first()) != null ? $pending->delete() : null;
                    DB::commit();
             
                    return redirect(route('student.application.start', ['step'=>0]))->with('success', "Payment successful.");
                    break;
                
                case 'CANCELLED':
                    # code...
                    // notify user
                    ($pending = \App\Models\PendingTranzakTransaction::where('requestId', $request->requestId)->first()) != null ? $pending->delete() : null;
                    return redirect(route('student.home'))->with('message', 'Payment Not Made. The request was cancelled.');
                    break;
                
                case 'FAILED':
                    # code...
                    ($pending = \App\Models\PendingTranzakTransaction::where('requestId', $request->requestId)->first()) != null ? $pending->delete() : null;
                    return redirect(route('student.home'))->with('error', 'Payment failed.');
                    break;
                
                case 'REVERSED':
                    # code...
                    ($pending = \App\Models\PendingTranzakTransaction::where('requestId', $request->requestId)->first()) != null ? $pending->delete() : null;
                    return redirect(route('student.home'))->with('message', 'Payment failed. The request was reversed.');
                    break;
                
                default:
                    # code...
                    break;
            }

            return redirect(route('student.home'))->with('error', 'Payment failed. Unrecognised transaction status.');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            session()->flash('error', "F::{$th->getFile()}, L::{$th->getLine()}, M::{$th->getMessage()}");
            return back();
        }
    }

    
    public function download_admission_letter()
    {
        # code...
        $data['title'] = "Download Admission Letter";
        $data['_this'] = $this;
        $data['applications'] = auth('student')->user()->applicationForms->where('admitted', 1);
        // $data['applications'] = ApplicationForm::whereNotNull('matric')->get();
        // return $data;
        return view('student.online.admission_letter', $data);
    }

    public function download_admission_letter_save(Request $request, $appl_id)
    {
        return $this->app_service->admission_letter($appl_id);
    }

    public function payment_data(Request $request){
        $data['title'] = "Transaction History";
        $charges = auth('student')->user()->platform_transactions()->get();
        $data['payments'] = auth('student')->user()->transactions()->get()->merge($charges);
        $data['programs'] = collect(json_decode($this->api_service->programs())->data);
        return view('student.online.payment_data', $data);
    }


}
