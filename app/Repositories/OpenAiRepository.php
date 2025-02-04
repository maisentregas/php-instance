<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;

class OpenAiRepository
{
    private $apiKey, $assistantsVersion = 'v2', $url = 'https://api.openai.com/v1';

    public function __construct()
    {
        $openaiEnabled = true; // @todo: check if OpenAI is enabled

        if(!$openaiEnabled) {
            throw new \Exception("OpenAI is not enabled");
        }

        $this->apiKey = '';
    }

    public function getHttpWithHeaders($canThrowException = true)
    {
        if($canThrowException && empty($this->apiKey)) {
            throw new \Exception("OpenAI API Key not found");
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
            'OpenAI-Beta' => 'assistants=' . $this->assistantsVersion,
        ]);
    }

    // ...
}