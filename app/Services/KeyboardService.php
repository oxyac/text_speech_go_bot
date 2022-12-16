<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Language;
use App\Models\Menu;
use Illuminate\Support\Facades\App;
use Telegram\Bot\Objects\Update;

class KeyboardService
{

    public function __construct(private readonly Update $update)
    {
    }

    public function getBoard()
    {
        $dataString = $this->update->callbackQuery->data;
        $data = explode(' ', $dataString);
        $board = $data[0];
        if(isset($data[1])){
            $action = $data[1];
            $variables = $data[2];
            /* @var $actionService ActionService */
            $actionService = App::makeWith(ActionService::class, ['chatId' => $this->update->getChat()->id]);
            call_user_func([$actionService, $action], $variables);
        }

        return call_user_func([$this, $board]);
    }

    private function getMainMenuBoard(): Menu
    {
        return self::getMainMenuBoardStatic($this->update->getChat()->id);
    }

    public static function getMainMenuBoardStatic($chatId): Menu
    {
        $chat = Chat::where(['id' => $chatId])->first();

        $text = " Welcome to another <b> TTS bot </b> ! \n\n";
        $text .= "Welcome back  ! \n"; //$chat->first_name
        $text .= "Enter any text to generate audio\n\n";
        $text .= " <a href='https://www.pngall.com/wp-content/uploads/2/Sound-Waves-PNG-Download-Image.png'>&#8205;</a>";

        $keyboard = [
            [
                [
                    'text' => 'Settings',
                    'callback_data' => 'getSettingsBoard'
                ],
                [
                    'text' => 'Stats',
                    'callback_data' => 'getStatsBoard'
                ]
            ]
        ];

        return new Menu([
            'text' => $text, 'keyboard' => $keyboard
        ]);
    }

    private static function addBackButton(array &$keyboard, $location)
    {
        $keyboard[] = [
            [
                'text' => 'Back', 'callback_data' => "$location"
            ]
        ];
    }

    private function getSelectLangBoard(): Menu
    {
        $text = TextService::getChatSettingsText($this->update->getChat()->id);
        $languages = Language::query()->select(['name', 'code'])->groupBy('name', 'code')->get();
        $keyboard = [];
        $buttons = [];
        foreach ($languages as $i => $language) {
            $buttons[] = ['text' => $language->name, 'callback_data' => 'getMainMenuBoard setLangByCode ' . $language->code];
            if ($i++ > 0 && $i % 3 == 0) {
                $keyboard[] = $buttons;
                $buttons = [];
            }
        }
        if ($buttons) {
            $keyboard[] = $buttons;
        }
        self::addBackButton($keyboard, 'getSettingsBoard');

        return new Menu([
            'text' => $text,
            'keyboard' => $keyboard
        ]);
    }

    function getSelectVoiceBoard(): Menu
    {
        $chatId = $this->update->getChat()->id;
        $text = TextService::getChatSettingsText($chatId);
        $voices = Language::query()->select(['languages.id', 'languages.voice_name'])->whereIn(
            'code', function ($query) use ($chatId) {
            $query->select(['languages.code'])->from('chats')
                ->join('languages', 'chats.language_id', '=', 'languages.id')
                ->where('chats.id', $chatId);
        });
        $voices = $voices->get();
        $keyboard = [];
        $buttons = [];
        foreach ($voices as $i => $voice) {
            $buttons[] = ['text' => $voice->voice_name, 'callback_data' => 'getMainMenuBoard setVoiceById ' . $voice->id];
            if ($i++ > 0 && $i % 3 == 0) {
                $keyboard[] = $buttons;
                $buttons = [];
            }
        }
        if ($buttons) {
            $keyboard[] = $buttons;
        }

        self::addBackButton($keyboard, 'getSettingsBoard');

        return new Menu([
            'text' => $text, 'keyboard' => $keyboard
        ]);
    }

    public function getSettingsBoard(): Menu
    {

        $text = TextService::getChatSettingsText($this->update->getChat()->id);

        $keyboard = [
            [
                [
                    'text' => 'Language',
                    'callback_data' => 'getSelectLangBoard'
                ],
                [
                    'text' => 'Voice',
                    'callback_data' => 'getSelectVoiceBoard'
                ]
            ]
        ];

        self::addBackButton($keyboard, 'getMainMenuBoard');

        return new Menu([
            'text' => $text, 'keyboard' => $keyboard
        ]);
    }

    public function getStatsBoard(): Menu
    {
        $text = "Users: {USERS}\n";
        $users = Chat::query()->count();
        $text = str_replace('{USERS}', $users, $text);
        $keyboard = [];
        self::addBackButton($keyboard, 'getMainMenuBoard');
        return new Menu([
            'text' => $text, 'keyboard' => $keyboard
        ]);
    }
}
