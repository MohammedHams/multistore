<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Enabling two-factor authentication for all users...');

        // Check if the necessary columns exist in the users table
        if (Schema::hasColumn('users', 'two_factor_secret') &&
            Schema::hasColumn('users', 'two_factor_recovery_codes') &&
            Schema::hasColumn('users', 'two_factor_confirmed_at')) {
            
            $this->enableTwoFactorForUsers();
        } else {
            $this->command->error('The necessary columns for two-factor authentication do not exist in the users table.');
            $this->command->info('Please run the migration first: php artisan migrate --path=database/migrations/2025_05_14_000001_add_two_factor_columns_to_users_table.php');
        }
    }

    /**
     * Enable two-factor authentication for all users.
     *
     * @return void
     */
    private function enableTwoFactorForUsers()
    {
        // Get all users
        $users = DB::table('users')->get();
        $count = count($users);
        
        if ($count === 0) {
            $this->command->info('No users found in the database.');
            return;
        }
        
        $this->command->info("Found {$count} users. Enabling two-factor authentication...");
        
        $google2fa = new Google2FA();
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();
        
        foreach ($users as $user) {
            // Skip users who already have 2FA enabled
            if ($user->two_factor_secret) {
                $bar->advance();
                continue;
            }
            
            // Generate a new secret key
            $secretKey = $google2fa->generateSecretKey();
            
            // Generate recovery codes
            $recoveryCodes = $this->generateRecoveryCodes();
            
            // Update the user
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'two_factor_secret' => encrypt($secretKey),
                    'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
                    'two_factor_confirmed_at' => now(),
                ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine();
        $this->command->info('Two-factor authentication has been enabled for all users.');
        $this->command->info('Note: Users will need to set up their authenticator app with the QR code on their next login.');
    }

    /**
     * Generate recovery codes.
     *
     * @return array
     */
    private function generateRecoveryCodes()
    {
        $recoveryCodes = [];
        
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = $this->generateRecoveryCode();
        }
        
        return $recoveryCodes;
    }

    /**
     * Generate a recovery code.
     *
     * @return string
     */
    private function generateRecoveryCode()
    {
        return substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 10);
    }
}
