<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmailValidationService
{
  protected $apiKey;

  public function __construct()
  {
    $this->apiKey = env('KICKBOX_API_KEY');
  }

  public function validateEmail($email)
  {
    $response = Http::get('https://api.kickbox.com/v2/verify', [
      'email' => $email,
      'apikey' => $this->apiKey
    ]);

    return $response->json();
  }
}
