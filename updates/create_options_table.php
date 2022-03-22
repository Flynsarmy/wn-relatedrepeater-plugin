<?php

namespace Flynsarmy\RelatedRepeater\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('rr_options', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title')->default('');
            $table->integer('sort_order')->default(0)->index();
        });

        Schema::create('rr_option_values', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('option_id')->nullable()->default(0)->unsigned()->index();
            $table->string('key')->default('');
            $table->string('value')->default('');
            $table->integer('sort_order')->default(0)->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rr_option_values');
        Schema::dropIfExists('rr_options');
    }
}
