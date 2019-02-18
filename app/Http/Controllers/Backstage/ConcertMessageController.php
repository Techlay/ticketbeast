<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessageController extends Controller
{
    public function create($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);
        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }
}
