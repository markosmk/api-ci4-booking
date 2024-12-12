<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateCustomersTable extends Migration
{
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
                'name'    => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'phone'    => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'email'    => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
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

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('customers');

        $this->db->query("ALTER TABLE customers MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

    }

    public function down()
    {
        $this->forge->dropTable('customers');
    }
}
