<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "constraint" => 11, 
                "unsigned" => true,
                "auto_increment" => true
            ],
            "name" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => false
            ],
            "email" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => false,
                "unique" => true
            ],
            "password" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                ]
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("users");
    }
    
    public function down()
    {
        $this->forge->dropTable("users");
        
    }
}
