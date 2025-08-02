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

    public function sections()
    {
        $data['title'] = __('text.word_sections');
        $data['parent_id'] = 0;
        $data['units'] = \App\Models\SchoolUnits::where('parent_id', 0)->get();
        return view('admin.units.sections')->with($data);
    }


    public static function subunitsOf($id){
        $s_units = [];
        $direct_sub = DB::table('school_units')->where('parent_id', '=', $id)->get()->pluck('id')->toArray();
        $s_units[] = $id;
        if (count($direct_sub) > 0) {
            # code...
            foreach ($direct_sub as $sub) {
                # code...
                $s_units = array_merge_recursive($s_units, Self::subunitsOf($sub));
            }
        }
        return $s_units;
    }

    public static function orderedUnitsTree()
    {
        # code...
        $ids = DB::table('school_units')
                ->pluck('id')
                ->toArray();
        $units = [];
        $names = Self::allUnitNames();
        foreach ($ids as $id) {
            # code...
            foreach (Self::subunitsOf($id) as $sub) {
                # code...
                if (!in_array($sub, $units)) {
                    # code...
                    $units[$sub] = $names[$sub];
                }
            } 
        }
        return $units;
    }

    public static function allUnitNames()
    {
        # code...
        // added by Germanus. Loads listing of all classes accross all sections in a given school
        
        $base_units = DB::table('school_units')->get();
    
        // return $base_units;
        $listing = [];
        $separator = ' : ';
        foreach ($base_units as $key => $value) {
            # code...
            // set current parent as key and name as value, appending from the parent_array
            if (array_key_exists($value->parent_id, $listing)) {
                $listing[$value->id] = $listing[$value->parent_id] . $separator . $value->name; 
            }else {$listing[$value->id] = $value->name;}
    
            // atatch parent units if there be any
            if ($base_units->where('id', '=', $value->parent_id)->count() > 0) {
                // return $base_units->where('id', '=', $value->parent_id)->pluck('name')[0];
                $listing[$value->id] = array_key_exists($value->parent_id, $listing) ? 
                $listing[$value->parent_id] . $separator . $value->name :
                $base_units->where('id', '=', $value->parent_id)->pluck('name')[0] . $separator . $value->name ;
            }
            // if children are obove, move over and prepend to children listing
            foreach ($base_units->where('parent_id', '=', $value->id) as $keyi => $valuei) {
                $value->id > $valuei->id ?
                $listing[$valuei->id] = $listing[$value->id] . $separator . $listing[$value->id]:
                null;
            }
        }
        return $listing;
    }

    public function index($parent_id)
    {
        $data = [];
        $parent = \App\Models\SchoolUnits::find($parent_id);
        if (!$parent) {
            return  redirect(route('admin.sections'));
        }
        $units =  $parent->unit;
        $name = $parent->name;
        $data['title'] = ($units->count() == 0) ? __('text.no_sub_units_available_in', ['parent'=>$name]) : __('text.word_all').' '.$units->first()->type->name . " > {$name}";
        $data['units']  = $units;
        $data['parent_id']  = $parent_id;
        return view('admin.units.index')->with($data);
    }

    public function show($parent_id)
    {
        $data = [];
        $parent = \App\Models\SchoolUnits::find($parent_id);
        if (!$parent) {
            return  redirect(route('admin.sections'));
        }
        $units =  $parent->unit();
        $data['title'] = ($units->count() == 0) ? "No Sub Units Available in " . $parent->name : "All " . $units->first()->type->name;
        $data['units']  = $units;
        $data['parent_id']  = $parent_id;
        return view('admin.units.show')->with($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $unit = new \App\Models\SchoolUnits();
            $unit->name = $request->input('name');
            $unit->unit_id = $request->input('type');
            $unit->parent_id = $request->input('parent_id');
            $unit->prefix = $request->input('prefix');
            $unit->suffix = $request->input('suffix');
            $unit->save();
            DB::commit();
            return redirect()->to(route('admin.units.index', [$unit->parent_id]))->with('success', __('text.word_done'));
        } catch (\Exception $e) {
            DB::rollback();
            echo ($e);
        }
    }

    public function edit(Request $request, $id)
    {
        $lang = !$request->lang ? 'en' : $request->lang;
        \App::setLocale($lang);
        $data['id'] = $id;
        $unit = \App\Models\SchoolUnits::find($id);
        $data['unit'] = $unit;
        $data['parent_id'] = \App\Models\SchoolUnits::find($id)->parent_id;
        $data['title'] = __('text.word_edit')." " . $unit->name;
        return view('admin.units.edit')->with($data);
    }

    public function create(Request $request, $parent_id)
    {
        $data['parent_id'] = $parent_id;
        $parent = \App\Models\SchoolUnits::find($parent_id);
        $data['title'] = $parent ? __('text.new_sub_unit_under', ['item'=>$parent->name]) : __('text.new_section');
        return view('admin.units.create')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $unit = \App\Models\SchoolUnits::find($id);
            $unit->name = $request->input('name');
            $unit->unit_id = $request->input('type');
            $unit->prefix = $request->input('prefix');
            $unit->suffix = $request->input('suffix');
            $unit->parent_id = $request->input('parent_id');
            $unit->save();
            DB::commit();

            return redirect()->to(route('admin.units.index', [$unit->parent_id]))->with('success', __('text.word_done'));
        } catch (\Exception $e) {
            DB::rollback();
            echo ($e);
        }
    }

    /**
     * Delete the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $slug
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $unit = \App\Models\SchoolUnits::find($slug);
        if ($unit->unit->count() > 0) {
            return redirect()->back()->with('error', __('text.operation_not_allowed'));
        }
        $unit->delete();
        return redirect()->back()->with('success', __('text.word_done'));
    }


    // Request contains $program_id as $parent_id and $level_id
    public function subjects($program_level_id)
    {
        $parent = ProgramLevel::find($program_level_id);
        $data['title'] = __('text.subjects_under', ['class'=>$parent->name()]);
        $data['parent'] = $parent;
        // dd($parent->subjects()->get());
        $data['subjects'] = ProgramLevel::find($program_level_id)->subjects()->get();
        return view('admin.units.subjects')->with($data);
    }

    public function manageSubjects($parent_id)
    {
        $parent = ProgramLevel::find($parent_id);
        $data['parent'] = $parent;
        // return $parent;
        
        $data['title'] = __('text.manage_subjects_under', ['class'=>$parent->name()]);
        return view('admin.units.manage_subjects')->with($data);
    }

    public function students($id)
    { 
        return $this->studentsListing($id);

        $parent = \App\Models\SchoolUnits::find($id);
        $data['parent'] = $parent;

        $data['title'] = __('text.manage_students_under', ['unit'=>$parent->name]);
        return view('admin.units.student')->with($data);
    }
    public function studentsListing($id)
    {
    # code...
    // get array of ids of all sub units
    $year = \App\Helpers\Helpers::instance()->getCurrentAccademicYear();
    $subUnits = $this->subunitsOf($id);

    $students = DB::table('student_classes')
            ->whereIn('class_id', $subUnits)
            ->join('students', 'students.id', '=', 'student_classes.student_id')
            ->get();
    $parent = ProgramLevel::find($id);
    $data['parent'] = $parent;
    $data['students'] = $students;
    // dd($parent);
    $data['classes'] = \App\Http\Controllers\Admin\StudentController::baseClasses();
    $data['title'] = __('text.manage_students_under', ['unit'=>$parent->program()->first()->name]);
    return view('admin.units.student-listing')->with($data);
    }

    public function saveSubjects(Request  $request, $id)
    {
        $pl = ProgramLevel::find(request('parent_id'));
        $class_subjects = [];
        $validator = Validator::make($request->all(), [
            'subjects' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $parent = $pl;

        $new_subjects = $request->subjects;
        // if($parent != null)
        foreach ($parent->subjects()->get() as $subject) {
            array_push($class_subjects, $subject->subject_id);
        }


        foreach ($new_subjects as $subject) {
            if (!in_array($subject, $class_subjects)) {
                if(\App\Models\ClassSubject::where('class_id', $pl->id)->where('subject_id', $subject)->count()>0){
                    continue;
                }
                \App\Models\ClassSubject::create([
                    'class_id' => $pl->id,
                    'subject_id' => $subject,
                    'status'=> \App\Models\Subjects::find($subject)->status,
                    'coef'=> \App\Models\Subjects::find($subject)->coef
                ]);
            }
        }

        foreach ($class_subjects as $k => $subject) {
            if (!in_array($subject, $new_subjects)) {
                ClassSubject::where('class_id', $pl->id)->where('subject_id', $subject)->count() > 0 ?
                ClassSubject::where('class_id', $pl->id)->where('subject_id', $subject)->first()->delete() : null;
            }
        }


        $data['title'] = __('text.manage_subjects_under', ['class'=>$parent->name()]);
        return redirect()->back()->with('success', __('text.word_done'));
    }

    public function getSubUnits($parent_id)
    {
        $data = SchoolUnits::where('parent_id', $parent_id)->get();
        return response()->json($data);
    }

    public function semesters($background_id)
    {
        # code...
        $data['title'] = __('text.manage_semesters_under', ['unit'=>\App\Models\SchoolUnits::find($background_id)->name]);
        $data['semesters'] = \App\Models\SchoolUnits::find($background_id)->semesters()->get();
        return view('admin.semesters.index')->with($data);
    }

    public function create_semester($background_id)
    {
        # code...
        $data['title'] = __('text.create_semesters_under', ['unit'=>\App\Models\SchoolUnits::find($background_id)->name]);
        $data['semesters'] = \App\Models\SchoolUnits::find($background_id)->semesters()->get();
        return view('admin.semesters.create')->with($data);
    }

    public function edit_semester($background_id, $id)
    {
        # code...
        $data['title'] = __('text.edit_semester');
        $data['semesters'] = \App\Models\SchoolUnits::find($background_id)->semesters()->get();
        $data['semester'] = \App\Models\Semester::find($id);
        return view('admin.semesters.edit');
    }

    public function store_semester($program_id, Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'program_id'=>'required',
            'name'=>'required',
        ]);

        if ($validator->fails()) {
            # code...
            return back()->with('error', $validator->errors()->first());
        }
        try {
            //code...
            if (\App\Models\SchoolUnits::find($program_id)->semesters()->where('name', $request->name)->first()) {
                # code...
                return back()->with('error', __('text.record_already_exist', ['item'=>$request->name]));
            }
            $semester = new \App\Models\Semester($request->all());
            $semester->save();
            return back()->with('success', __('text.word_done'));
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('error', $th->getMessage());
        }
    }

    public function update_semester($program_id, $id)
    {
        # code...
    }

    public function delete_semester($id)
    {
        # code...
    }

    public function set_program_semester_type($program_id)
    {
        # code...
        $data['title'] = __('text.set_semester_type_for', ['unit'=>\App\Models\SchoolUnits::find($program_id)->name]);
        $data['semester_types'] = \App\Models\SemesterType::all();
        return view('admin.semesters.set_type', $data);
    }

    public function post_program_semester_type($program_id, Request $request)
    {
        # code...
        $validator = Validator::make(
            $request->all(),
            ['program_id'=>'required', 'background_id'=>'required']
        );

        if ($validator->fails()) {
            # code...
            return back()->with('error', $validator->errors()->first());
        }
        $program = \App\Models\SchoolUnits::find($program_id);
        $program->background_id = $request->background_id;
        $program->save();
        return back()->with('success', __('text.word_done'));
    }

    public function assign_program_level()
    {
        $data['title'] = __('text.manage_program_levels');
        return view('admin.units.set-levels', $data);
    }

    public function store_program_level(Request $request)
    {
        $this->validate($request, [
            'program_id'=>'required',
            'levels'=>'required'
        ]);
        // return $request->all();

        foreach ($request->levels as $key => $lev) {
            if (ProgramLevel::where('program_id', $request->program_id)->where('level_id', $lev)->count() == 0) {
                ProgramLevel::create(['program_id'=>$request->program_id, 'level_id'=>$lev]);
            }
        }
        return back()->with('success', __('text.word_done'));
    }

    public function program_levels($id)
    {
        $data['title'] = __('text.program_levels_for', ['unit'=>\App\Models\SchoolUnits::find($id)->name]);
        $data['program_levels'] =  ProgramLevel::where('program_id', $id)->pluck('level_id')->toArray();
        // $data['program_levels'] =  DB::table('school_units')->where('school_units.id', '=', $id)
        //             ->join('program_levels', 'program_id', '=', 'school_units.id')
        //             ->join('levels', 'levels.id', '=', 'program_levels.level_id')
        //             ->get(['program_levels.*', 'school_units.name as program', 'levels.level as level']);
        // dd($data);
        return view('admin.units.program-levels', $data);
    }


    public function program_index()
    {
        # code...
        $data['title'] = __('text.manage_programs');
        $data['programs'] = \App\Models\SchoolUnits::where('unit_id', 4)->get();
        // dd($data);
        return view('admin.units.programs', $data);
    }

    public function add_program_level($id, $level_id)
    {
        # code...
        if (ProgramLevel::where('program_id', $id)->where('level_id', $level_id)->count()>0) {
            # code...
            return back()->with('error', __('text.level_not_in_program'));
        }
        $pl = new ProgramLevel(['program_id'=>$id, 'level_id'=>$level_id]);
        $pl->save();
        return back()->with('success', __('text.word_done'));
    }

    public function _drop_program_level($id)
    {
        # code...
        if (ProgramLevel::find($id)==null) {
            # code...
            return back()->with('error', __('text.level_not_in_program'));
        }

        ProgramLevel::find($id)->delete();
        return back()->with('success', __('text.word_done'));
        
    }

    public function drop_program_level($id, $level_id)
    {
        # code...
        if (ProgramLevel::where('program_id', $id)->where('level_id', $level_id)->count()==0) {
            # code...
            return back()->with('error', __('text.level_not_in_program'));
        }
        ProgramLevel::where('program_id', $id)->where('level_id', $level_id)->first()->delete();
        return back()->with('success', __('text.word_done'));
        
    }

    public function program_levels_list()
    {
        # code...
        $data['title'] = __('text.class_list_for', ['campus'=>request()->has('campus_id') ? \App\Models\Campus::find(request('campus_id'))->name : '', 'class'=>request()->has('id') ? ProgramLevel::find(request('id'))->name() : '', 'year'=>request('year_id') != null ? Batch::find(request('year_id'))->name : '']);
        return view('admin.student.class_list', $data);
    }

    public function program_levels_list_index(Request $request)
    {
        # code...
        $data['title'] = __('text.student_listing');
        $data['filter'] = $request->filter ?? null;
        $data['items'] = [];
        if ($request->filter != null) {
            # code...
            switch ($request->filter) {
                case 'SCHOOL':
                    # code...
                    $schools = SchoolUnits::where(['unit_id'=>1])->get();
                    foreach ($schools as $key => $value) {
                        # code...
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                    
                case 'FACULTY':
                    # code...
                    $faculties = SchoolUnits::where(['unit_id'=>2])->get();
                    foreach ($faculties as $key => $value) {
                        # code...
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                        
                case 'DEPARTMENT':
                    # code...
                    $departments = SchoolUnits::where(['unit_id'=>3])->get();
                    foreach ($departments as $key => $value) {
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                
                case 'PROGRAM':
                    # code...
                    $programs = SchoolUnits::where(['unit_id'=>4])->get();
                    // dd($programs);
                    foreach ($programs as $key => $value) {
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                
                case 'CLASS':
                    # code...
                    $classes = Controller::sorted_program_levels();
                    foreach ($classes as $key => $value) {
                        $data['items'][] = ['id'=>$value['id'], 'name'=>$value['name']];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                
                case 'LEVEL':
                    # code...
                    $levels = Level::all();
                    foreach ($levels as $key => $value) {
                        $data['items'][] = ['id'=>$value->id, 'name'=>'Level '.$value->level];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                    
                    default:
                    # code...
                    break;
                }
            }
            // dd($data);
            return view('admin.student.student_list_index', $data);
    }

    public function bulk_program_levels_list(Request $request)
    {
        $year = $request->year_id ?? Helpers::instance()->getCurrentAccademicYear();
        # code...
        switch($request->filter){
            case 'SCHOOL':
                $data['title'] = __('text.students_for_school_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>1])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $students = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;
            
            case 'FACULTY' :
                $data['title'] = __('text.students_for_faculty_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>2])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $students = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;

            case 'DEPARTMENT':
                $data['title'] = __('text.students_for_department_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>3])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        // ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'school_units.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;

            case 'PROGRAM':
                $data['title'] = __('text.students_for', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $classes = ProgramLevel::where('program_id', $request->item_id)->pluck('id')->toArray();
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->where('students.active', true)
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;
                

            case 'CLASS':
                $data['title'] = __('text.all_students_for', ['unit'=>ProgramLevel::find($request->item_id)->name()]);
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->orderBy('students.name')->where('class_id', $request->item_id)->where('year_id', '=', $year)
                            ->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;

            case 'LEVEL':
                $level = Level::find($request->item_id);
                $data['title'] = __('text.all_students_for', ['unit'=>$level->level??''.' - '.Batch::find($request->year_id)->name]);
                $classes = ProgramLevel::where('level_id', '=', $level->id)->pluck('id')->toArray();
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $request->year_id)
                            ->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id']);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;
            
        }
    }

    public function bulk_message_notifications(Request $request)
    {
        $recipients = $request->recipients;
        $year = $request->year_id ?? Helpers::instance()->getCurrentAccademicYear();
        # code...
        switch($request->filter){
            case 'SCHOOL':
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);
            
            case 'FACULTY' :
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);

            case 'DEPARTMENT':
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);

            case 'PROGRAM':
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);
                

            case 'CLASS':
                $data['title'] = "Send Message Notification";
                $data['target'] = ProgramLevel::find($request->item_id)->name();
                return view('admin.student.bulk_messages', $data);

            case 'LEVEL':
                $level = Level::find($request->item_id);
                $data['title'] = "Send Message Notification";
                $data['target'] = $level->level??''.' - '.Batch::find($request->year_id)->name;
                return view('admin.student.bulk_messages', $data);
            
        }
    }
    public function bulk_message_notifications_save(Request $request)
    {
        $request->validate(['text'=>'required']);
        $recipients = $request->recipients;
        $recipients_field = $recipients == 'students' ? 'phone' : 'parent_phone_number';
        $year = $request->year_id ?? Helpers::instance()->getCurrentAccademicYear();
        # code...
        switch($request->filter){
            case 'SCHOOL':
                $data['title'] = __('text.students_for_school_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>1])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                
                Self::sendSmsNotificaition($request->text, $contacts);
                
                // dd($students);
                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;
            
            case 'FACULTY' :
                $data['title'] = __('text.students_for_faculty_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>2])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();


                Self::sendSmsNotificaition($request->text, $contacts);
                
                // dd($students);
                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;

            case 'DEPARTMENT':
                $data['title'] = __('text.students_for_department_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>3])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        // ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'school_units.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                        ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                // dd($students);
                
                Self::sendSmsNotificaition($request->text, $contacts);
                
                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;

            case 'PROGRAM':
                $data['title'] = __('text.students_for', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $classes = ProgramLevel::where('program_id', $request->item_id)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                // dd($students);

                Self::sendSmsNotificaition($request->text, $contacts);

                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;
                

            case 'CLASS':
                $data['title'] = __('text.all_students_for', ['unit'=>ProgramLevel::find($request->item_id)->name()]);
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->orderBy('students.name')->where('class_id', $request->item_id)->where('year_id', '=', $year)
                            ->distinct()->pluck($recipients_field)->toArray();
                // $data['students'] = $students;
                
                Self::sendSmsNotificaition($request->text, $contacts);

                // return view('admin.student.bulk_list', $data);
                break;

            case 'LEVEL':
                $level = Level::find($request->item_id);
                $data['title'] = __('text.all_students_for', ['unit'=>$level->level??''.' - '.Batch::find($request->year_id)->name]);
                $classes = ProgramLevel::where('level_id', '=', $level->id)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $request->year_id)
                            ->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                // $data['students'] = $students;

                Self::sendSmsNotificaition($request->text, $contacts);

                // return view('admin.student.bulk_list', $data);
                break;
            
        }
    }
    
    public function set_program_grading_type(Request $request, $program_id)
    {
        # code...
        $data['title'] = __('text.set_program_grading_type_for', ['unit'=>SchoolUnits::find($program_id)->name]);
        return view('admin.grading.set_grading_type', $data);
    }

    public function save_program_grading_type(Request $request, $program_id)
    {
        # code...
        $valid = Validator::make($request->all(), ['grading_type'=>'required', 'program_id'=>'required']);
        if ($valid->fails()) {
            # code...
            return $valid->errors()->first();
        }

        $program  = SchoolUnits::find($program_id);
        $program->grading_type_id = $request->grading_type;
        $program->save();
        return back()->with('success', __('text.word_done'));
    }

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
        $data['application'] = ApplicationForm::find($id);
        $data['title'] = "Application Details For ".$data['application']->name;
        
    }

    
    public function print_application_form(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Print Student Application Form";
            $data['_this'] = $this;
            $data['action'] = __('text.word_print');
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
        if($id == null){
            $data['title'] = "Edit Student Information";
            $data['_this'] = $this;
            $data['action'] = __('text.word_edit');
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where(['year_id'=> Helpers::instance()->getCurrentAccademicYear(), 'submitted'=>1])->get();
            return view('admin.student.applications', $data);
        }

        # code...
        $data['programs'] = collect(json_decode($this->api_service->programs())->data);
        $data['application'] = ApplicationForm::find($id);
        
        $data['title'] = "EDIT APPLICATION FORM FOR ".$data['application']->degree->name;
        return view('admin.student.edit_form', $data);
        
    }

    public function update_application_form(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['name'=>'required']);
        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }

        $data = ['name'=>$request->name, 'program'=>$request->program];
        $application = ApplicationForm::find($id);
        $application->update($data);
        if($application->admitted == 1){
            $this->api_service->update_student($application->matric, ['name'=>$request->name,]);
        }
        return back()->with('success', __('text.word_done'));
    }

    public function uncompleted_application_form(Request $request, $id=null)
    {
        # code...
        if($id == null){
            $data['title'] = "Uncompleted Application Forms";
            $data['_this'] = $this;
            $data['action'] = __('text.word_show');
            // $data['bypass'] = 'bypass form';
            $data['programs'] = collect(json_decode($this->api_service->programs())->data);
            $data['applications'] = ApplicationForm::whereNull('transaction_id')->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            // return $data;
            return view('admin.student.applications', $data);
        }

        // return $this->api_service->campuses();
        $data['application'] = ApplicationForm::find($id);


        $data['title'] = "INCOMPLETE APPLICATION FORM FOR ".$data['application']->degree->name??'';
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

}
