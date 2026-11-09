<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoadTestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['drop.admin_user' => 'admin', 'drop.admin_password' => 'secret-test']);
    }

    public function test_requires_admin_credentials(): void
    {
        $this->get(route('admin.load-test'))->assertStatus(401);
        $this->post(route('admin.load-test.run'))->assertStatus(401);
    }

    public function test_form_is_available_to_admin_outside_production(): void
    {
        $this->withBasicAuth('admin', 'secret-test')->get(route('admin.load-test'))
            ->assertOk()->assertSee('Lancer le test');
    }

    public function test_disabled_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->withBasicAuth('admin', 'secret-test')->get(route('admin.load-test'))->assertNotFound();
        // En production le contrôle CSRF s'applique aussi aux tests : on fournit un jeton valide.
        $this->withSession(['_token' => 't'])->withBasicAuth('admin', 'secret-test')
            ->post(route('admin.load-test.run'), ['_token' => 't'])->assertNotFound();
    }
}
