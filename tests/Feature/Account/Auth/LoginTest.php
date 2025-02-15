<?php

namespace Tests\Feature\Account\Auth;

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
    }

    public function test_it_directs_to_vatsim_sso()
    {
        config()->set('vatsim-connect.base', 'https://my-oauth-url.com');
        config()->set('vatsim-connect.id', 12345);
        config()->set('vatsim-connect.scopes', explode(',', 'my-first,my-second,my-third'));

        $this->assertGuest();

        $this->get(route('login'))
            ->assertRedirect();
        $this->post(route('login'))
            ->assertRedirect();

        $redirectUrl = $this->get(route('login'))
            ->headers->get('location');

        $this->assertStringContainsString(config('services.vatsim-net.connect.base'), $redirectUrl);
        $this->assertStringContainsString('state', $redirectUrl);
        $this->assertStringContainsString('scope='.implode('%20', config('services.vatsim-net.connect.scopes')), $redirectUrl);
        $this->assertStringContainsString('response_type=code', $redirectUrl);
        $this->assertStringContainsString('redirect_uri='.urlencode(route('login.post')), $redirectUrl);
        $this->assertStringContainsString('client_id='.config('services.vatsim-net.connect.id'), $redirectUrl);
    }

    public function test_it_redirects_without_vatsim_sso_on_secondary_login()
    {
        $this->assertFalse(Auth::guard('vatsim-sso')->check());
        $this->post(route('auth-secondary'))
            ->assertRedirect(route('landing'));
        $this->get(route('auth-secondary'))
            ->assertRedirect(route('landing'));
    }

    public function test_it_logs_a_user_out()
    {
        $this->actingAs($this->account);
        $this->assertAuthenticatedAs($this->account);

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }

    public function test_it_logs_a_user_out_of_the_vatsim_sso_guard()
    {
        $this->actingAs($this->account, 'vatsim-sso');
        $this->assertAuthenticatedAs($this->account, 'vatsim-sso');

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }
}
