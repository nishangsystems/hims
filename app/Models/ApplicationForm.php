<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationForm extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    protected $fillable = [
        'student_id', 'year_id', 'gender', 'name', 'dob', 'id_card_number', 'id_date_of_issue', 'id_place_of_issue', 'nationality', 'country_of_birth', 'referer', 'pob', 'region', 'residence', 'phone', 'extra_phone', 'email',
        'program', 'guardian', 'guardian_phone', 'guardian_address', 'sponsor', 'sponsor_phone', 'sponsor_address', 'secondary_school', 'secondary_exam_center', 'secondary_candidate_number', 'secondary_exam_year', 'gce_ol_record',
        'high_school', 'high_school_exam_center', 'high_school_candidate_number', 'high_school_exam_year', 'gce_al_record', 'matric', 'level',
        'candidate_declaration', 'parent_declaration', 'degree_id', 'admitted', 'submitted', 'momo_number', 'transaction_id'
    ];

    protected $dates = ['dob', 'created_at', 'updated_at', 'id_date_of_issue'];
    public function can_submit()
    {
        # code...
        return !in_array(null, [
            $this->degree_id, $this->name, $this->dob, $this->pob, $this->gender, $this->id_card_number, 
            $this->id_date_of_issue, $this->id_place_of_issue, $this->nationality, $this->region, 
            $this->country_of_birth, $this->referer, $this->phone, $this->residence,
            $this->guardian, $this->guardian_phone, $this->guardian_address, $this->sponsor, 
            $this->sponsor_phone, $this->sponsor_address, $this->secondary_school, $this->secondary_exam_center, 
            $this->secondary_candidate_number, $this->secondary_exam_year, $this->gce_ol_record, 
            $this->high_school, $this->high_school_exam_center, $this->high_school_candidate_number, 
            $this->high_school_exam_year, $this->gce_al_record, $this->program
        ]);
    }

    public function alternate(){
        return in_array($this->degree_id, [3, 6]);
    }

    public function student()
    {
        # code...
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function degree()
    {
        # code...
        return $this->belongsTo(Degree::class);
    }

    public function year()
    {
        # code...
        return $this->belongsTo(Batch::class, 'year_id');
    }

    public function _region()
    {
        # code...
        return $this->belongsTo(Region::class, 'region');
    }

    public function campus_banks()
    {
        return CampusBank::where('campus_id', $this->campus_id);
    }

    public function _program()
    {
        # code...
        return $this->belongsTo(Program::class, 'program');
    }

    public function tranzak_transaction()
    {
        # code...
        return $this->belongsTo(TranzakTransaction::class, 'transaction_id');
    }

    public function transaction()
    {
        # code...
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

}
