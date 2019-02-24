<?php

namespace Tests\Feature\Jobs;

use App\Concert;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_resizes_the_poster_image_to_600px_wide()
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
        );
        $concert = factory(Concert::class)->create(['poster_image_path' => 'posters/example-poster.png']);
        $concert->publish();

        ProcessPosterImage::dispatch($concert);

        $resizeImage = Storage::disk('public')->get('posters/example-poster.png');
        list($width) = getimagesizefromstring($resizeImage);
        $this->assertEquals(600, $width);
    }
}
