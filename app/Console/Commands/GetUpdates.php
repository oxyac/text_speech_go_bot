<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class GetUpdates extends Command implements Isolatable
{

    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:update {bot}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start getUpdates worker';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $input = $this->argument('bot');;
        $botName = config('telegram.bots.' . $input);
        if(!$botName){
            return 1;
        }

        $this->telegramService->init($input);
        $this->telegramService->run();

        return 0;
    }
}
