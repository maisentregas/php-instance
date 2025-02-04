<?php

namespace App\Services;

use App\Repositories\OpenAiRepository;

class OpenAiService
{
    private $openAiRepository;

    public function __construct()
    {
        $this->openAiRepository = new OpenAiRepository();
    }

    // ...
}