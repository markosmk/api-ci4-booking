<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'        => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
                'primary_key'    => true,
                'default'        => 'UUID()',
            ],
            'tourId'    => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
            ],
            'date'      => [
                'type'       => 'DATETIME',
            ],
            'startTime' => [
                'type'       => 'DATETIME',
            ],
            'endTime'   => [
                'type'       => 'DATETIME',
            ],
            'available' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
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

        $this->forge->createTable('schedules');
        $this->forge->addForeignKey('tourId', 'tours', 'id', 'CASCADE', 'CASCADE');

        $this->db->query("ALTER TABLE schedules MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropForeignKey('schedules', 'schedules_tourId_foreign');
        $this->forge->dropTable('schedules');
    }
}
