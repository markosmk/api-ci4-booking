<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'name' => 'Admin User',
                'role' => 'ADMIN',
            ],
            [
                'username' => 'superadmin',
                'email' => 'superadmin@gmail.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'name' => 'Super Admin User',
                'role' => 'SUPERADMIN',
            ],
        ];

        $this->db->table('users')->insertBatch($data);

        // execute php spark db:seed UserSeeder
    }
}
