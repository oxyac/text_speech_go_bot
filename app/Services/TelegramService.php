<?php

namespace App\Services;

use App\Jobs\ProcessUpdate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class TelegramService
{
    protected string $botName;

    public function init(string $botName = '')
    {
        $this->botName = $botName;

    }

    public function run()
    {
        $timeIterationStart = microtime(true);
        while (true) {
            $updates = $this->getUpdates();
            foreach ($updates as $update) {
                $this->queueCommand($update);
            }
            Log::debug('Check time: ' . time());
        }
    }

    private function setLastUpdateId($updateId)
    {
        Cache::put(config('cache.keys.telegram_update'), $updateId);
    }

    private function getLastUpdateId()
    {
        return Cache::get(config('cache.keys.telegram_update'));
    }

    /**
     * @return Update[]
     */
    private function getUpdates(): array
    {
        $lastUpdateId = $this->getLastUpdateId();
        try{
            $updates = Telegram::bot($this->botName)->getUpdates([
                'allowed_updates' => ["message", "callback_query"],
                'timeout'         => 10,
                'offset'          => $lastUpdateId
            ]);

        } catch(TelegramSDKException $exception){
            Log::error($exception->getMessage());
            $updates = [];
        }

        if ($lastUpdate = end($updates)) {
            $lastUpdateId = $lastUpdate->updateId;
            $this->setLastUpdateId(++$lastUpdateId);
        }
        return $updates;
    }

    private function queueCommand(Update $update)
    {
        $botName = $this->botName;
        ProcessUpdate::dispatch($update, $botName);
    }

    private function speedControl(&$iterationStartTime): void
    {
        $usleepControl = 1000000 - (microtime(true) - $iterationStartTime) * 1000000;
        if ($usleepControl < 0) {
            $usleepControl = 0;
        }
        usleep($usleepControl);
        $iterationStartTime = microtime(true);
    }
}
