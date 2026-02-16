<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckAdminStatus extends Command
{
    protected $signature = 'admin:check';
    protected $description = 'Check admin status of all users';

    public function handle()
    {
        $users = User::select('id', 'name', 'email', 'is_admin')->get();
        
        if ($users->isEmpty()) {
            $this->info('No users found');
            return;
        }

        $this->info('Current Users:');
        $this->table(['ID', 'Name', 'Email', 'Is Admin'], 
            $users->map(fn ($u) => [
                $u->id,
                $u->name,
                $u->email,
                $u->is_admin ? '✓ YES' : '✗ NO'
            ])->toArray()
        );

        $this->newLine();
        $this->info('To make a user admin, run:');
        $this->line('php artisan admin:make-admin <user_id>');
    }
}
