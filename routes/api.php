<?php

use App\Http\Commands\StartCommand;
use App\Http\Controllers\TelegramController;
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
