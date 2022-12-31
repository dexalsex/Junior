<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use App\Http\Controllers\MailController;
use App\Models\Card;
use App\Models\EmailVerification;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        if (!Auth::check()) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:25',
                'password' => 'required|string|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            if (!$token = auth()->attempt($validator->validated())) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $x = new EmailVerificationController();
            if (!$x->check($request->email)) {
                return response()->json('go verify bitch', 300);
            };
            $token = Auth::attempt($request->except('_token'));
            $data = [
                'token' => $token,
                'user' => Auth::user(),
                'card' => Card::where('user_id', auth()->user()->id)->first()
            ];
            if ($data['user']['role_id'] == 1) {
                Auth::logout();
                return response()->json("Login not allowed", 401);
            }
            return response()->json($data);
        } else {
            die;
        }
    }


    public function register(Request $req)
    {
        $data = $req->all();
        $validator = Validator::make($data, [
            'first_name' => ['required', 'string', 'max:20'],
            'last_name' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:25', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required_with:password', 'string', 'min:8', 'same:password'],
        ]);
        $errors = $validator->errors()->get('email');
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::create([
            'first_name' => $req['first_name'],
            'last_name' => $req['last_name'],
            'email' => $req['email'],
            'password' => Hash::make($req['password']),
            'role_id' => 2,
            'friends' => "example",
        ]);
        $token = Auth::login($user);
        $code = rand(10000, 99999);
        EmailVerification::create([
            'user_id' => $user->id,
            'code' => $code,
        ]);
        $x = new MailController();
        $x->sendcode($code, $user->email);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully, Please check your email for the verification code.',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
