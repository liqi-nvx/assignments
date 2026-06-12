<?php

namespace Tests\Unit\Tenant;

use Tests\TenantTestCase;
use App\Services\Tenant\TenantUserService;
use App\Repositories\Tenant\TenantUserRepository;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserSessionSecurityTest extends TenantTestCase
{
    protected TenantUserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new TenantUserService(new TenantUserRepository());
    }

    /** @test */
    public function test_it_hashes_password_on_registration_and_issues_sanctum_token_safely()
    {
        $user = $this->userService->register([
            'name'     => 'Mechanic Pro',
            'email'    => 'mechanic@workshop.com',
            'password' => 'plain-password-123'
        ]);

        $this->assertNotEquals('plain-password-123', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plain-password-123', $user->password));

        // 模拟登录请求，验证 Token 签发与会话再生
        $request = Request::create('/login', 'POST');
        $request->setLaravelSession(app('session')->driver('array'));

        $token = $this->userService->handleUserLogin($request, 'mechanic@workshop.com');
        
        $this->assertNotNull($token);
        $this->assertCount(1, $user->fresh()->tokens);
    }

    /** @test */
    public function test_it_wipes_all_api_tokens_and_invalidates_session_on_logout()
    {
        $user = TenantUser::create([
            'name' => 'Goodbye User', 'email' => 'out@test.com', 'password' => '123'
        ]);

        // 预先签发两个不同的接入设备令牌
        $user->createToken('device-a');
        $user->createToken('device-b');
        $this->assertCount(2, $user->tokens);

        // 模拟 Web 登录守卫现状
        Auth::guard('web')->setUser($user);

        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession(app('session')->driver('array'));

        // 执行注销清理机制
        $this->userService->handleUserLogout($request);

        // 验证物理双重擦除：API 令牌数据库清零
        $this->assertCount(0, $user->fresh()->tokens);
        // 验证当前 Web 登录状态已摘除
        $this->assertNull(Auth::guard('web')->user());
    }
}