<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim(
            (string) env('MASTER_ADMIN_EMAIL')
        );

        $name = trim(
            (string) env(
                'MASTER_ADMIN_NAME',
                'Administrador'
            )
        );

        $password = (string) env(
            'MASTER_ADMIN_PASSWORD'
        );

        if ($email === '') {
            throw new RuntimeException(
                'A variável MASTER_ADMIN_EMAIL não foi configurada.'
            );
        }

        $admin = Admin::query()->firstOrNew([
            'email' => $email,
        ]);

        $admin->name = $name;
        $admin->role = Admin::ROLE_MASTER;
        $admin->is_active = true;

        /*
         * A senha é exigida somente na criação.
         * Executar novamente não redefine a senha existente.
         */
        if (! $admin->exists) {
            if (trim($password) === '') {
                throw new RuntimeException(
                    'A variável MASTER_ADMIN_PASSWORD não foi configurada.'
                );
            }

            $admin->password = Hash::make($password);
        }

        $admin->save();
    }
}
