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
        {--p|public : Use php\'s implicit visibility (don\'t show \'public\' keyword)}
        {--c|curly : Put curly brackets in the same line}
        {--z|zonda : Full-on Zonda mode. Annotated, snake-cased methods without public keyword. Curly brackets in the same line}
        {--without-email-verification : Don\'t include a test connected to the email verification feature added in Laravel 5.7}
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
        'Feature/Auth/ForgotPasswordTest.php',
        'Feature/Auth/LoginTest.php',
        'Feature/Auth/RegisterTest.php',
        'Feature/Auth/ResetPasswordTest.php',
    ];

    protected $emailVerificationTest = 'Feature/Auth/EmailVerificationTest.php';

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
        $tests = $this->tests;

        if (! $this->option('without-email-verification')) {
            $tests[] = $this->emailVerificationTest;
        }

        foreach ($tests as $test) {
            if (file_exists($destination = base_path('tests/' . $test)) && ! $this->option('force')) {
                if (! $this->confirm("The [{$test}] test already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            $this->publishStub(__DIR__ . '/../stubs/tests/' . $test, $destination);
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

        if ($this->option('snake-case') || $this->option('zonda')) {
            $content = $this->snakeCase($content);
        }

        if ($this->option('annotation') || $this->option('zonda')) {
            $content = $this->annotate($content);
        }

        if ($this->option('public') || $this->option('zonda')) {
            $content = $this->removePublicKeyword($content);
        }

        if ($this->option('curly') || $this->option('zonda')) {
            $content = $this->putCurlyBracketsInTheSameLine($content);
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
            return strtolower(preg_replace('/([A-Z])/', '_$0', $matches[0]));
        }, $stub);
    }

    /**
     * Get test in an annotated format.
     *
     * @return string
     */
    public function annotate($stub)
    {
        return str_replace('function _', 'function ', preg_replace_callback('/    public function test./', function ($matches) {
            return '    /** @test */' . PHP_EOL . '    public function ' . strtolower($matches[0][-1]);
        }, $stub));
    }

    /**
     * Get test without 'public' keywords.
     *
     * @return string
     */
    public function removePublicKeyword($stub)
    {
        return str_replace('public function ', 'function ', $stub);
    }

    /**
     * Get test with curly brackets in the same line.
     *
     * @return string
     */
    public function putCurlyBracketsInTheSameLine($stub)
    {
        return str_replace(")\n    {", ') {', str_replace("TestCase\n{", 'TestCase {', $stub));
    }
}
