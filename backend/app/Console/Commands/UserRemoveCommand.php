<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command;
use App\Models\User;

class UserRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:remove {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an existing User';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = User::findOrFail($this->argument('id'));

        if ($this->confirm("Are you sure you want to remove {$user->email}?")) {
            $user->delete();
            $this->info("User {$user->email} removed correctly");
        }
    }
}