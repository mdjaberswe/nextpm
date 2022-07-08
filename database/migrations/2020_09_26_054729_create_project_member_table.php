<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_member', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('staff_id')->unsigned();
            // Project permissions
            $table->boolean('project_view')->default(1);
            $table->boolean('project_edit')->default(0);
            $table->boolean('project_delete')->default(0);
            // Project Member Permission
            $table->boolean('member_view')->default(0);
            $table->boolean('member_create')->default(0);
            $table->boolean('member_edit')->default(0);
            $table->boolean('member_delete')->default(0);
            // Project activity => Milestone permissions
            $table->boolean('milestone_view')->default(1);
            $table->boolean('milestone_create')->default(0);
            $table->boolean('milestone_edit')->default(0);
            $table->boolean('milestone_delete')->default(0);
            // Project activity => Task permissions
            $table->boolean('task_view')->default(1);
            $table->boolean('task_create')->default(0);
            $table->boolean('task_edit')->default(0);
            $table->boolean('task_delete')->default(0);
            // Project activity => Issue permissions
            $table->boolean('issue_view')->default(1);
            $table->boolean('issue_create')->default(0);
            $table->boolean('issue_edit')->default(0);
            $table->boolean('issue_delete')->default(0);
            // Project activity => Event permissions
            $table->boolean('event_view')->default(1);
            $table->boolean('event_create')->default(0);
            $table->boolean('event_edit')->default(0);
            $table->boolean('event_delete')->default(0);
            // Project => Note permissions
            $table->boolean('note_view')->default(0);
            $table->boolean('note_create')->default(0);
            $table->boolean('note_edit')->default(0);
            $table->boolean('note_delete')->default(0);
            // Project => Attachment permissions
            $table->boolean('attachment_view')->default(0);
            $table->boolean('attachment_create')->default(0);
            $table->boolean('attachment_delete')->default(0);
            // Project => Tool permissions
            $table->boolean('gantt')->default(0);
            $table->boolean('report')->default(0);
            $table->boolean('history')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_member');
    }
}
