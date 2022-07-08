<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_owner')->unsigned();
            $table->string('name', 200);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location', 200)->nullable();
            $table->text('description', 65535)->nullable();
            $table->enum('priority', ['high', 'highest', 'low', 'lowest', 'normal'])->nullable();
            $table->enum('access', ['private', 'public', 'public_rwd'])->default('public');
            $table->string('linked_type', 20)->nullable();
            $table->integer('linked_id')->unsigned()->nullable();
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
        Schema::dropIfExists('events');
    }
}
