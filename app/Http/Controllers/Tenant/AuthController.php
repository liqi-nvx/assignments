<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantLoginRequest;
use App\Http\Requests\Tenant\TenantRegisterRequest;
use App\Services\Tenant\TenantUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\Tenant\TenantUpdateProfileRequest;
use App\Models\Tenant\User as TenantUser;

class AuthController extends Controller
{
    protected TenantUserService $userService;

    public function __construct(TenantUserService $userService) 
    { 
        $this->userService = $userService; 
    }

    public function showLogin() {

        return Inertia::render('Tenant/Auth/Login');
    }

    public function showRegister() {

        return Inertia::render('Tenant/Auth/Register');
    }

    public function register(TenantRegisterRequest $request)
    {
        $this->userService->register($request->validated());

        return redirect()->route('tenant.login')->with('success', 'Registration complete. Please log in.');
    }

    public function login(TenantLoginRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $this->userService->handleUserLogin($request, $request->email);

            return redirect()->intended('/products');
        }

        return back()->withErrors(['email' => 'Invalid email or password credentials.']);
    }

    public function logout(Request $request)
    {
        $this->userService->handleUserLogout($request);

        return redirect()->route('tenant.login');
    }

    public function editProfile()
    {
        return Inertia::render('Tenant/Auth/Profile', ['user' => Auth::user()]);
    }

    public function updateProfile(TenantUpdateProfileRequest $request)
    {
        $this->userService->updateProfile(Auth::user(), $request->validated());

        return back()->with('success', 'Profile updated effectively.');
    }
}