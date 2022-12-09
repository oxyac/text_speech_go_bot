<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\FileUpload\InputFile;

class TelegramService
{
    protected Api $bot;

    public function __construct()
    {
        $this->bot = new Api('5924300524:AAH3MIbJH_4Y8uk3zD3TSB6bfg6XgBeU-Gk');
    }


    private function notifyTelegramChannels(Model $model)
    {
        $text = "<b> {$model->getAttribute('title')} </b>\n";
        $text .= $model->getAttribute('body');
//        $text .= "</div>";

        $file = InputFile::create('/home/og/Downloads/inspiring-cinematic-ambient-116199.mp3', 'Inspire me');

        $keyboard = [
            'keyboard' => [
                ['7', '8', '9'],
                ['4', '5', '6'],
                ['1', '2', '3'],
                ['0']
            ],
            'one_time_keyboard' => true,
            'resize_keyboard' => true,
            'input_field_placeholder' => 'Guess my number'
        ];

        $replyKeyboard = json_encode( $keyboard );
        try {
            echo json_encode($this->bot->
//            setAsyncRequest(true)->
            sendMessage([
                'chat_id' => '-863583783',
                'parse_mode' => 'HTML',
                'text' => $text,
                'reply_markup' => $replyKeyboard
            ]));



//            $this->bot->
//            sendAudio([
//                'chat_id' => '-863583783',
//                'audio' => $file,
//                'caption' => 'best music on the block'
//            ]);

//            $this->bot->sendSticker(
//                [
//                    'chat_id' => '-863583783',
//                    'sticker' => 'CAACAgIAAxkBAAMKYzn4jL5jXFlokxHPZYicrubaFFoAAncAAwH12y4yxI16yOJb6SoE'
//                ]
//            );
        } catch (TelegramSDKException $e) {
            echo $e->getMessage();
        }
    }

}
