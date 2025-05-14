<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class MultiGuardAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Skip running migrations as they should already be handled by migrate:fresh
        $this->command->info('Setting up multi-guard authentication...');
        
        // Run seeders
        $this->command->info('Running seeders for multi-guard authentication...');
        $this->call(StoreOwnerPermissionsSeeder::class);
        
        $this->command->info('Multi-guard authentication setup completed!');
    }
}
