# E-Docs Installation

 1. Download or clone this repository.
 2. Run `composer install`
 3. Copy from `.env.example`& setup `.env` file.
 4. Create database & Change `DB_DATABASE` in `.env`.
 5. Run `php artisan key:generate`
 6. Run `php arisan migrate`
 7. Run `php artisan db:seed` (This will generate super-admin & basic settings [required]).
 8. Run `npm install && npm run dev`
 9. Run `php artisan serve`
 10. Visit URL in the browser. 

##### Default Login Credential for super admin

| Username | Password |
|----------|----------|
| yoghiyb| 123456   |
