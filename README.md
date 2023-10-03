# Inspire WordPress Framework 2β

## Install
```
$ composer require jyokyoku/iwf2b
```

## Install Test Environment
```shell
docker compose exec {container} bash -c "/var/www/html/wp-content/plugins/iwf2b/bin/install-wp-tests.sh {db} {db_user} {db_pass} {db_host} 5.3.2"
```

## PHP Unit Test
```shell
docker compose exec {container} bash -c "cd /var/www/html/wp-content/plugins/iwf2b; phpunit"
```
