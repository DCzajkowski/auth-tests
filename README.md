# Missing tests for Laravel's auth module
[![Latest Stable Version](https://poser.pugx.org/dczajkowski/auth-tests/version)](https://packagist.org/packages/dczajkowski/auth-tests)
[![License MIT](https://poser.pugx.org/dczajkowski/auth-tests/license)](https://packagist.org/packages/dczajkowski/auth-tests)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)](https://egghead.io/courses/how-to-contribute-to-an-open-source-project-on-github)

![](https://i.imgur.com/1z5XkDc.png)

## ⚠️ Deprecation notice ⚠️
As of Laravel 8, the [laravel/ui](https://github.com/laravel/ui) package is discouraged to be used on new Laravel installations. **This package should be used only with already-existing, created with Laravel 7 or lower, applications that use laravel/ui auth controllers.**

All of the applications already using [laravel/ui](https://github.com/laravel/ui) will get updates of this package to new Laravel versions, although the support may be dropped in the future. This doesn't mean you won't be able to use the package or upgrade to new Laravel versions, but that the upgrades to the major versions may require manual changes from the consumers of this package.

The new way of installing Laravel 8's and above auth scaffolding is using the `--jet` option in the Laravel installer. Laravel [Jetstream](https://github.com/laravel/jetstream) hides all of its controllers inside the package, meaning it doesn't make sense to test those controllers, as they [are already tested inside the package](https://github.com/laravel/jetstream/tree/1.x/tests).

## Versioning
~The version of this package reflects current major version of the Laravel framework. For example:
If Laravel framework has version 5.6, version of this package compatible will be `5.6.*`.~

Due to changes in Laravel's versioning scheme, version 6.1.* will aim to be compatible with a version 6.1.* of Laravel. For example, _auth-tests_ version 6.1.1 and 6.1.20 will be all compatible with Laravel 6.1.*

## Installation
> Before installation please make sure you have scaffolded
> frontend views with a `--auth` flag e.g.
> ```bash
> composer require laravel/ui && php artisan ui vue --auth
> ```

```bash
composer require dczajkowski/auth-tests --dev
php artisan make:auth-tests --without-email-verification
```

Edit `phpunit.xml` file by adding these two lines between `<php>` tags:
```xml
<server name="DB_CONNECTION" value="sqlite"/>
<server name="DB_DATABASE" value=":memory:"/>
```
Alternatively, use different database than sqlite, but also different from the one used for development.

### Using the e-mail verification feature
If you want to use the e-mail verification feature, you will have to make following changes:
- update `routes/web.php`:
```diff
- Auth::routes();
+ Auth::routes(['verify' => true]);
```
- update `app/Models/User.php`:
```diff
- class User extends Authenticatable
+ class User extends Authenticatable implements MustVerifyEmail
```

## Options
There are four flags for customizing your tests. You can use any combination of them. (All flags have their short version e.g. `--zonda` or `-z`)
```php
# make:auth-tests
public function testUserCanLogout()
{
    //
}

# make:auth-tests --snake-case
public function test_user_can_logout()
{
    //
}

# make:auth-tests --annotation
/** @test */
public function userCanLogout()
{
    //
}

# make:auth-tests --public
function testUserCanLogout()
{
    //
}

# make:auth-tests --curly
public function testUserCanLogout() {
    //
}

# make:auth-tests -caps # or --zonda
/** @test */
function user_can_logout() {
    //
}
```
Since version 5.7 there has been a new test for email verification added. You can omit it by running `--without-email-verification`.

To review all flags run `php artisan make:auth-tests --help`.

## Updating
To update tests when a new version of this package arrives:
```bash
composer update dczajkowski/auth-tests
php artisan make:auth-tests
```
**Warning! All changes to the files this package provides will be lost when running this command!**

## Automate your workflow
Instead of including this package manually every project you create, simply create a bash function that will do that for you. I have included my personal function [here](https://gist.github.com/DCzajkowski/9ebaeaa09d136e77497e060449b03171). Feel free to edit it and reuse however you like.

## Contributing
Feel free to make PRs to this repo.

## License
This package is open-source software licensed under the MIT license (same as Laravel itself).
