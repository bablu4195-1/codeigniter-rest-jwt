<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BookMigration extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            "id" => [
                "type" => "INT",    
                "constraint" => 11,
                "unsigned" => true,
                "auto_increment" => true
            ],
            "user_id" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                
            ],
            "title" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => false
            ], 
            "price" => [
                "type" => "INT",
                "constraint" => 255,
                "null" => false,
            ]

        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('books');
    }

    public function down()
    {
        //
        $this->forge->dropTable('books');
    }
}
