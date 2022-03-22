<?php

namespace Flynsarmy\RelatedRepeater\Updates;

use DB;
use Seeder;
use Flynsarmy\RelatedRepeater\Models\Option;
use Flynsarmy\RelatedRepeater\Models\OptionValue;

class InitialSeed extends Seeder
{
    public function run()
    {
        DB::disableQueryLog();

        $option = Option::create([
            'title' => "Colour",
        ]);

        $option->option_values()->save(new OptionValue([
            'key' => "red",
            "value" => "Red",
        ]));
        $option->option_values()->save(new OptionValue([
            'key' => "green",
            "value" => "Green",
        ]));
        $option->option_values()->save(new OptionValue([
            'key' => "blue",
            "value" => "Blue",
        ]));
        $option->save();

        DB::enableQueryLog();
    }
}
