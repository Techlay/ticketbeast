<?php

namespace App;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator, InvitationCodeGenerator
{
    public function generate()
    {
        $pool = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
