<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attach_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('location', 200);
            $table->string('format', 50)->nullable();
            $table->float('size', 10, 0)->unsigned()->nullable();
            $table->string('linked_type', 20);
            $table->integer('linked_id')->unsigned();
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
        Schema::dropIfExists('attach_files');
    }
}
