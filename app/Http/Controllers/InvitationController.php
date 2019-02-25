<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function show($code)
    {
        $invitation = Invitation::findByCode($code);

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }
}
