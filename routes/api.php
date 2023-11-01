<?php

use App\Http\Commands\StartCommand;
use App\Http\Controllers\TelegramController;
use App\Jobs\ProcessUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/getMe', function (Request $request) {
    return Telegram::getCommands();
})->name('telegram.get_me');


Route::get('/addCommand', function (Request $request) {
    Telegram::addCommand(StartCommand::class);
})->name('telegram.get_me');

Route::get('/setWebhook', function (Request $request) {
    return Telegram::setWebhook([
        'url' => 'https://voice.oxyac.dev/api/' . config('telegram.bots.text_speech_go_bot.webhook_secret') . '/webhook',
        'allowed_updates' => ["message", "callback_query"]
    ]);
})->name('telegram.get_me');


Route::post('/' . config('telegram.bots.text_speech_go_bot.webhook_secret') .'/webhook', function () {
    $update = Telegram::commandsHandler(true);
    // Commands handler method returns the Update object.
    // So you can further process $update object
    // to however you want.
    ProcessUpdate::dispatch($update, 'text_speech_go_bot');

    return 'ok';
});
