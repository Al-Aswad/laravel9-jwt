<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private $authRepository;
    public function __construct()
    {
        // time zone set ID
        // date_default_timezone_set('Asia/Jakarta');
        $this->authRepository = new AuthRepository();
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $token = $this->authRepository->login($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = AUTH::guard('api')->user();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = AUTH::guard('api')->login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        AUTH::guard('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        // try {
        //     $user = auth('api')->userOrFail();
        //     return response()->json([
        //         'status' => 'success',
        //         'user' => auth('api')->user()
        //         // 'user' => AUTH::guard('api')->user()
        //     ]);
        // } catch (\PHPOpenSourceSaver\JWTAuth\Exceptions\UserNotDefinedException $e) {
        //     // do something
        //     return response()->json([
        //         // 'status' => 'success',
        //         // 'user' => auth('api')->user()
        //         // 'user' => AUTH::guard('api')->user()
        //         'Unauthenticated.'
        //     ]);
        // }
        return response()->json([
            'status' => 'success',
            'user' => Auth::user()
            // 'user' => AUTH::guard('api')->user()
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => AUTH::guard('api')->user(),
            'authorisation' => [
                'token' => AUTH::guard('api')->refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
