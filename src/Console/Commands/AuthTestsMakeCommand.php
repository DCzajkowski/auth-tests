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
        {--a|annotation : Don\'t prepend tests\' names with \'test\', but use @test annotation instead}
        {--s|snake-case : Use snake-case rather than camel-case}
        {--f|force : Overwrite existing tests}';

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
        'Feature/MakesRequestsFromPage.php' => 'Feature/MakesRequestsFromPage.php',
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

            $this->publishStub(__DIR__ . '/../stubs/tests/' . $key, $test);
        }
    }

    /**
     * Publish individual stubs.
     *
     * @return void
     */
    public function publishStub($stubPath, $destinationTest)
    {
        $content = file_get_contents($stubPath);

        if ($this->option('snake-case')) {
            $content = $this->snakeCase($content);
        }

        if ($this->option('annotation')) {
            $content = $this->annotate($content);
        }

        file_put_contents($destinationTest, $content);
    }

    /**
     * Get test in snake_case format.
     *
     * @return string
     */
    public function snakeCase($stub)
    {
        return preg_replace_callback('/    public function test.+/', function ($matches) {
            return strtolower(preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', '_$0', $matches[0])));
        }, $stub);
    }

    /**
     * Get test in annotated format.
     *
     * @return string
     */
    public function annotate($stub)
    {
        return str_replace('function _', 'function ', preg_replace_callback('/    public function test./', function ($matches) {
            return '    /** @test */' . PHP_EOL . '    public function ' . strtolower($matches[0][-1]);
        }, $stub));
    }
}
