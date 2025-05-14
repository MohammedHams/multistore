<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class EnableTwoFactorAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:enable-2fa {--force : Force enable 2FA for all users without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable two-factor authentication for all users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to enable two-factor authentication for all users?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);

        // Enable 2FA for all users
        $userCount = User::count();
        $this->info("Enabling two-factor authentication for {$userCount} users...");
        
        $progressBar = $this->output->createProgressBar($userCount);
        $progressBar->start();
        
        User::chunk(100, function ($users) use ($enableTwoFactorAuthentication, $progressBar) {
            foreach ($users as $user) {
                if (!$user->two_factor_secret) {
                    $enableTwoFactorAuthentication($user);
                }
                $progressBar->advance();
            }
        });
        
        $progressBar->finish();
        $this->newLine();
        $this->info('Two-factor authentication has been enabled for all users.');
        $this->warn('Note: Users will need to scan the QR code and confirm their 2FA setup on their next login.');

        return 0;
    }
}
