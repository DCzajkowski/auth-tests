<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function getValidToken($user)
    {
        return Password::broker()->createToken($user);
    }

    protected function getInvalidToken()
    {
        return 'invalid-token';
    }

    protected function fromPage($uri)
    {
        return $this->withServerVariables(['HTTP_REFERER' => $uri]);
    }

    public function testUserCanViewAPasswordResetForm()
    {
        $user = factory(User::class)->create();

        $response = $this->get(route('password.reset', $token = $this->getValidToken($user)));

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');
        $response->assertViewHas('token', $token);
    }

    public function testUserCannotViewAPasswordResetFormWhenAuthenticated()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('password.reset', $this->getValidToken($user)));

        $response->assertRedirect(route('home'));
    }

    public function testUserCanResetPasswordWithValidToken()
    {
        Event::fake();

        $user = factory(User::class)->create();
        $this->withoutExceptionHandling();
        $response = $this->post('/password/reset', [
            'token' => $this->getValidToken($user),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('new-awesome-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function testUserCannotResetPasswordWithInvalidToken()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('old-password'),
        ]);

        $response = $this->fromPage(route('password.reset', $this->getInvalidToken()))->post('/password/reset', [
            'token' => $this->getInvalidToken(),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect(route('password.reset', $this->getInvalidToken()));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function testUserCannotResetPasswordWithoutProvidingANewPassword()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('old-password'),
        ]);

        $response = $this->fromPage(route('password.reset', $token = $this->getValidToken($user)))->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('password.reset', $token));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function testUserCannotResetPasswordWithoutProvingAnEmail()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('old-password'),
        ]);

        $response = $this->fromPage(route('password.reset', $token = $this->getValidToken($user)))->post('/password/reset', [
            'token' => $token,
            'email' => '',
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect(route('password.reset', $token));
        $response->assertSessionHasErrors('email');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }
}
