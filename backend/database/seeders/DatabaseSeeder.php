<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
        ]);

        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
        $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
        $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

        $this->upsertSystemUser('admin@ems.com', 'System Admin', (string) $adminRole->id);
        $this->upsertSystemUser('manager@ems.com', 'System Manager', (string) $managerRole->id);
        $this->upsertSystemUser('employee@ems.com', 'System Employee', (string) $employeeRole->id);
    }

    private function upsertSystemUser(string $email, string $name, string $roleId): void
    {
        User::query()
            ->withTrashed()
            ->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role_id' => $roleId,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'deleted_at' => null,
                ]
            );
    }
}
