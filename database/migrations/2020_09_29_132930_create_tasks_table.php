<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_owner')->unsigned()->nullable();
            $table->string('name', 200);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('task_status_id')->unsigned();
            $table->tinyInteger('completion_percentage')->unsigned()->default(0);
            $table->enum('priority', ['high', 'highest', 'low', 'lowest', 'normal'])->nullable();
            $table->text('description', 65535)->nullable();
            $table->string('linked_type', 20)->nullable();
            $table->integer('linked_id')->unsigned()->nullable();
            $table->integer('milestone_id')->unsigned()->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
