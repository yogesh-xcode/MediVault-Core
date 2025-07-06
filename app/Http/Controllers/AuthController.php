<?php

namespace App\Http\Controllers;

use App\DTOs\AuthDTO;
use App\Http\Resources\UserResource;
<<<<<<< HEAD
=======

>>>>>>> dev
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
<<<<<<< HEAD
=======

>>>>>>> dev
use App\Repositories\UserRepository;
use App\Services\AccessTokenService;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly AccessTokenService $accessTokenService
    ) {
    }

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
            status: "success",
            message: "User registered successfully.",
            code: 201
        );
    }

    public function login(Request $request): AuthDTO
    {
        $validated = $request->validate(rules: $this->validationRules()["login"]);

        $user = $this->userRepo->getUserByUsername(
            username: $validated["username"]
        );

        if (!$user || !Hash::check($validated["password"], $user->password)) {
            return new AuthDTO(
                data: new UserResource($user), // still return the structure
                status: "error",
                message: "Invalid credentials.",
                code: 401
            );
        }

        $accessToken = $this->accessTokenService->createAccessToken(user: $user);
        $cookie = $this->accessTokenService->setAccessToken(
            accessToken: $accessToken,
            role: $user->role,
            username: $user->username
        );

        return new AuthDTO(
            data: new UserResource(resource: $user),
            status: "success",
            message: "User successfully logged in.",
            code: 200,
            cookie: $cookie
        );
    }
}