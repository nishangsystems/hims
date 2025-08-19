<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ResultsAndTranscriptsController;
use App\Http\Controllers\admin\StockController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\CustomForgotPasswordController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\documentation\BaseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Student\HomeController as StudentHomeController;
use App\Http\Controllers\Teacher\HomeController as TeacherHomeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Transactions;
use App\Http\Resources\SubjectResource;
use App\Http\Services\MailService;
use App\Models\Program;
use App\Models\Resit;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use \App\Models\Subjects;

Route::get('/clear', function () {
    echo Session::get('applocale');
    $clearcache = Artisan::call('cache:clear');
    echo "Cache cleared<br>";

    $clearview = Artisan::call('view:clear');
    echo "View cleared<br>";

    $clearconfig = Artisan::call('config:cache');
    echo "Config cleared<br>";

});

// test mail sender
Route::get('send_sms', [Controller::class, 'sendSMS']/*function(){
    // $mailer = new MailService();
    // $subject = "Form Submission Notification";
    // $text = "Your application form has been submitted successfully";
    // $data = ['name'=>"GERMANUS K", 'email'=>"germanuskeming@gmail.com"];
    // if(@mail($data['email'], $subject, $text)){
    //     return "success";
    // }else{return "failed";}
    // $mailer->sendPlainMail($subject, $text, $data);

    // $basic  = new \Vonage\Client\Credentials\Basic("8d8bbcf8", "04MLvso1he1b8ANc");
    // $client = new \Vonage\Client($basic);

    // $response = $client->sms()->send(
    //     new \Vonage\SMS\Message\SMS("237699131895", '+237672908239', 'A text message sent using the Nexmo SMS API')
    // );
    
    // $message = $response->current();
    
    // if ($message->getStatus() == 0) {
    //     echo "The message was sent successfully\n";
    // } else {
    //     echo "The message failed with status: " . $message->getStatus() . "\n";
    // }
}*/);

Route::get('store_courses', function(){
    return view('store_courses');
});
Route::post('store_courses', function(Request $request){
    $request->validate(['file'=>'required|file|mimes:csv']);
    $file = $request->file('file');
    $filename = '__'.time().'__'.random_int(100000, 999999).'.'.$file->getClientOriginalExtension();
    $file->move(public_path('uploads'), $filename);

    $subjects = [];
    $file_reader = fopen(public_path('uploads').'/'.$filename, 'r');
    $first = true;
    while (($subject = fgetcsv($file_reader, 100, ',')) != null) {
        # code...
        if($first){$first = false; continue;}
        $subjects[] = ['name'=>$subject[1]];
    }
    Subject::insert($subjects);
    return $subjects;
});

Route::get('set_local/{lang}', [Controller::class, 'set_local'])->name('lang.switch');

Route::get('payment-form',[TransactionController::class,'paymentForm'])->name('payment_form');
Route::post('make-payments',[TransactionController::class,'makePayments'])->name('make_payments');
Route::get('complete-transaction/{transaction_id}',[StudentHomeController::class,'complete_transaction'])->name('complete_transaction');
Route::get('failed-transaction/{transaction_id}',[StudentHomeController::class,'failed_transaction'])->name('failed_transaction');
// Route::get('get-transaction-status/{transaction_id}',[TransactionController::class,'getTransactionStatus'])->name('get_transaction_status');
Route::post('mtn-momo',[TransactionController::class,'mtnCallBack'])->name('mtn_callback');

Route::post('login', [CustomLoginController::class, 'login'])->name('login.submit');
Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::get('registration', [Controller::class, 'registration'])->name('registration');
Route::post('check_matricule', [Controller::class, 'check_matricule'])->name('check_matricule');
Route::post('createAccount', [Controller::class, 'createAccount'])->name('createAccount');
Route::post('logout', [CustomLoginController::class, 'logout'])->name('logout');

Route::post('reset_password_with_token/password/reset', [CustomForgotPasswordController::class, 'validatePasswordRequest'])->name('reset_password_without_token');
Route::get('reset_password_with_token/{token}/{email}', [CustomForgotPasswordController::class, 'resetForm'])->name('reset');
Route::post('reset_password_with_token', [CustomForgotPasswordController::class, 'resetPassword'])->name('reset_password_with_token');
Route::post('recover_username', [CustomForgotPasswordController::class, 'recover_username'])->name('recover_username');

