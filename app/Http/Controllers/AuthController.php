<?php

namespace App\Http\Controllers;

use App\DTOs\AuthDTO;
use App\Http\Resources\UserResource;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

use App\Repositories\UserRepository;
use App\Services\AccessTokenService;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly AccessTokenService $accessTokenService,
    ) {}
    public function validationRules(): array
    {
        $roles = ['admin', 'manager', 'doctor'];
        return [
            "register" => [
                'user_id' => [
                    'required',
                    'string',
                    'between:7,15',
                    'unique:users'
                ],
                'username' => [
                    'required',
                    'string',
                    'between:7,15',
                    'unique:users',
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'unique:users'
                ],
                'password' => [
                    'required',
                    'string',
                    Password::defaults()
                ],
                'role' => [
                    'required',
                    'string',
                    Rule::in(values: $roles)
                ]
            ],
            "login" => [
                'username' => [
                    'required',
                    'string',
                    'between:7,15',
                ],
                'password' => [
                    'required',
                    'string',
                    Password::defaults()
                ],
            ]
        ];
    }

    public function register(Request $request): AuthDTO
    {
        $validated = $request->validate(rules: $this->validationRules()["register"]);
        $validated["password"] = Hash::make(value: $validated["password"]);
        $user = $this->userRepo->create(user: $validated);

        return new AuthDTO(
            data: new UserResource(resource: $user),
            status: $user ?  "success" : "error",
            message: $user ? "user registered successfully" : "user already exists!",
            code: $user ? 201 : 409
        );
    }

    public function login(Request $request): AuthDTO
    {

        $validated = $request->validate(rules: $this->validationRules()["login"]);

        $user = $this->userRepo->getUserByUsername(
            username: $validated["username"]
        );


        $isVerify = Hash::check(
            value: $validated["password"],
            hashedValue: $user["password"]
        );

        $accessToken = $this->accessTokenService->createAccessToken(user: $user);
        $cookie_login = $this->accessTokenService->setAccessToken(
            accessToken: $accessToken,
            role: $user["role"],
            username: $user["username"]
        );

        return new AuthDTO(
            data: new UserResource(resource: $user),
            status: $isVerify ? "success" : "error",
            message: $isVerify ?
                "user successfully logged in"
                : "user is unauthorized!",
            code: $isVerify ? 200 : 401,
            cookie: $cookie_login
        );
    }
}
