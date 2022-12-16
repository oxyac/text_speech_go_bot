<?php

namespace App\Http\Commands;

use App\Models\Chat;
use App\Models\Language;
use App\Services\KeyboardService;
use App\Services\KeyboardService_new;
use Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "start";

    /**
     * @var string Command Description
     */
    protected $description = "Start text to speech bot";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        $userId = $this->getUpdate()->getMessage()->from->id;
//        Log::debug('StartCommand::chatID:' . $chatId . ' userID:' . $userId);
//        Log::debug($this->getUpdate()->getChat()->toJson());

        $chat = Chat::firstOrNew(['id' => $chatId]);
        $chat->first_name = $this->getUpdate()->getChat()->firstName;
        if(!$chat->local_id) {
            $chat->fill($this->getUpdate()->getChat()->toArray());
            $chat->user_id = $userId;
            $chat->save();
        }

        $menu = KeyboardService::getMainMenuBoardStatic($chatId);
        $message = $this->replyWithMessage([
            'parse_mode'   => 'HTML',
            'text'         => $menu->text,
            'reply_markup' => json_encode(['inline_keyboard' => $menu->keyboard])
        ]);
        Log::debug('MESSAGE SENT:' . json_encode($message));
    }
}