Route::get('', 'WelcomeController@home');
Route::get('home', 'WelcomeController@home');
// Route::middleware('password_reset')->group(function(){
    // });


Route::get('_api/root/create', [Controller::class, 'create_api_root'])->name('api.create_root');
Route::post('_api/root/create', [Controller::class, 'save_api_root']);


Route::prefix('admin')->name('admin.')->middleware('isAdmin')->group(function () {

    Route::get('', 'Admin\HomeController@index')->name('home');
    Route::get('home', 'Admin\HomeController@index')->name('home');
    Route::get('set_watermark', 'Admin\HomeController@set_watermark')->name('set_watermark');
    Route::post('set_watermark', 'Admin\HomeController@save_watermark');
    Route::get('setayear', 'Admin\HomeController@setayear')->name('setayear');
    Route::post('setayear/{id}', 'Admin\HomeController@setAcademicYear')->name('createacademicyear');
    Route::get('units/{parent_id}/student', 'Admin\ProgramController@students')->name('students.index');

    Route::get('student_list/select/{filter?}', 'Admin\ProgramController@program_levels_list_index')->name('student.bulk.index');
    Route::get('student_list/bulk/{filter}/{item_id}/{year_id?}', 'Admin\ProgramController@bulk_program_levels_list')->name('student.bulk.list');
    Route::get('messages/bulk/{filter}/{item_id}/{recipients}/{year_id?}', 'Admin\ProgramController@bulk_message_notifications')->name('messages.bulk');
    Route::post('messages/bulk/{filter}/{item_id}/{recipients}/{year_id?}', 'Admin\ProgramController@bulk_message_notifications_save')->name('messages.bulk');
    Route::get('programs/{id}/levels', 'Admin\ProgramController@program_levels')->name('programs.levels');
    Route::get('programs/index', 'Admin\ProgramController@program_index')->name('programs.index');
    
    Route::get('search/students/{name}', 'Admin\PayIncomeController@searchStudent')->name('searchStudent');
    Route::get('search/students/', 'Admin\PayIncomeController@get_searchStudent')->name('get_searchStudent');
    Route::get('search/users/', 'Admin\PayIncomeController@get_searchUser')->name('get_searchUser');

    Route::get('users/search', [Controller::class, 'search_user'])->name('users.search');
    
    Route::resource('users', 'Admin\UserController');
    Route::get('student/matricule', 'Admin\StudentController@matric')->name('students.matricule');
    Route::post('student/matricule', 'Admin\StudentController@matricPost')->name('students.matricule');
    Route::post('student/{id}/password/reset', 'Admin\StudentController@reset_password')->name('student.password.reset');
    Route::resource('student', 'Admin\StudentController');
    Route::post('students', 'Admin\StudentController@getStudentsPerClass')->name('getStudent.perClassYear');
    

    Route::get('sub-units/{parent_id}','Admin\ProgramController@getSubUnits')->name('getSubUnits');

    Route::resource('roles','Admin\RolesController');
    Route::get('permissions', 'Admin\RolesController@permissions')->name('roles.permissions');
    Route::get('assign_role', 'Admin\RolesController@rolesView')->name('roles.assign');
    Route::post('assign_role', 'Admin\RolesController@rolesStore')->name('roles.assign.post');
    Route::post('roles/destroy/{id}', 'Admin\RolesController@destroy')->name('roles.destroy');

    Route::prefix('statistics')->name('stats.')->group(function(){
        Route::get('sudents', 'Admin\StatisticsController@students')->name('students');
        Route::get('fees', 'Admin\StatisticsController@fees')->name('fees');
        Route::get('results', 'Admin\StatisticsController@results')->name('results');
        Route::get('income', 'Admin\StatisticsController@income')->name('income');
        Route::get('expenditure', 'Admin\StatisticsController@expenditure')->name('expenditure');
        Route::get('fees/{class_id}', 'Admin\StatisticsController@unitFees')->name('unit-fees');
        Route::get('ie_report', 'Admin\StatisticsController@ie_report')->name('ie_report');
        Route::get('ie_report/monthly', 'Admin\StatisticsController@ie_monthly_report')->name('ie.report');
    });
    
    Route::get('set_letter_head', [AdminHomeController::class, 'set_letter_head'])->name('set_letter_head');
    Route::post('set_letter_head/save', [AdminHomeController::class, 'save_letter_head'])->name('save_letter_head');

    Route::get('students', [StudentController::class, 'index'])->name('students.index');

    Route::get('reset_password', 'Controller@reset_password')->name('reset_password');
    Route::post('reset_password', 'Controller@reset_password_save')->name('reset_password');
    
    Route::get('charges/set', 'Admin\HomeController@set_charges')->name('charges.set');
    Route::post('charges/set', 'Admin\HomeController@save_charges')->name('charges.save');

    // APPLICATION PLATFORM ROUTES ONLY

    Route::prefix('xbypass')->name('bypass.')->group(function(){
        Route::get('xapplication/{application_id?}', [AdminHomeController::class, '__application_bypass'])->name('application');
        Route::post('xapplication/{application_id?}', [AdminHomeController::class, '__save_application_bypass']);
        Route::get('xplatform/{student_id?}', [AdminHomeController::class, '__platform_bypass'])->name('platform');
        Route::post('xplatform/{student_id?}', [AdminHomeController::class, '__save_platform_bypass']);
    });

    Route::get('admission/campus/degrees/{cid?}', [ProgramController::class, 'config_degrees'])->name('admission.campus.degrees');
    Route::post('admission/campus/degrees/{cid?}', [ProgramController::class, 'set_config_degrees']);
    Route::get('admission/degree/certificates/{degree_id?}', [ProgramController::class, 'degree_certificates'])->name('admission.degree.certificates');
    Route::post('admission/degree/certificates/{degree_id?}', [ProgramController::class, 'set_degree_certificates']);
    Route::get('admission/open/{id?}', [ProgramController::class, 'open_admission'])->name('admission.open');
    Route::post('admission/open/{id?}', [ProgramController::class, 'set_open_admission']);
    Route::get('admission/programs/{cid?}', [ProgramController::class, 'config_programs'])->name('admission.programs.config');
    Route::post('admission/programs/{cid?}', [ProgramController::class, 'set_config_programs']);
    Route::get('admission/admit/{id}', [ProgramController::class, 'admit_student'])->name('admission.admit');
    Route::get('admission/show/{id}', [ProgramController::class, 'application_details'])->name('admission.show');
    Route::get('applications', [ProgramController::class, 'applications'])->name('applications.all');
    Route::name('applications.')->prefix('applications')->group(function(){
        Route::get('print_form/{id?}', [ProgramController::class, 'print_application_form'])->name('print_form');
        Route::get('edit/{id?}', [ProgramController::class, 'edit_application_form'])->name('update');
        Route::post('edit/{id?}', [ProgramController::class, 'update_application_form']);
        Route::get('download/{program_id?}', [ProgramController::class, 'downlaod_applications'])->name('download');
        Route::post('download/{program_id?}', [ProgramController::class, 'download_forms']);
        Route::get('admit/{id?}', [ProgramController::class, 'admit_application_form'])->name('admit');
        Route::post('admit/{id?}', [ProgramController::class, 'admit_student']);
        Route::get('uncompleted/{id?}', [ProgramController::class, 'uncompleted_application_form'])->name('uncompleted');
        Route::get('uncompleted/{id}/bypass', [ProgramController::class, 'bypass_application_form'])->name('bypass');
        Route::get('distant/{id?}', [ProgramController::class, 'distant_application_form'])->name('distant');
        Route::get('admission_letter/{id?}', [ProgramController::class, 'admission_letter'])->name('admission_letter');
        Route::get('program/change/{id?}', [ProgramController::class, 'application_form_change_program'])->name('change_program');
        Route::post('program/change/{id?}', [ProgramController::class, 'change_program']);
        Route::post('change_program/{id?}', [ProgramController::class, 'change_program_save'])->name('_change.program');
        Route::get('by_program/{id?}', [ProgramController::class, 'applications_per_program'])->name('by_program');
        Route::get('by_degree/{id?}', [ProgramController::class, 'applications_per_degree'])->name('by_degree');
        Route::get('finance/general', [ProgramController::class, 'finance_general_report'])->name('finance.general');

    });
    Route::prefix('reports')->name('reports.')->group(function(){
        Route::get('degree/{degree?}', [ProgramController::class, 'applicants_report_by_degree'])->name('applicants.by_degree');
        Route::get('program/{program?}', [ProgramController::class, 'applicants_report_by_program'])->name('applicants.by_program');
        Route::get('finance/general', [ProgramController::class, 'finance_report_general'])->name('applicants.by_program');
        Route::get('application/bypass', [ProgramController::class, 'application_bypass_report'])->name('application_bypass');
        Route::get('platform/bypass', [ProgramController::class, 'platform_bypass_report'])->name('platform_bypass');
    });
    Route::prefix('stats')->name('stats.')->group(function(){
        Route::get('application', [AdminHomeController::class, 'application_statistics'])->name('application');
        Route::get('admission', [AdminHomeController::class, 'admission_statistics'])->name('admission');
    });

    Route::get('search_student', [AdminHomeController::class, '_search_student'])->name('search_student');

    Route::get('campus/program/levels/{campus_id}/{program_id}', [Controller::class, 'campusProgramLevels'])->name('campus.program.levels');
});


