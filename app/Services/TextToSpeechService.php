<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Telegram\Bot\FileUpload\InputFile;

class TextToSpeechService
{

    /*http://localhost:5500/api/tts?
    voice=espeak%3Aen&
    text=Welcome%20to%20the%20world%20of%20speech%20synthesis%21&vocoder=high&denoiserStrength=0.03&cache=false*/
    public function generateAudioFromText(string $chatId, string $message): InputFile
    {

        $language = Language::query()->select(['languages.voice_code'])
            ->join('chats', 'chats.language_id', '=', 'languages.id')
            ->where('chats.id', $chatId)->first();
        $params = [
            'voice' => $language->voice_code,
            'text' => $message,
        ];

        $url = "http://localhost:5500/api/tts?" . http_build_query($params);
        $response = Http::get($url);
        $filename =  'tmp/' . Str::uuid() . '.wav';
        Storage::disk()->put('/' . $filename, $response->body());
        return InputFile::create( storage_path('app/' . $filename), 'TTS GO BOT');
    }
}
