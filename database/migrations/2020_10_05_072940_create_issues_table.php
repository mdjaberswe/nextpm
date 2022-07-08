<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('issue_owner')->unsigned()->nullable();
            $table->string('name', 200);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('issue_status_id')->unsigned();
            $table->integer('issue_type_id')->unsigned()->nullable();
            $table->enum('severity', ['blocker', 'critical', 'major', 'minor', 'trivial'])->nullable();
            $table->enum('reproducible', ['always', 'sometimes', 'rarely', 'only_once', 'unable'])->nullable();
            $table->text('description', 65535)->nullable();
            $table->string('linked_type', 20)->nullable();
            $table->integer('linked_id')->unsigned()->nullable();
            $table->integer('release_milestone_id')->unsigned()->nullable();
            $table->integer('affected_milestone_id')->unsigned()->nullable();
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
        Schema::dropIfExists('issues');
    }
}
