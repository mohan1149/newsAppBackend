## About News Aggregator 

News Aggregator website pulls articles from various sources and displays them in a clean,
easy-to-read format

## How to setup

Clone the repository from "https://github.com/mohan1149/newsAppBackend.git"   
`git clone https://github.com/mohan1149/newsAppBackend.git`  
`cd newsAppBackend`   
`composer install`  
`cp env.example .env`  

## Set up Database
Update following as per your configuration  
DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=databse_name  
DB_USERNAME=user_name  
DB_PASSWORD=password  

## Run DB migrations
`php artisan migrate`  

## Start Application 
`php artisan serve`  

## Docker Set up
Replace <your_app_key>, <your_db_name>, <your_db_username>, <your_db_password>, and <your_db_root_password> with the actual values for your application and database.  

    Open a terminal in the same directory as the Dockerfile.  
    Build the Docker image by running the command `docker build -t laravel-app .`, This will create an image with the tag laravel-app.   
    After the build is complete, you can run a container   


Open a terminal in the same directory as the Docker Compose file and run the command `docker-compose up -d`  to start the Laravel application and MySQL database containers.  

Make sure you have Docker and Docker Compose installed on your machine before running the above commands.  

For more details reach me at mohan.velegacherla@gmail.com



