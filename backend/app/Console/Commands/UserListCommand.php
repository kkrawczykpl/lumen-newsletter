<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command;
use App\Models\User;

class UserListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'user:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all registered Users in table/list';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['ID', 'Name', 'EMail', 'created_at', 'updated_at'];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->handleApi();

        $users = User::all(array_map('strtolower', $this->headers))->toArray();

        // Return json object of users if "json" option providen
        if ($this->option('json')) {
            $this->line(json_encode(array_values($users)));
            return;
        }

        if(count($users)) {
            $this->info(count($users) . " Users found:");
            $this->table($this->headers, $users);
        }else{
            $this->errors("There are no users!");
        }
    }

    /**
     * Handle "api" Option
     * 
     * @return void
     */
    protected function handleApi()
    {
        if($this->option('api')) {
            $this->headers = ['ID', 'Name', 'EMail', 'API_Key', 'created_at', 'updated_at'];
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['api', null, InputOption::VALUE_NONE, 'Output Users with their API keys'],
        ];
    }
}