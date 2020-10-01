<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function show() {
        $user = User::first();
        return $user


    }

}
