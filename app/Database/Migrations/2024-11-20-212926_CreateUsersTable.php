<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateUsersTable extends Migration
{
    /**
     * Creates the 'users' table with the following fields:
     * - id: Primary key, VARCHAR(36), default value is generated UUID.
     * - email: Unique, VARCHAR(255).
     * - password: VARCHAR(255).
     * - name: VARCHAR(255), nullable.
     * - role: ENUM with values 'ADMIN', 'SUPERADMIN', 'USER', default is 'ADMIN'.
     *
     * @return void
     */
    public function up()
    {
        $this->forge->addField(
            [
                'id'       => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'username'    => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    // 'unique'     => true,
                ],
                'email'    => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'unique'     => true,
                ],
                'password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'name'     => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'role'     => [
                    'type'       => 'ENUM',
                    'constraint' => ['ADMIN', 'SUPERADMIN', 'USER'],
                    'default'    => 'ADMIN',
                ],
                'created_at'  => [
                    'type' => 'TIMESTAMP',
                    'null' => false,
                    'default' => new RawSql('CURRENT_TIMESTAMP'),
                ],
                'updated_at'  => [
                    'type' => 'TIMESTAMP',
                    'null' => false,
                    'default' => new RawSql('CURRENT_TIMESTAMP'),
                ],
            ]
        );

        // $this->forge->addKey('id', true);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('users');

        $this->db->query("ALTER TABLE users MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        // execute php spark migrate
    }

    /**
     * Revert the users table
     *
     * @return void
     */
    public function down()
    {
        $this->forge->dropTable('users');
    }
}
