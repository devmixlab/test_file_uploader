Steps for installation(docker/sail)
- rename .env.example into .env
- composer update
- alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
- sail up --build
- sail artisan key:generate
- sail artisan migrate
- sail npm install
- sail npm run dev
- sail artisan storage:link
