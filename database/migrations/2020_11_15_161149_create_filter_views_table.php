<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_views', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module_name', 20);
            $table->string('view_name', 200);
            $table->text('filter_params', 65535)->nullable();
            $table->enum('visible_type', ['only_me', 'everyone', 'selected_users']);
            $table->text('visible_to', 65535)->nullable();
            $table->boolean('is_fixed')->default(0);
            $table->boolean('is_default')->default(0);
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
        Schema::dropIfExists('filter_views');
    }
}
