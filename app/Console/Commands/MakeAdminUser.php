<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeAdminUser extends Command
{
    protected $signature = 'admin:make-admin {user_id : User ID to make admin}';
    protected $description = 'Make a user an admin';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        $user->update(['is_admin' => true]);

        $this->info("User '{$user->name}' ({$user->email}) is now an admin!");
        return 0;
    }
}
