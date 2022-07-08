<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_name', 200);
            $table->string('module_name', 200);
            $table->boolean('is_imported')->default(0);
            $table->enum('import_type', ['new', 'update', 'update_overwrite']);
            $table->longText('created_data')->nullable();
            $table->longText('updated_data')->nullable();
            $table->longText('skipped_data')->nullable();
            $table->longText('initial_data')->nullable();
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
        Schema::dropIfExists('imports');
    }
}
