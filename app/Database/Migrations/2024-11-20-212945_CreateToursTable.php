<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateToursTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
                'primary_key'    => true,
                'default'        => 'UUID()',
            ],
            'name'        => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'duration'    => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'capacity'    => [
                'type'       => 'INT',
                'null'       => true,
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
                // 'onUpdate' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->createTable('tours');
        $this->db->query("ALTER TABLE tours MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('tours');
    }
}
