<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
class CommentFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_comment_form_validation()
    {
        $response = $this->withoutMiddleware(VerifyCsrfToken::class)
            ->post('/comments', [
            'user_name' => '', // має бути помилка, поле порожнє
            'email' => 'wrong-email-format',
            'text' => '', // теж обов'язкове
            // captcha спеціально пропущена
        ]);

        $response->assertSessionHasErrors(['user_name', 'email', 'text', 'g-recaptcha-response']);
    }
}
