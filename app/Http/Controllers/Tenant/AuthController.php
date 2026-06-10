<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Repositories\Tenant\TenantUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Tenant\User as TenantUser;

class AuthController extends Controller
{
    protected TenantUserRepository $userRepo;
    public function __construct(TenantUserRepository $userRepo) { $this->userRepo = $userRepo; }

    public function showLogin() { return Inertia::render('Tenant/Auth/Login'); }
    public function showRegister() { return Inertia::render('Tenant/Auth/Register'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $this->userRepo->create($data);
        return redirect()->route('tenant.login')->with('success', 'Registration complete. Please log in.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 🎯 修复核心：从当前租户数据库的上下文中精准捞出带有 HasApiTokens 的租户 User 模型
            $user = TenantUser::where('email', $request->email)->first();
            
            if ($user) {
                $token = $user->createToken('tenant-api-token')->plainTextToken;
                // 如果你想把 token 塞进 Session 传给 Inertia 页面，可以解开下一行：
                // session(['api_token' => $token]);
            }

            return redirect()->intended('/products');
        }
        return back()->withErrors(['email' => 'Invalid email or password credentials.']);
    }

    public function logout(Request $request)
    {
        // 1. 安全捞出当前登录的租户用户，并加上 null 检查
        // 显式指定从 'web' 门禁捞人，防止从 API Guard 捞出来一个不支持 Token 的实例
        $user = Auth::guard('web')->user();
        
        if ($user) {
            // 通过租户 User 查询模型来删 Token
            $tenantUser = TenantUser::find($user->id);
            if ($tenantUser) {
                $tenantUser->tokens()->delete(); // 彻底销毁当前租户名下的所有 API Token
            }
        }

        // 2. 🎯 修复核心：显式指定使用 'web' Guard 来退出 Session 状态
        // 这样 Laravel 调用的就是 SessionGuard::logout()，绝不会再报 RequestGuard 找不到方法的错误
        Auth::guard('web')->logout();

        // 3. 按照 Laravel 标准安全规范，作废当前 Session 并刷新 CSRF Token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4. 重定向回当前子域名的登录页
        return redirect()->route('tenant.login');
    }

    public function editProfile()
    {
        return Inertia::render('Tenant/Auth/Profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        $this->userRepo->update($user, $data);
        return back()->with('success', 'Profile updated effectively.');
    }
}