Route::prefix('student')->name('student.')->middleware(['isStudent', 'platform'])->group(function () {
    Route::get('', 'Student\HomeController@index')->name('home');
    Route::get('edit_profile', 'Student\HomeController@edit_profile')->name('edit_profile');
    Route::post('update_profile', 'Student\HomeController@update_profile')->name('update_profile');
    
    Route::get('class-subjects/{level}', 'Student\HomeController@class_subjects')->name('class-subjects');
    Route::get('search_course', 'Student\HomeController@search_course')->name('search_course');

    Route::get('reset_password', 'Controller@reset_password')->name('reset_password');
    Route::post('reset_password', 'Controller@reset_password_save')->name('reset_password');

    Route::get('platform/pay', 'Student\HomeController@pay_platform_charges')->name('platform_charge.pay')->withoutMiddleware('platform');
    Route::post('charges/pay', 'Student\HomeController@pay_charges_save')->name('charge.pay')->withoutMiddleware('platform');
    
    Route::get('result/pay', 'Student\HomeController@pay_semester_results')->name('result.pay');
    Route::get('transcript/pay', 'Student\HomeController@pay_transcript_charges')->name('transcript.pay');

    Route::get('online_payments/history', 'Student\HomeController@online_payment_history')->name('online.payments.history');



    // ONLINE APPLICATION PORTAL ROUTES
    Route::get('campus/degree/certs/programs/{campus_id}/{degree_id}/{cert_id}', [Controller::class, 'campusDegreeCertPrograms'])->name('campus.degree.cert.programs');
    Route::get('campus/program/levels/{campus_id}/{program_id}', [Controller::class, 'campusProgramLevels'])->name('campus.program.levels');
    Route::get('campus/programs/{campus_id}', [Controller::class, 'campusPrograms'])->name('campus.programs');
    Route::get('campus/degrees/{campus_id}', [Controller::class, 'campusDegrees'])->name('campus.degrees');
    Route::get('region/divisions/{region_id}', [Controller::class, 'regionDivisions'])->name('region.divisions');
    Route::get('programs/all', [StudentHomeController::class, 'all_programs'])->name('programs.index');
    Route::get('payment/data', [StudentHomeController::class, 'payment_data'])->name('payment.data');
    Route::get('application/start/{step}/{id?}', [StudentHomeController::class, 'start_application'])->name('application.start');
    Route::post('application/start/{step}/{id?}', [StudentHomeController::class, 'persist_application']);
    Route::get('application/submit/{id?}', [StudentHomeController::class, 'submit_application'])->name('application.submit');
    Route::post('application/submit/{id?}', [StudentHomeController::class, 'submit_application_save']);
    Route::get('application/form/download/{id?}', [StudentHomeController::class, 'download_application_form'])->name('application.form.download');
    Route::post('application/form/download/{id?}', [StudentHomeController::class, 'download_form']);
    Route::get('application/admission_letter/download/{id?}', [StudentHomeController::class, 'download_admission_letter'])->name('application.admission_letter.download');
    Route::post('application/admission_letter/download/{id?}', [StudentHomeController::class, 'download_admission_letter_save']);
    Route::get('application/payment/processing/{form_id}', [StudentHomeController::class, 'pending_payment'])->name('application.payment.processing');
    Route::get('application/payment/complete/{form_id}', [StudentHomeController::class, 'pending_complete'])->name('application.payment.complete');
    Route::post('application/payment/complete/{form_id}', [StudentHomeController::class, 'pending_complete']);
});

