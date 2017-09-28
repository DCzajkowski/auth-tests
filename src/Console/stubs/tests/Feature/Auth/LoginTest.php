<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Tests\Feature\MakesRequestsFromPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase, MakesRequestsFromPage;

    public function testUserCanViewALoginForm()
    {
        $response = $this->get(route('login'));

        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    public function testUserCannotViewALoginFormWhenAuthenticated()
    {
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('home'));
    }

    public function testUserCanLoginWithCorrectCredentials()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function testUserCannotLoginWithIncorrectPassword()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('i-love-laravel'),
        ]);

        $response = $this->fromPage(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCannotLoginWithEmailThatDoesNotExist()
    {
        $response = $this->fromPage(route('login'))->post(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function testUserCanLogout()
    {
        $this->be(factory(User::class)->create());

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function testUserCannotLogoutWhenNotAuthenticated()
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function testUserCannotMakeMoreThanFiveAttemptsInOneMinute()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        foreach (range(0, 5) as $_) {
            $response = $this->fromPage(route('login'))->post(route('login'), [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);
        }

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertContains(
            'Too many login attempts.',
            collect($response
                ->baseResponse
                ->getSession()
                ->get('errors')
                ->getBag('default')
                ->get('email')
            )->first()
        );
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
