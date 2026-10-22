<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MarketingSiteTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function pageProvider(): array
    {
        return [
            'home' => ['/'],
            'how it works' => ['/how-it-works'],
            'features' => ['/features'],
            'faq' => ['/faq'],
            'about' => ['/about'],
            'contact' => ['/contact'],
            'privacy' => ['/privacy'],
            'terms' => ['/terms'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_marketing_pages_render_successfully(string $path): void
    {
        $response = $this->get($path);

        $response->assertOk();
        $response->assertSee('Packer', false);
    }

    public function test_navigation_links_point_to_real_routes(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('how-it-works'), false);
        $response->assertSee(route('features'), false);
        $response->assertSee(route('about'), false);
        $response->assertSee(route('contact'), false);
        $response->assertSee(route('privacy'), false);
        $response->assertSee(route('terms'), false);
    }

    public function test_contact_form_rejects_empty_submission(): void
    {
        $response = $this->from('/contact')->post('/contact', []);

        $response->assertRedirect('/contact');
        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_contact_form_accepts_a_valid_submission(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Dana Traveller',
            'email' => 'dana@example.com',
            'company' => 'Skyline Handling',
            'message' => 'We move about 400 bags a day and would like to see how tracking would work.',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('status');
    }
}
