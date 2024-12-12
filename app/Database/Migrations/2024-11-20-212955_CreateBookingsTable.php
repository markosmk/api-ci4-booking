<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateBookingsTable extends Migration
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
            // 'uuid'         => [
            //     'type'           => 'VARCHAR',
            //     'constraint'     => 36,
            //     'primary_key'    => true,
            //     'default'        => 'UUID()',
            // ],
            'customerId'     => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'tourId'     => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'scheduleId' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            // 'date'       => [
            //     'type'       => 'DATE',
            // ],
            // 'startTime'  => [
            //     'type'       => 'TIME',
            // ],
            // 'endTime'    => [
            //     'type'       => 'TIME',
            // ],
            'quantity'   => [
                'type'       => 'INT',
            ],
            'status'     => [
                'type'       => 'ENUM',
                'constraint' => ['PENDING', 'CONFIRMED', 'CANCELED'],
                'default'    => 'PENDING',
            ],
            'totalPrice'  => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'tourData'    => [
                'type'       => 'TEXT',
            ],
            'scheduleData' => [
                'type'       => 'TEXT',
            ],
            'customerData' => [
                'type'       => 'TEXT', // JSON
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
        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey('customerId', 'customers', 'id', 'CASCADE', 'CASCADE', 'bookings_customerId_foreign');
        $this->forge->addForeignKey('tourId', 'tours', 'id', 'CASCADE', 'CASCADE', 'bookings_tourId_foreign');
        $this->forge->addForeignKey('scheduleId', 'schedules', 'id', 'CASCADE', 'CASCADE', 'bookings_scheduleId_foreign');

        $this->forge->createTable('bookings');
        $this->db->query("ALTER TABLE bookings MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

    }

    public function down()
    {
        $this->forge->dropForeignKey('bookings', 'bookings_customerId_foreign');
        $this->forge->dropForeignKey('bookings', 'bookings_tourId_foreign');
        $this->forge->dropForeignKey('bookings', 'bookings_scheduleId_foreign');
        $this->forge->dropTable('bookings');
    }
}
