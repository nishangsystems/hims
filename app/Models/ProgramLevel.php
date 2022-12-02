<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramLevel extends Model
{
    use HasFactory;
    
    protected $fillable = ['program_id', 'level_id'];
    
    public function students()
    {
        return $this->hasMany(Students::class, 'program_id');
    }
    public function _students($year)
    {
        return $this->belongsToMany(Students::class, 'student_classes', 'class_id', 'program_id')->where('year_id', '=', $year);
    }
    public function program()
    {
        return $this->belongsTo(SchoolUnits::class, 'program_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function campuses()
    {
        return $this->belongsToMany(Campus::class, CampusProgram::class, 'program_level_id', 'campus_id');
    }

    public function class_subjects()
    {
        $this->hasMany(ClassSubject::class, 'class_id');
    }
    public function subjects()
    {
        return $this->belongsToMany( Subjects::class, ClassSubject::class, 'class_id', 'subject_id');
    }

    public function name()
    {
        # code...
        // dd ($this->program->name.' : Level '.$this->level->level);

        return SchoolUnits::find($this->program_id)->name.' : Level '.Level::find($this->level_id)->level;
    }
}
