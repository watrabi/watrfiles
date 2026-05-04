# watrfiles
funny recreation of a old project\
https://github.com/Water-Boi/Water-Files
# Setup
configure .env-example and rename it to .env

install composer and run
`composer install`

then run
`vendor/bin/phinx init` (this is to init the migration system)\
after that fill out whats in phinx.php (per your configuration and whatnot)\
\
and then run\
`vendor/bin/phinx migrate` (this puts all the migrations in)\
\
and you should be good to go\

reminder: this uses twig now\
so try and keep your templates twig like\

that's all for now. if you would like to contribute the fork and pr buttons above\
also if you want to this has the funny laravel thing of returning an array and it becoming a json response
