<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Due to security reasons it must not be possible to create first time Admin User via API
 * This command comes handy to make LumenNewsletter installation as easy as possible.
 * Also not everyone may want to be able to add users through the API.
 */
class UserAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'user:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user who can handle requests to the API';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        do {
            $details  = $this->askForDetails();
            $name     = $details['name'];
            $email    = $details['email'];
        } while (!$this->confirm("Create user {$name} <{$email}>?", true));

        // Create a user
        $user = $this->getUser(
            $details
        );

        if(!$user) {
            exit;
        }

        $this->info("The user #{$user->id}: {$user->name} <{$email}> now has full access to your site.");
    }

    protected function askForDetails()
    {
        $name = $this->ask('Enter the User Name');
        $email = $this->askEmail('Enter the User E-mail');
        $password = $this->secret('Enter User password');
        $confirmPassword = $this->secret('Confirm User password');

        return compact('name', 'email', 'password', 'confirmPassword');
    }

    /**
     * Get or create user.
     *
     * @param bool $create
     *
     * @return User
     */
    protected function getUser($details)
    {
        if ($details['password'] != $details['confirmPassword']) {
            $this->info("Passwords don't match");
            return;
        }

        $this->info('Creating admin account');

        return User::forceCreate([
            'name' => $details['name'],
            'email' => $details['email'],
            'password' => Hash::make($details['password'])
        ]);
        return User::where('email', $email)->firstOrFail();
    }

    protected function askEmail($message)
    {
        do {
            $email = $this->ask($message);
        } while (!$this->isValidEmail($email) || !$this->isUniqueEmail($email));

        return $email;
    }

    /**
     * Checks wheter providen e-mail is correct
     * 
     * @param string $email
     * @return boolean 
     */
    protected function isValidEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("<{$email}> is not an valid E-mail address!");
            return false;
        }
        return true;
    }


    /**
     * Checks wheter providen e-mail is unique
     * 
     * @param string $email
     * @return boolean 
     */
    protected function isUniqueEmail($email)
    {
        if(User::where('email', $email)->exists())
        {
            $this->error("User with <{$email}> e-mail already exists!");
            return false;
        }
        return true;
    }
}