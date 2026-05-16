## Task

Додані міграції і дамп БД.

Додані тести. Працюють по основній БД.

- Get user - 404
- Auth fail
- Create user with ROLE_USER
- Update user with ROLE_USER
- Get user with ROLE_USER
- Create user with ROLE_USER by user with ROLE_USER 
- Update user with ROLE_USER
- Delete users by ROLE_ROOT


~~~
composer install

php bin/console doctrine:migrations:migrate

php bin/phpunit
~~~
