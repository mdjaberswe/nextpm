<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_status', function (Blueprint $table) {
            $table->increments('id');
            $table->float('position', 10, 0)->unsigned();
            $table->string('name', 200)->unique();
            $table->enum('category', ['open', 'closed']);
            $table->tinyInteger('completion_percentage')->unsigned();
            $table->text('description', 65535)->nullable();
            $table->boolean('fixed')->default(0);
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
        Schema::dropIfExists('task_status');
    }
}
