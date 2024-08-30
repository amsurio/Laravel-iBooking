<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\EmailValidationService;
use Illuminate\Support\Facades\Log;

class CheckTemporaryEmail
{
    protected $emailValidationService;

    public function __construct(EmailValidationService $emailValidationService)
    {
        $this->emailValidationService = $emailValidationService;
    }

    public function handle(Request $request, Closure $next)
    {
        $email = $request->input('email');
        $result = $this->emailValidationService->validateEmail($email);

        Log::info($result);

        if ($result['result'] === 'risky' || $result['result'] === 'undeliverable' || $result['result'] === 'unknown' || $result['disposable'] || $result['reason'] === 'invalid_email' || $result['reason'] === 'rejected_email' || $result['reason'] === 'rejected_domain' || $result['reason'] === 'invalid_domain' || $result['reason'] === 'invalid_smtp' || $result['reason'] === 'unavailable_smtp') {
            return response()->json([
                'status' => false,
                'message' => view('Frontend::components.alert', ['type' => 'danger', 'message' => 'Email addresses are not allowed.'])->render()
            ]);
        }

        return $next($request);
    }
}
