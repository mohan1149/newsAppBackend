## About News Aggregator 

News Aggregator website pulls articles from various sources and displays them in a clean,
easy-to-read format

## How to setup

Clone the repository from "https://github.com/mohan1149/newsAppBackend.git"
$cd newsAppBackend
$composer install
cp env.example .env

## Set up Database
Update following as per your configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=databse_name
DB_USERNAME=user_name
DB_PASSWORD=password

## Run DB migrations
$php artisan migrate

## Start Application 
$php artisan serve



