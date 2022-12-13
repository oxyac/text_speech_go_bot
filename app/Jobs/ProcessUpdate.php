<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Language;
use App\Services\KeyboardService;
use App\Services\TelegramService;
use App\Services\TextToSpeechService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use Throwable;

class ProcessUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @param Update $telegramUpdate
     * @param string $botName
     */
    public function __construct(private Update $telegramUpdate, private string $botName)
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $start = microtime(true);
        $bot = Telegram::bot($this->botName);
        $message = $this->telegramUpdate->getMessage();
        $type = $this->telegramUpdate->objectType();
        Log::debug("MESSAGE $type RECEIVED:" . json_encode($message));
        if ($type === 'message') {
            if ($this->telegramUpdate->getMessage()->has('entities')) {
                $bot->processCommand($this->telegramUpdate);
            } else {
                $this->processMessage();
            }
        } elseif ($type === 'callback_query') {
/*            Log::debug('MESSAGE DATA:' . $this->telegramUpdate->callbackQuery->data);*/
            $bot->answerCallbackQuery(['callback_query_id' => $this->telegramUpdate->callbackQuery->id]);
            $this->processCallback();
        }
Log::debug('MESSAGE IN ASYNC TIME:' . microtime(true) - $start);
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        try {
            Telegram::bot($this->botName)->sendMessage([
                'text'    => 'Sorry, an error occurred',
                'chat_id' => $this->telegramUpdate->getChat()->id
            ]);
        } catch (TelegramSDKException $e) {
            Log::critical($e->getMessage());
        }

        Log::critical($exception->getMessage());
    }

    private function processCallback()
    {

        $dataString = $this->telegramUpdate->callbackQuery->data;
        $chatId = $this->telegramUpdate->getChat()->id;
        $messageId = $this->telegramUpdate->getMessage()->messageId;
        $data = explode(' ', $dataString);
        $action = $data[0];
        $value = $data[1];

        Log::debug("CALLBACK CHAT_ID:$chatId ACTION:$action VALUE:$value MESSAGE_ID:$messageId");

        switch ($action) {
            case 'SETTINGS':
                if ($value === 'LANG') {
                    $keyboard = KeyboardService::getSelectLangBoard($chatId);
                } elseif ($value === 'VOICE') {
                    $keyboard = KeyboardService::getSelectVoiceBoard($chatId);
                } else {
                    $keyboard = KeyboardService::getSettingsBoard($chatId);
                }

                Telegram::bot($this->botName)->editMessageText([
                    'parse_mode'   => 'HTML',
                    'text'         => $keyboard['text'],
                    'reply_markup' => $keyboard['keyboard'],
                    'chat_id'      => $chatId,
                    'message_id'   => $messageId,
                ]);

                break;
            case 'LANG':
            case 'VOICE':
                $chatModel = Chat::where(['id' => $chatId])->first();
                if ($action === 'LANG') {
                    $chatModel->language_id = Language::where('code', $value)->first()->id;
                } else {
                    $chatModel->language_id = Language::where('voice_code', $value)->first()->id;
                }

                $chatModel->save();
                $keyboard = KeyboardService::getSettingsBoard($chatId);

                Telegram::bot($this->botName)->editMessageText([
                    'parse_mode'   => 'HTML',
                    'text'         => $keyboard['text'],
                    'reply_markup' => $keyboard['keyboard'],
                    'chat_id'      => $chatId,
                    'message_id'   => $messageId,
                ]);
                break;
            case 'STATS':
                $keyboard = KeyboardService::getStatsBoard($chatId);
                Telegram::bot($this->botName)->editMessageText([
                    'parse_mode'   => 'HTML',
                    'text'         => $keyboard['text'],
                    'reply_markup' => $keyboard['keyboard'],
                    'chat_id'      => $chatId,
                    'message_id'   => $messageId,
                ]);
                break;
            case 'RETURN':
                Log::debug($chatId);

                $keyboard = KeyboardService::getReturnKeyboard($value, $chatId);
                Telegram::bot($this->botName)->editMessageText([
                    'parse_mode'   => 'HTML',
                    'text'         => $keyboard['text'],
                    'reply_markup' => $keyboard['keyboard'],
                    'chat_id'      => $chatId,
                    'message_id'   => $messageId,
                ]);
                break;
        }
    }

    public function processMessage()
    {
        $message = $this->telegramUpdate->getMessage()->text;
        $chatId = $this->telegramUpdate->getChat()->id;

        Log::debug("MESSAGE CHAT_ID:$chatId MESSAGE:$message");

        /** @var TextToSpeechService $textToSpeechService */
        $textToSpeechService = App::make(TextToSpeechService::class);
        $audio = $textToSpeechService->generateAudioFromText($chatId, $message);
        Telegram::bot($this->botName)->sendAudio([
            'parse_mode' => 'HTML',
            'audio'         => $audio,
            'caption' => 'Generated by <a href="https://t.me/text_speech_go_bot">text_speech_go_bot</a>',
            'chat_id'      => $chatId,
        ]);
    }


}
