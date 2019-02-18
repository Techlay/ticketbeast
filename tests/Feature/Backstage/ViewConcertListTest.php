<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function guests_cannot_view_a_promoter_concerts_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    function promoters_can_view_a_list_of_their_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $publishedConcertA = factory(Concert::class)->create(['user_id' => $user->id]);
        $publishedConcertA->publish();
        $publishedConcertB = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $publishedConcertB->publish();
        $publishedConcertC = factory(Concert::class)->create(['user_id' => $user->id]);
        $publishedConcertC->publish();

        $unpublishedConcertA = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
        $unpublishedConcertB = factory(Concert::class)->states('unpublished')->create(['user_id' => $otherUser->id]);
        $unpublishedConcertC = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertC,
        ]);

        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertC,
        ]);
    }
}
