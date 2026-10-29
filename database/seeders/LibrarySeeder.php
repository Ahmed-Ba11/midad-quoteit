<?php

namespace Database\Seeders;

use App\Models\Poet;
use App\Models\Saying;
use App\Models\Verse;
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
                'wikisource_url' => $this->wikisourceUrl($poetData['wikisource'] ?? null),
                'aldiwan_url' => isset($poetData['aldiwan']) ? 'https://www.aldiwan.net/'.$poetData['aldiwan'] : null,
                'sort_order' => $poetOrder,
            ]);

            foreach ($poetData['works'] as $workOrder => $workData) {
                $work = $poet->works()->updateOrCreate(['slug' => $workData['slug']], [
                    ...collect($workData)->only(['title', 'type', 'color', 'ink'])->all(),
                    'wikisource_url' => $this->wikisourceUrl($workData['wikisource'] ?? null),
                    'sort_order' => $workOrder,
                ]);

                foreach ($workData['verses'] as $verseOrder => $verse) {
                    [$sadr, $ajuz, $occasion] = $verse;
                    $work->verses()->updateOrCreate(['sadr' => $sadr], [
                        'ajuz' => $ajuz,
                        'occasion' => $occasion,
                        'source_url' => $this->wikisourceUrl($verse[3] ?? null),
                        'sort_order' => $verseOrder,
                    ])->refreshSearchText();
                }
            }
        }

        // شرح البيت: a plain meaning and the meanings of its difficult words (written for مِداد).
        foreach ($library['explanations'] ?? [] as $sadr => $explanation) {
            Verse::where('sadr', $sadr)->update([
                'explanation' => $explanation['meaning'],
                'glossary' => json_encode(collect($explanation['words'])
                    ->map(fn (string $meaning, string $word) => ['word' => $word, 'meaning' => $meaning])
                    ->values(), JSON_UNESCAPED_UNICODE),
            ]);
        }

        foreach ($library['sayings'] as [$text, $source]) {
            Saying::updateOrCreate(['text' => $text], ['source' => $source]);
        }
    }

    /**
     * A Wikisource page title (e.g. "معلقة امرئ القيس") as a link.
     */
    private function wikisourceUrl(?string $title): ?string
    {
        return $title ? 'https://ar.wikisource.org/wiki/'.rawurlencode(str_replace(' ', '_', $title)) : null;
    }
}