Route::middleware('isStudent')->group(function(){
    Route::post('student/charges/pay', 'Student\HomeController@pay_charges_save')->name('student.charge.pay');
    Route::get('student/platform/pay', 'Student\HomeController@pay_platform_charges')->name('student.platform_charge.pay');
    Route::get('student/charges/complete_transaction/{ts_id}', 'Student\HomeController@complete_charges_transaction')->name('student.charges.complete');
    Route::get('student/charges/failed_transaction/{ts_id}', 'Student\HomeController@failed_charges_transaction')->name('student.charges.failed');
    Route::get('student/tranzak/processing', 'Student\HomeController@tranzak_payment_processing')->name('student.tranzak.processing');
    Route::post('student/tranzak/complete', 'Student\HomeController@tranzak_complete')->name('student.tranzak.complete');
});

Route::get('section-children/{parent}', 'HomeController@children')->name('section-children');
Route::get('section-subjects/{parent}', 'HomeController@subjects')->name('section-subjects');
Route::get('student-search/{name}', 'HomeController@student')->name('student-search');
Route::get('student-search', 'HomeController@student_get')->name('student-search-get');
Route::get('search-all-students/{name}', 'HomeController@searchStudents')->name('search-all-students');
Route::get('search-all-students', 'HomeController@searchStudents_get')->name('get-search-all-students');
Route::get('search-students', 'HomeController@search_students')->name('search_students');
Route::get('student-fee-search', 'HomeController@fee')->name('student-fee-search');
Route::get('student_rank', 'HomeController@rank')->name('student_rank');
Route::post('student_rank', 'HomeController@rankPost')->name('student_rank');

