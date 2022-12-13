<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class KeyboardService
{
    static function getMainMenuBoard($chatId)
    {
        $chat = Chat::where(['id' => $chatId])->first();

        $text = " Welcome to another <b> TTS bot </b> ! \n\n";
        $text .= "Welcome back $chat->first_name ! \n";
        $text .= "Enter any text to generate audio\n\n";
        $text .= " <a href='https://www.pngall.com/wp-content/uploads/2/Sound-Waves-PNG-Download-Image.png'>&#8205;</a>";

        $keyboard = [
            [
                [
                    'text'          => 'Settings',
                    'callback_data' => 'SETTINGS NULL'
                ],
                [
                    'text'          => 'Stats',
                    'callback_data' => 'STATS NULL'
                ]
            ]
        ];

        return ['text' => $text, 'keyboard' => json_encode(['inline_keyboard' => $keyboard])];
    }

    static function getSelectLangBoard($chatId)
    {
        $text = self::getMessageByChatId($chatId);
        $languages = Language::query()->select(['name', 'code'])->groupBy('name', 'code')->get();
        $keyboard = [];
        $buttons = [];
        foreach ($languages as $i => $language) {
            Log::debug($language->toJson());
            $buttons[] = ['text' => $language->name, 'callback_data' => 'LANG ' . $language->code];
            if ($i++ > 0 && $i % 3 == 0) {
                $keyboard[] = $buttons;
                $buttons = [];
            }
        }
        if ($buttons) {
            $keyboard[] = $buttons;
        }
        self::addBackButton($keyboard, 'getSettingsBoard');

        return ['text' => $text, 'keyboard' => json_encode(['inline_keyboard' => $keyboard])];
    }

    public static function getMessageByChatId($chatId)
    {
        return Cache::get(config('cache.keys.telegram_chat_message') . $chatId);
    }

    private static function addBackButton(array &$keyboard, $location)
    {
        $keyboard[] = [
            [
                'text' => 'Back', 'callback_data' => "RETURN $location"
            ]
        ];
    }

    static function getSelectVoiceBoard($chatId)
    {
        $text = self::getMessageByChatId($chatId);
        $voices = Language::query()->select(['languages.voice_code', 'languages.voice_name'])->whereIn(
            'code', function ($query) use ($chatId) {
            $query->select(['languages.code'])->from('chats')
                ->join('languages', 'chats.language_id', '=', 'languages.id')
                ->where('chats.id', $chatId);
        });
        Log::debug($voices->toSql());
        $voices = $voices->get();
        $keyboard = [];
        $buttons = [];
        foreach ($voices as $i => $voice) {
            Log::debug($voice->toJson());
            $buttons[] = ['text' => $voice->voice_name, 'callback_data' => 'VOICE ' . $voice->voice_code];
            if ($i > 0 && $i % 2 == 0) {
                $keyboard[] = $buttons;
                $buttons = [];
            }
        }
        if ($buttons) {
            $keyboard[] = $buttons;
        }

        self::addBackButton($keyboard, 'getSettingsBoard');

        return ['text' => $text, 'keyboard' => json_encode(['inline_keyboard' => $keyboard])];
    }

    public static function getReturnKeyboard(string $location, $args)
    {
        return forward_static_call(self::class . "::$location", $args);
    }

    public static function getSettingsBoard($chatId)
    {

        $text = "Language: {LANG}\n";
        $text .= "Voice: {VOICE}";

        $settings = Chat::query()->select(['languages.name', 'languages.voice_name'])
            ->join('languages', 'chats.language_id', '=', 'languages.id')
            ->where(['chats.id' => $chatId])->first();

        if (!$settings) {
            $text = str_replace('{LANG}', 'Not set', $text);
            $text = str_replace('{VOICE}', 'Not set', $text);
        } else {
            $text = str_replace('{LANG}', $settings->name, $text);
            $text = str_replace('{VOICE}', $settings->voice_name, $text);
        }

        self::setMessageByChatId($chatId, $text);

        $keyboard = [
            [
                [
                    'text'          => 'Language',
                    'callback_data' => 'SETTINGS LANG'
                ],
                [
                    'text'          => 'Voice',
                    'callback_data' => 'SETTINGS VOICE'
                ]
            ]
        ];

        self::addBackButton($keyboard, 'getMainMenuBoard');

        return ['text' => $text, 'keyboard' => json_encode(['inline_keyboard' => $keyboard])];
    }

    public static function setMessageByChatId(string $chatId, $message)
    {
        Cache::put(config('cache.keys.telegram_chat_message') . $chatId, $message, 3600);
    }

    public static function getStatsBoard($chatId)
    {
        $text = "Users: {USERS}\n";
        $users = Chat::query()->count();
        $text = str_replace('{USERS}', $users, $text);
        $keyboard = [];
        self::addBackButton($keyboard, 'getMainMenuBoard');
        return ['text' => $text, 'keyboard' => json_encode(['inline_keyboard' => $keyboard])];
    }
}
