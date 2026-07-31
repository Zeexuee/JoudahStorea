<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {email : Admin email} {password : New password}';
    protected $description = 'Reset admin password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found");
            return 1;
        }

        if (!$user->is_admin) {
            $this->warn("Warning: User {$email} is not an admin!");
        }

        $user->update(['password' => Hash::make($password)]);

        $this->info("Password for '{$user->name}' ({$email}) has been reset!");
        $this->line("New password: {$password}");
        return 0;
    }
}
