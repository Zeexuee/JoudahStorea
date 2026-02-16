<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MakeAdminUser extends Command
{
    protected $signature = 'admin:make-admin {user_id? : User ID to make admin} {--create : Create new admin user} {--email= : Email for new admin} {--password= : Password for new admin} {--name= : Name for new admin}';
    protected $description = 'Make a user an admin or create a new admin user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $createNew = $this->option('create');

        // If --create flag is set, create new admin
        if ($createNew) {
            return $this->createNewAdmin();
        }

        // Otherwise update existing user
        if (!$userId) {
            $emails = User::pluck('email')->toArray();
            if (empty($emails)) {
                $this->error("No users found in database");
                return 1;
            }
            
            $email = $this->choice('Select user email to make admin:', $emails);
            $user = User::where('email', $email)->first();
        } else {
            $user = User::find($userId);
        }

        if (!$user) {
            $this->error("User not found");
            return 1;
        }

        if ($user->is_admin) {
            $this->warn("User '{$user->name}' ({$user->email}) is already an admin!");
            return 0;
        }

        $user->update(['is_admin' => true]);
        $this->info("✓ User '{$user->name}' ({$user->email}) is now an admin!");
        
        return 0;
    }

    private function createNewAdmin()
    {
        $email = $this->option('email') ?: $this->ask('Enter email address');
        
        // Check if email exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email '{$email}' already exists!");
            return 1;
        }

        $name = $this->option('name') ?: $this->ask('Enter full name', 'Admin');
        $password = $this->option('password') ?: $this->secret('Enter password (min 6 characters)');

        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters!');
            return 1;
        }

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);

            $this->info("✓ New admin user created successfully!");
            $this->line("  Email: {$user->email}");
            $this->line("  Name: {$user->name}");
            $this->line("  ID: {$user->id}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error creating admin user: " . $e->getMessage());
            return 1;
        }
    }
}
