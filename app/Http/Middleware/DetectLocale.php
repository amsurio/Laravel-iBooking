<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Services\GeoLocationService;

class DetectLocale
{
    protected $geoLocationService;

    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $location = $this->geoLocationService->getLocation($ip);
        $countryCode = $location->country;

        $mappings = config('location.mappings');
        $default = config('location.default');

        dd($countryCode);

        $locale = $mappings[$countryCode] ?? $default;

        App::setLocale($locale['language']);
        Session::put('currency', $locale['currency']);

        return $next($request);
    }
}
