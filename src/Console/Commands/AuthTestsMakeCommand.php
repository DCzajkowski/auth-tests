<?php

namespace DCzajkowski\AuthTests\Console\Commands;

use Illuminate\Console\Command;

class AuthTestsMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:auth-tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tests for the auth module.';

    /**
     * The tests that need to be exported.
     *
     * @var array
     */
    protected $tests = [
        'Feature/Auth/ForgotPasswordTest.stub' => 'Feature/Auth/ForgotPasswordTest.php',
        'Feature/Auth/LoginTest.stub' => 'Feature/Auth/LoginTest.php',
        'Feature/Auth/RegisterTest.stub' => 'Feature/Auth/RegisterTest.php',
        'Feature/Auth/ResetPasswordTest.stub' => 'Feature/Auth/ResetPasswordTest.php',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->tests as $key => $value) {
            if (file_exists($test = base_path('tests/' . $value)) && ! $this->option('force')) {
                if (! $this->confirm("The [{$value}] test already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                __DIR__ . '/../stubs/tests/' . $key,
                $test
            );
        }

        $this->info('Authentication tests generated successfully.');
    }
}
