<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMilestonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('milestone_owner')->unsigned();
            $table->string('name', 200);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description', 65535)->nullable();
            $table->enum('access', ['private', 'public', 'public_rwd'])->default('public');
            $table->float('position', 10, 0)->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('milestones');
    }
}
