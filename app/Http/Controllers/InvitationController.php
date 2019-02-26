<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function show($code)
    {
        $invitation = Invitation::findByCode($code);

        if ($invitation->hasBeenUsed()) {
            abort(404);
        }

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }
}
