<?php
namespace App\Services\Tenant;

use App\Repositories\Tenant\TenantUserRepository;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class TenantUserService
{
    protected TenantUserRepository $userRepo;

    public function __construct(TenantUserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data): TenantUser
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepo->create($data);
    }

    public function updateProfile(TenantUser $user, array $data): bool
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepo->update($user, $data);
    }

    public function handleUserLogin(Request $request, string $email): ?string
    {
        // 1. 刷新 Session 会话
        $request->session()->regenerate();

        // 2. 签发 API Token 
        $user = TenantUser::where('email', $email)->first();
        if ($user) {
            $token = $user->createToken('tenant-api-token')->plainTextToken;
            // 如果后续想塞进 session，可以直接在这里操作：
            // session(['api_token' => $token]);
            return $token;
        }
        
        return null;
    }

    public function handleUserLogout(Request $request): void
    {
        // 1. 销毁当前 Web 用户的 API Token
        $user = Auth::guard('web')->user();
        if ($user) {
            $tenantUser = TenantUser::find($user->id);
            if ($tenantUser) {
                $tenantUser->tokens()->delete();
            }
        }

        // 2. 退出 Web 登录状态
        Auth::guard('web')->logout();

        // 3. 彻底作废当前会话并刷新 CSRF Token
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}