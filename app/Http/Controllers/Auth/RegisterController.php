<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use App\Rules\Recaptcha;
use App\Services\EmailValidationService;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;
    protected $emailValidationService;

    public function __construct(EmailValidationService $emailValidationService)
    {
        $this->middleware('guest');
        $this->emailValidationService = $emailValidationService;
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'g-recaptcha-response' => new Recaptcha(),
        ]);
    }

    public function register(Request $request)
    {
        $validate = $this->validator($request->all());

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => view('Frontend::components.alert', ['type' => 'danger', 'message' => $validate->errors()->first()])->render()
            ]);
        }

        $email = $request->input('email');
        $result = $this->emailValidationService->validateEmail($email);

        Log::info($result);

        if ($result['result'] === 'risky' || $result['result'] === 'undeliverable' || $result['result'] === 'unknown' || $result['disposable'] || $result['reason'] === 'invalid_email' || $result['reason'] === 'rejected_email' || $result['reason'] === 'rejected_domain' || $result['reason'] === 'invalid_domain' || $result['reason'] === 'invalid_smtp' || $result['reason'] === 'unavailable_smtp') {
            return response()->json([
                'status' => false,
                'message' => view('Frontend::components.alert', ['type' => 'danger', 'message' => 'Email addresses are not allowed.'])->render()
            ]);
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        RoleUser::create([
            'role_id' => 3,
            'user_id' => $user['id']
        ]);

        return response()->json([
            'status' => true,
            'message' => view('Frontend::components.alert', ['type' => 'success', 'message' => __('Register successfully')])->render(),
            'redirect' => dashboard_url('')
        ]);
    }

    public function verifyRecaptcha(Request $request)
    {
        dd(session('register_data', []));
        $validate = $request->validate([
            'g-recaptcha-response' => ['required', new Recaptcha],
        ]);

        // if ($request->session->exists('register_data'))
        $data = session('register_data');

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Session data not found. Please try again.',
            ]);
        }

        event(new Registered($user = $this->create($data)));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }
        // $request->session()->forget('register_data');

        return response()->json([
            'status' => true,
            'message' => view('Frontend::components.alert', ['type' => 'success', 'message' => __('Register successfully')])->render(),
            'redirect' => url('/')
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
