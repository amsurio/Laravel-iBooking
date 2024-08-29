<?php

namespace App\Services;

use ipinfo\ipinfo\IPinfo;

class GeoLocationService
{
  protected $ipinfo;

  public function __construct()
  {
    $this->ipinfo = new IPinfo(env('IPINFO_API_KEY'));
  }

  public function getLocation($ip)
  {
    return $this->ipinfo->getDetails($ip);
  }
}
