<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Language;

class TextService
{

    public static function getChatSettingsText($chatId): string
    {
        $text = "Language: {LANG}\n";
        $text .= "Voice: {VOICE}";

        $settings = Chat::query()->select(['languages.name', 'languages.voice_name'])
            ->join('languages', 'chats.language_id', '=', 'languages.id')
            ->where(['chats.id' => $chatId])->first();


        $text = str_replace('{LANG}', $settings->name, $text);
        $text = str_replace('{VOICE}', $settings->voice_name, $text);

        return $text;
    }

}
