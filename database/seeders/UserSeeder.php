<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;



class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'first_name' => 'dex',
            'last_name' => 'alsex',
            'password' => Hash::make('dexdexdex'),
            'email' => 'admin@admin.com',
            'role_id' => Role::where('name', 'admin')->get()->first()->id,
            'friends' => "a;b;c",
            'verified' => 1,
          ]);

        $user = User::create([
          'first_name' => 'user1',
          'last_name' => 'verified',
          'password' => Hash::make('dexdexdex'),
          'email' => 'user1@email.com',
          'role_id' => Role::where('name', 'user')->get()->first()->id,
          'friends' => "a;b;c",
          'verified' => 1,
        ]);
        $user = User::create([
            'first_name' => 'user2',
            'last_name' => 'not verified',
            'password' => Hash::make('dexdexdex'),
            'email' => 'user2@email.com',
            'role_id' => Role::where('name', 'user')->get()->first()->id,
            'friends' => "a;b;c",
            'verified' => 0,
          ]);
    }
}
