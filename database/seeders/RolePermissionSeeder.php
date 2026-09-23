<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds baseline RBAC: three roles (admin, editor, viewer) and per-resource
 * permissions grouped by the CMS content types.
 *
 * - admin  : every permission (including user management)
 * - editor : full content CRUD, no user management
 * - viewer : read-only access to content
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Resource => actions that make up the permission matrix.
     *
     * @var array<string, array<int, string>>
     */
    private const RESOURCES = [
        'users' => ['view', 'create', 'update', 'delete', 'assign-role'],
        'hero' => ['view', 'update'],
        'about' => ['view', 'update'],
        'partners' => ['view', 'create', 'update', 'delete'],
        'projects' => ['view', 'create', 'update', 'delete'],
        'contact-inquiries' => ['view', 'delete'],
        'uploads' => ['create'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = $this->createPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'api']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'api']);

        // admin: everything.
        $admin->syncPermissions($permissions);

        // editor: full CRUD on content, read-only elsewhere (no user management).
        $editor->syncPermissions([
            ...$this->permissionsFor('hero', ['view', 'update']),
            ...$this->permissionsFor('about', ['view', 'update']),
            ...$this->permissionsFor('partners', ['view', 'create', 'update', 'delete']),
            ...$this->permissionsFor('projects', ['view', 'create', 'update', 'delete']),
            ...$this->permissionsFor('contact-inquiries', ['view']),
            ...$this->permissionsFor('uploads', ['create']),
        ]);

        // viewer: read-only.
        $viewer->syncPermissions($this->permissionsFor('hero', ['view']));
        $viewer->givePermissionTo($this->permissionsFor('about', ['view']));
        $viewer->givePermissionTo($this->permissionsFor('partners', ['view']));
        $viewer->givePermissionTo($this->permissionsFor('projects', ['view']));
        $viewer->givePermissionTo($this->permissionsFor('contact-inquiries', ['view']));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Create every permission and return their model instances.
     *
     * @return array<int, Permission>
     */
    private function createPermissions(): array
    {
        $permissions = [];

        foreach (self::RESOURCES as $resource => $actions) {
            foreach ($actions as $action) {
                $permissions[] = Permission::firstOrCreate([
                    'name' => "{$resource}.{$action}",
                    'guard_name' => 'api',
                ]);
            }
        }

        return $permissions;
    }

    /**
     * Resolve permission models for a resource/action subset.
     *
     * @param  array<int, string>  $actions
     * @return array<int, Permission>
     */
    private function permissionsFor(string $resource, array $actions): array
    {
        return array_map(
            fn (string $action) => Permission::findByName("{$resource}.{$action}", 'api'),
            $actions,
        );
    }
}