// Messages
Route::name('messages.')->prefix('messages')->group(function(){
    Route::get('create', [NotificationsController::class, 'create_message'])->name('create');
    Route::post('create', [NotificationsController::class, 'create_message_save']);
    Route::get('sent', [NotificationsController::class, 'sent_messages'])->name('sent');
});

Route::get('search/students/boarders/{name}', 'HomeController@getStudentBoarders')->name('getStudentBoarder');

Route::get('/campuses/{id}/programs', function(Request $request){
    $order = \App\Models\SchoolUnits::orderBy('name', 'ASC')->pluck('id')->toArray();
    $resp = DB::table('campus_programs')->where('campus_id', '=', $request->id)
                ->join('program_levels', 'program_levels.id', '=', 'campus_programs.program_level_id')
                ->get(['program_levels.*']);
    // $resp = \App\Models\CampusProgram::where('campus_id', $request->id)->get();
    // $resp = \App\Models\CampusProgram::where('campus_id', $request->id)->orderBy(function($model) use ($order){
    //     return array_search($model->getKey(), $order);
    // });
    $data = [];
    foreach ($resp as $key => $value) {

        $value->program = \App\Models\SchoolUnits::find($value->program_id)->name;
        $value->level = \App\Models\Level::find($value->level_id)->level;
        $data[] = $value;
    }

    return $data;
})->name('campus.programs');
Route::get('semesters/{background}', function(Request $request){
    return \App\Models\Semester::where('background_id', $request->background)->get();
})->name('semesters');
Route::get('class_subjects/{program_level_id}', function($program_level_id){
    // return $program_level_id;
    $courses = \App\Models\ClassSubject::where(['class_subjects.class_id'=>$program_level_id])
            ->join('subjects', ['subjects.id'=>'class_subjects.subject_id'])
            ->get('subjects.*');
            // return $courses;
            return response()->json(SubjectResource::collection($courses));
})->name('class_subjects');
Route::get('campus/{campus}/program_levels', [Controller::class, 'sorted_campus_program_levels'])->name('campus.program_levels');
Route::get('program_levels', [Controller::class, 'sorted_program_levels'])->name('program_levels');
Route::get('getColor/{label}', [HomeController::class, 'getColor'])->name('getColor');

Route::get('search_subjects', function (Request $request) {
    $data = $request->name;
    $subjects = Subjects::where('code', 'LIKE', '%' . $data . '%')
        ->orWhere('name', 'LIKE', '%' . $data . '%')->orderBy('name')->paginate(20);
    return $subjects;
})->name('search_subjects');

Route::get('get-income-item/{income_id}', function(Request $request, $income_id){
    return \App\Models\Income::find($income_id);
})->name('get-income-item');

Route::get('mode/{locale}', function ($batch) {
    session()->put('mode', $batch);

    return redirect()->back();
})->name('mode');

Route::get('trace_resits', function(){
    $data['resits'] = Resit::all();
    $resit_ids = $data['resits']->pluck('id')->toArray();
    $data['resit_students'] = StudentSubject::whereIn('resit_id', $resit_ids)->get();
    return $data;
});
