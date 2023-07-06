<?php
namespace App\Http\Services;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Http;

class ApiService{

    // get campuses
    public function campuses(){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.campuses'))->body();
    }

    // get campuses
    public function campusDegrees($campus_id){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.campus_degrees').'/'.$campus_id)->body();
    }

    // get campuses
    public function campusProgramLevels($campus_id, $program_id){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.campus_program_levels').'/'.$campus_id.'/'.$program_id)->body();
    }

    // get campuses
    public function setCertificatePrograms($certificate_id, array $program_ids){
        return Http::post(Helpers::instance()->getApiRoot().'/'.config('api_routes.certificate_programs').'/'.$certificate_id, ['certificate_id'=>$certificate_id, 'program_ids'=>$program_ids])->body();
    }

    // get campuses
    public function certificatePrograms($certificate_id){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.certificate_programs').'/'.$certificate_id)->body();
    }

    // get campuses
    public function certificates(){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.certificates'))->body();
    }

    // get campuses
    public function degrees(){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.degrees'))->body();
    }

    // get campuses
    public function campusDegreeCertificatePrograms($campus_id, $degree_id, $certificate_id){
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.campus_degree_certificate_programs').'/'.$campus_id.'/'.$degree_id.'/'.$certificate_id)->body();
    }

    public function programs()
    {
        # code...
        return Http::get(Helpers::instance()->getApiRoot().'/'.config('api_routes.programs'))->body();
    }

}