<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');
        $managerRoleId = DB::table('roles')->where('name', 'manager')->value('id');

        // Add sample admin user
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
            'role_id' => $adminRoleId,
            'approval_status' => true, // Setujui admin secara default
        ]);

        // Add sample regular user (belum disetujui)
        DB::table('users')->insert([
            'name' => 'Regular User',
            'email' => 'user@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
            'role_id' => $userRoleId,
            'approval_status' => false, // Belum disetujui
        ]);

        // Add sample manager user (belum disetujui)
        DB::table('users')->insert([
            'name' => 'Regular Manager',
            'email' => 'manager@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
            'role_id' => $managerRoleId,
            'approval_status' => false, // Belum disetujui
        ]);
    }
}
