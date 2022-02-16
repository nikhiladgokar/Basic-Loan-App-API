<p align="center">Loan App</p>

## Table of Contents

* [Introduction](#introduction)
* [Setup](#setup)
* [Run API Test Cases](#run-api-test-cases)
* [Code Coverage](#code-coverage)


> open project from command line in VsCode editor with `code .`

### Introduction
setup `.env` file before starting setup
and `.env.testing` file for test-case database


### Setup

```bash
yarn install
yarn docker:start

'Note: Use http://localhost:8000/dbadmin.php and create 2 database loan_app_dev and loan_app_test.

Server: mysql
user: root
password: admin
' 

yarn docker:shell # new tab in console
composer install
php artisan migrate:fresh --seed 
php artisian passport:install

Note: Include passport client token in postman environment 
```
1. your project is ready on url: `http://localhost:8000`

2. Import postman collection added in to root folder of project `loan_application.postman_collection.json`

3. Import postman environment added in to root folder of project `loan_application.postman_environment.json`

### Run Api Test Cases

```bash
yarn docker:shell # new tab in console
php artisan test 
```

### Code Coverage
```bash
yarn docker:shell # new tab in console
vendor/bin/phpunit --coverage-html reports/
```
 To run coverage report in root directory open reports/index.php file 
