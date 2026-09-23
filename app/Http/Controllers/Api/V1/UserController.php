<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignRoleRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-only user management: CRUD plus role assignment/revocation.
 */
class UserController extends Controller
{
    /**
     * Paginated list of users with optional search + role filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->with('roles')
            ->when($request->string('search')->trim()->toString(), function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->string('role')->trim()->toString(), function ($query, string $role) {
                $query->role($role);
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return UserResource::collection($users);
    }

    /**
     * Create a user and optionally assign roles.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password']));

        if ($roles = $request->validated('roles')) {
            $user->syncRoles($roles);
        }

        return (new UserResource($user->load('roles')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Show a single user.
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user->load('roles', 'permissions'));
    }

    /**
     * Update a user's profile and (optionally) roles.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user->update($request->safe()->only(['name', 'email', 'password']));

        if ($request->has('roles')) {
            $user->syncRoles($request->validated('roles') ?? []);
        }

        return new UserResource($user->fresh()->load('roles'));
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.',
        ]);
    }

    /**
     * Assign one or more roles to a user (additive).
     */
    public function assignRoles(AssignRoleRequest $request, User $user): UserResource
    {
        $user->assignRole($request->validated('roles'));

        return new UserResource($user->fresh()->load('roles'));
    }

    /**
     * Revoke one or more roles from a user.
     */
    public function revokeRoles(AssignRoleRequest $request, User $user): UserResource
    {
        $user->removeRole($request->validated('roles'));

        return new UserResource($user->fresh()->load('roles'));
    }
}
