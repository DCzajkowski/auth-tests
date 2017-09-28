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
    protected $signature = 'make:auth-tests
        {--force : Overwrite existing tests}';

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
        'Feature/Auth/ForgotPasswordTest.php' => 'Feature/Auth/ForgotPasswordTest.php',
        'Feature/Auth/LoginTest.php' => 'Feature/Auth/LoginTest.php',
        'Feature/Auth/RegisterTest.php' => 'Feature/Auth/RegisterTest.php',
        'Feature/Auth/ResetPasswordTest.php' => 'Feature/Auth/ResetPasswordTest.php',
    ];

    /**
     * Directories that must be created.
     *
     * @var array
     */
    protected $directories = [
        'tests/Feature/Auth',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createDirectories();

        $this->publishTests();

        $this->info('Authentication tests generated successfully.');
    }

    /**
     * Create required directories.
     *
     * @return void
     */
    public function createDirectories()
    {
        foreach ($this->directories as $dir) {
            if (! is_dir($directory = base_path($dir))) {
                mkdir($directory, 0755, true);
            }
        }
    }

    /**
     * Publish all tests.
     *
     * @return void
     */
    public function publishTests()
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
    }
}
