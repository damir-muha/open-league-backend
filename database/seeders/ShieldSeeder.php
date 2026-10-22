<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_event","view_any_event","create_event","update_event","restore_event","restore_any_event","replicate_event","reorder_event","delete_event","delete_any_event","force_delete_event","force_delete_any_event","view_game","view_any_game","create_game","update_game","restore_game","restore_any_game","replicate_game","reorder_game","delete_game","delete_any_game","force_delete_game","force_delete_any_game","view_group","view_any_group","create_group","update_group","restore_group","restore_any_group","replicate_group","reorder_group","delete_group","delete_any_group","force_delete_group","force_delete_any_group","view_person","view_any_person","create_person","update_person","restore_person","restore_any_person","replicate_person","reorder_person","delete_person","delete_any_person","force_delete_person","force_delete_any_person","view_player","view_any_player","create_player","update_player","restore_player","restore_any_player","replicate_player","reorder_player","delete_player","delete_any_player","force_delete_player","force_delete_any_player","view_round","view_any_round","create_round","update_round","restore_round","restore_any_round","replicate_round","reorder_round","delete_round","delete_any_round","force_delete_round","force_delete_any_round","view_season","view_any_season","create_season","update_season","restore_season","restore_any_season","replicate_season","reorder_season","delete_season","delete_any_season","force_delete_season","force_delete_any_season","view_season::roster","view_any_season::roster","create_season::roster","update_season::roster","restore_season::roster","restore_any_season::roster","replicate_season::roster","reorder_season::roster","delete_season::roster","delete_any_season::roster","force_delete_season::roster","force_delete_any_season::roster","view_stage","view_any_stage","create_stage","update_stage","restore_stage","restore_any_stage","replicate_stage","reorder_stage","delete_stage","delete_any_stage","force_delete_stage","force_delete_any_stage","view_team","view_any_team","create_team","update_team","restore_team","restore_any_team","replicate_team","reorder_team","delete_team","delete_any_team","force_delete_team","force_delete_any_team","view_tournament","view_any_tournament","create_tournament","update_tournament","restore_tournament","restore_any_tournament","replicate_tournament","reorder_tournament","delete_tournament","delete_any_tournament","force_delete_tournament","force_delete_any_tournament","view_transfer::request","view_any_transfer::request","create_transfer::request","update_transfer::request","restore_transfer::request","restore_any_transfer::request","replicate_transfer::request","reorder_transfer::request","delete_transfer::request","delete_any_transfer::request","force_delete_transfer::request","force_delete_any_transfer::request","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","view_venue","view_any_venue","create_venue","update_venue","restore_venue","restore_any_venue","replicate_venue","reorder_venue","delete_venue","delete_any_venue","force_delete_venue","force_delete_any_venue","view_game::lineup","view_any_game::lineup","create_game::lineup","update_game::lineup","restore_game::lineup","restore_any_game::lineup","replicate_game::lineup","reorder_game::lineup","delete_game::lineup","delete_any_game::lineup","force_delete_game::lineup","force_delete_any_game::lineup"]},{"name":"Tournament Administrator","guard_name":"web","permissions":["view_game","view_any_game","create_game","update_game","delete_game","view_group","view_any_group","create_group","update_group","delete_group","view_player","view_any_player","create_player","update_player","delete_player","view_round","view_any_round","create_round","update_round","delete_round","view_season","view_any_season","create_season","update_season","delete_season","view_season::roster","view_any_season::roster","create_season::roster","update_season::roster","delete_season::roster","view_stage","view_any_stage","create_stage","update_stage","delete_stage","view_team","view_any_team","create_team","update_team","delete_team","view_tournament","view_any_tournament","create_tournament","update_tournament","delete_tournament","view_transfer::request","view_any_transfer::request"]},{"name":"Team Manager","guard_name":"web","permissions":["view_person","view_any_person","view_player","view_any_player","view_season::roster","create_season::roster","update_season::roster","view_team","view_transfer::request","create_transfer::request","update_transfer::request","delete_transfer::request","view_game::lineup","create_game::lineup","update_game::lineup","delete_game::lineup"]},{"name":"Referee","guard_name":"web","permissions":["view_event","create_event","update_event","delete_event","view_game","update_game","view_player","view_any_player","view_team","view_any_team"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
