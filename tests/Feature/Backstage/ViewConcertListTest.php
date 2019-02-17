<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        Collection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value),
                'Failed asserting that the collection contained the specified value.');
        });

        Collection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value),
                'Failed asserting that the collection did not contained the specified value.');
        });

        Collection::macro('assertEquals', function ($items) {
            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

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
        $concerts = factory(Concert::class, 3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);
        $this->assertTrue($response->original->getData()['concerts']->contains($concerts[0]));
        $this->assertTrue($response->original->getData()['concerts']->contains($concerts[1]));
        $this->assertTrue($response->original->getData()['concerts']->contains($concerts[2]));
    }

    /** @test */
    function promoters_can_only_view_a_list_of_their_own_concert()
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
