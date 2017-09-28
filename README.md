# Missing tests for Laravel's auth module
## Requirements
- Laravel 5.5

## Installation
```bash
composer require dczajkowski/auth-tests --dev
php artisan make:auth # if not ran previously
php artisan make:auth-tests
```

Edit `phpunit.xml` file by adding these two lines between `<php>` tags:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
Alternatively, use different database than sqlite, but also different from the one used for development.

## Updating
To update tests when a new version of this package arrives:
```bash
composer update dczajkowski/auth-tests
php artisan make:auth-tests --force
```
**Warning! All changes to the files this package provides will be lost when running this command!**

## Contributing
Feel free to make PRs to this repo.

## License
License can be found in the `LICENSE` file. It is a simple MIT, which basically means -- use it howewer you like.
