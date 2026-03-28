## REPOSITORY

1. GITHUB
   - Link: https://github.com/diky-satria/interview_nexlaw
2. PREREQUISITE
   - php version: >= 8.1.25
   - composer version: >= 2.9.2
   - npm version: 10.9.2
   - node version: >= 22.17.0

## HOW TO SETUP THIS PROJECT?

1. BACKEND
   - open your terminal
   - clone this project in your local directory: git clone https://github.com/diky-satria/interview_nexlaw.git
   - go to backend: cd interview_nexlaw/backend/
   - run this: composer install
   - open your local phpmyadmin then create this database name: interview_nexlaw
   - run this to generate database table: php artisan migrate
   - run this: php artisan serve
2. FRONTEND
   - open new terminal
   - go to frontend: cd {YOUR_DIRECTORY}/interview_nexlaw/frontend/
   - run this: npm install
   - run this: npm run dev

## NOTE (if your backend port is not 8000, please change VITE_API_URL in .env frontend with your backend port)
