# Missing tests for Laravel's auth module
## Installation
```bash
composer require dczajkowski/auth-tests --dev
php artisan make:auth-tests
```

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
