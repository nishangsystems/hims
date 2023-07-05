<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'telephone',
        'school_id'
    ];

    public function programs()
    {
        return $this->belongsToMany(SchoolUnits::class, CampusProgram::class, 'program_id', 'campus_id');
    }

    public function students()
    {
        return $this->hasMany(Students::class, 'campus_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'campus_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function campus_programs()
    {
        return $this->hasMany(CampusProgram::class);
    }


    public function resits()
    {
        # code...
        return $this->hasMany(Resit::class, 'campus_id');
    }

    public function degrees()
    {
        # code...
        return $this->belongsToMany(Degree::class, CampusDegree::class, 'campus_id', 'degree_id');
    }
}
