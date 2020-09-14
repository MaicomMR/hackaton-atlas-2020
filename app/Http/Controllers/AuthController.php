<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Auth\Register;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 604800,
            'type' => auth('api')->user()->roles->pluck('name')->first(),
        ]);
    }

    public function me()
    {
        return response()->json([
            'user' => auth('api')->user(),
            'totalReports' => auth('api')->user()->getTotalReports(auth()->user()->id),
            'type' => auth('api')->user()->roles->pluck('name')->first(),
        ]);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function register(Request $request, Register $registerService)
    {
        $data = $registerService->handle($request);

        return response()->json(['data' => $data], 200);
    }
}
