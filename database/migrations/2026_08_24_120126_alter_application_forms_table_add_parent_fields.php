<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterApplicationFormsTableAddParentFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if(Schema::hasColumn('application_forms', 'father')){
            return;
        }

        Schema::table('application_forms', function(Blueprint $table){
            $table->string('father')->nullable()->default(null);
            $table->string('father_phone')->nullable()->default(null);
            $table->string('mother')->nullable()->default(null);
            $table->string('mother_phone')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
