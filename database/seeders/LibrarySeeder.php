<?php

namespace Database\Seeders;

use App\Models\Poet;
use App\Models\Saying;
use Illuminate\Database\Seeder;

/**
 * Loads خزانة الأدب from database/data/library.json.
 * Safe to run more than once: rows are matched by slug / text instead of duplicated.
 */
class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $library = json_decode(file_get_contents(database_path('data/library.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($library['poets'] as $poetOrder => $poetData) {
            $poet = Poet::updateOrCreate(['slug' => $poetData['slug']], [
                ...collect($poetData)->only(['name', 'era', 'years', 'region', 'bio', 'is_peninsula', 'color'])->all(),
                'sort_order' => $poetOrder,
            ]);

            foreach ($poetData['works'] as $workOrder => $workData) {
                $work = $poet->works()->updateOrCreate(['slug' => $workData['slug']], [
                    ...collect($workData)->only(['title', 'type', 'color', 'ink'])->all(),
                    'sort_order' => $workOrder,
                ]);

                foreach ($workData['verses'] as $verseOrder => [$sadr, $ajuz, $occasion]) {
                    $work->verses()->updateOrCreate(['sadr' => $sadr], [
                        'ajuz' => $ajuz,
                        'occasion' => $occasion,
                        'sort_order' => $verseOrder,
                    ]);
                }
            }
        }

        foreach ($library['sayings'] as [$text, $source]) {
            Saying::updateOrCreate(['text' => $text], ['source' => $source]);
        }
    }
}
