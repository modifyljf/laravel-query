
#### 1. add 'vcs' repositories type.
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/guesl/laravel-admin.git",
        "no-api": true
    }
]

#### 2. Install the composer package.
Add the below line into composer.json file:
"guesl/laravel-admin": "^1.0.0"
  
  or run the command in terminal:

    composer require "guesl/laravel-admin":"^1.0.0"


#### 3. Install or update composer.
    composer install
    or
    composer update


#### 4. Init the guesl admin template files and make an example
    php artisan guesl:install
    php artisan guesl:make User --module=User --force

##### 4.1 Note: the model index js will get the schema info from database, make sure you have created the migrations already.

#### 5. Run yarn or npm to generate the js file for index list(datatable list)
    yarn install/ npm i
    yarn run dev/ npm run dev
    