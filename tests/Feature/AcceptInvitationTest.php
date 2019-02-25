<?php

namespace Tests\Feature;

use App\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function viewing_an_unused_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }
}
