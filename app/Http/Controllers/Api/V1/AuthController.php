<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Passport-backed authentication: password grant login, refresh, current user
 * and logout (token revocation).
 */
class AuthController extends Controller
{
    /**
     * Exchange email + password for a Passport access/refresh token pair.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! password_verify($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'Kredensial tidak valid.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $tokens = $this->issueToken(
            $request->validated('email'),
            $request->validated('password'),
        );

        if ($tokens === null) {
            return response()->json([
                'message' => 'Gagal menerbitkan token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => new UserResource($user->load('roles')),
                'token' => $tokens,
            ],
        ]);
    }

    /**
     * Return the currently authenticated user with roles and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource(
                $request->user()->load('roles', 'permissions'),
            ),
        ]);
    }

    /**
     * Rotate a refresh token into a fresh access/refresh token pair.
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $tokens = $this->postToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->validated('refresh_token'),
            'client_id' => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'scope' => '',
        ]);

        if ($tokens === null) {
            return response()->json([
                'message' => 'Refresh token tidak valid atau kedaluwarsa.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Token berhasil diperbarui.',
            'data' => ['token' => $tokens],
        ]);
    }

    /**
     * Revoke the access token used for the current request (and its refresh
     * token, when available).
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        if ($token) {
            $token->revoke();

            $token->refreshToken?->revoke();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Request a password-grant token pair from Passport.
     *
     * @return array<string, mixed>|null
     */
    private function issueToken(string $email, string $password): ?array
    {
        return $this->postToken([
            'grant_type' => 'password',
            'client_id' => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'username' => $email,
            'password' => $password,
            'scope' => '',
        ]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    private function postToken(array $params): ?array
    {
        if (app()->runningUnitTests()) {
            $response = Http::asForm()->post($this->tokenUrl(), $params);

            return $response->successful() ? $response->json() : null;
        }

        $tokenRequest = Request::create('/oauth/token', 'POST', $params);
        $tokenRequest->headers->set('Accept', 'application/json');

        $response = app()->handle($tokenRequest);

        if (! $response->isSuccessful()) {
            Log::warning('Passport grant gagal.', [
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getContent(), true),
            ]);

            return null;
        }

        return json_decode($response->getContent(), true);
    }

    private function tokenUrl(): string
    {
        return url('oauth/token');
    }
}
