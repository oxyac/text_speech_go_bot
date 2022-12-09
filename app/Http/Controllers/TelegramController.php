<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handle(Request $request){

    }

    public function getMe(Request $request){
        $response = Telegram::getMe();
        return $response;
    }
}
