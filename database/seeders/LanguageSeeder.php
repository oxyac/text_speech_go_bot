<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $response = Http::timeout(30)->get(config('app.tts.url') . 'voices')->json();

        $lang = [];
        foreach ($response as $key => $item) {
                $lang[] = [
                    'code'       => $item['language'],
                    'name'       => locale_get_display_language($item['language']),
                    'voice_code' => $key,
                    'voice_name' => $item['name'],
                    'gender' => $item['gender']
                ];
        }

        Language::upsert($lang, ['voice_code'], ['code', 'name', 'voice_code', 'voice_name']);
    }
}
