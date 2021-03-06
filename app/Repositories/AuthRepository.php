<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;

class AuthRepository
{
    public function login($credentials)
    {
        return Auth::guard('api')->attempt($credentials);
    }
};
