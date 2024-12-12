<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // 'id'        => [
            //     'type'           => 'VARCHAR',
            //     'constraint'     => 36,
            //     'primary_key'    => true,
            //     'default'        => 'UUID()',
            // ],
            'tourId'    => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'date'      => [
                'type'       => 'DATE',
            ],
            'startTime' => [
                'type'       => 'TIME',
            ],
            'endTime'   => [
                'type'       => 'TIME',
            ],
            'active' => [
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

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('tourId', 'tours', 'id', 'CASCADE', 'CASCADE', 'schedules_tourId_foreign');
        $this->forge->createTable('schedules');

        $this->db->query("ALTER TABLE schedules MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropForeignKey('schedules', 'schedules_tourId_foreign');
        $this->forge->dropTable('schedules');
    }
}
