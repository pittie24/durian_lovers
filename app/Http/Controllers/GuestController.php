<?php

namespace App\Http\Controllers;

class GuestController extends Controller
{
    public function landing()
    {
        return view('guest.landing');
    }
}
