<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Language;

class ActionService
{

    public function __construct(private $chatId)
    {
    }

    public function setLangByCode($code) {
        $chatModel = Chat::where(['id' => $this->chatId])->first();
            $chatModel->language_id = Language::where('code', $code)->first()->id;
        $chatModel->save();
    }

    public function setVoiceById($code) {
        $chatModel = Chat::where(['id' => $this->chatId])->first();
        $chatModel->language_id = Language::where('id', $code)->first()->id;
        $chatModel->save();
    }
}
