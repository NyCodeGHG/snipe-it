<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EnforceApiUserAgentTest extends TestCase
{
    public function test_setting_off_allows_any_user_agent()
    {
        $this->settings->set(['require_user_agent_on_api' => '0']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', 'curl/8.5.0')
            ->getJson(route('api.users.selectlist'))
            ->assertOk();
    }

    public function test_setting_on_blocks_empty_user_agent()
    {
        $this->settings->set(['require_user_agent_on_api' => '1']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', '')
            ->getJson(route('api.users.selectlist'))
            ->assertForbidden()
            ->assertJson(['status' => 'error']);
    }

    public function test_setting_on_blocks_curl()
    {
        $this->settings->set(['require_user_agent_on_api' => '1']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', 'curl/8.5.0')
            ->getJson(route('api.users.selectlist'))
            ->assertForbidden();
    }

    public function test_setting_on_blocks_postman()
    {
        $this->settings->set(['require_user_agent_on_api' => '1']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', 'PostmanRuntime/7.32.1')
            ->getJson(route('api.users.selectlist'))
            ->assertForbidden();
    }

    public function test_setting_on_blocks_python_requests()
    {
        $this->settings->set(['require_user_agent_on_api' => '1']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', 'python-requests/2.31.0')
            ->getJson(route('api.users.selectlist'))
            ->assertForbidden();
    }

    public function test_setting_on_allows_browser_user_agent()
    {
        $this->settings->set(['require_user_agent_on_api' => '1']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
            ->getJson(route('api.users.selectlist'))
            ->assertOk();
    }

    public function test_setting_on_allows_custom_non_generic_user_agent()
    {
        $this->settings->set(['require_user_agent_on_api' => '1']);

        Passport::actingAs(User::factory()->superuser()->create());

        $this->withHeader('User-Agent', 'AcmeCorp-Internal-Integration/1.0')
            ->getJson(route('api.users.selectlist'))
            ->assertOk();
    }
}
