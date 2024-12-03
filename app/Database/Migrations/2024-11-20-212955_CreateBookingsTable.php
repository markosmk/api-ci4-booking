<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
                'primary_key'    => true,
                'default'        => 'UUID()',
            ],
            'userId'     => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
            ],
            'tourId'     => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
            ],
            'scheduleId' => [
                'type'           => 'VARCHAR',
                'constraint'     => 36,
            ],
            'date'       => [
                'type'       => 'DATETIME',
            ],
            'startTime'  => [
                'type'       => 'DATETIME',
            ],
            'endTime'    => [
                'type'       => 'DATETIME',
            ],
            'quantity'   => [
                'type'       => 'INT',
            ],
            'status'     => [
                'type'       => 'ENUM',
                'constraint' => ['PENDING', 'CONFIRMED', 'CANCELED'],
                'default'    => 'PENDING',
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

        $this->forge->createTable('bookings');

        $this->forge->addForeignKey('userId', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tourId', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('scheduleId', 'schedules', 'id', 'CASCADE', 'CASCADE');

        $this->db->query("ALTER TABLE bookings MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

    }

    public function down()
    {
        $this->forge->dropForeignKey('bookings', 'bookings_userId_foreign');
        $this->forge->dropForeignKey('bookings', 'bookings_tourId_foreign');
        $this->forge->dropForeignKey('bookings', 'bookings_scheduleId_foreign');
        $this->forge->dropTable('bookings');
    }
}
