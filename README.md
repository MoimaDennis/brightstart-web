# BrightStart Education Support – Website

PHP / MySQL website, JSON API and admin dashboard for **BrightStart**, a non-profit education initiative in South Africa. Staff and volunteers use it to manage students, programs, sessions, attendance, performance and donations.

**Module:** XISD6329 – Part 2 and 3 | **Live site:** https://cartoon-decency-unblessed.ngrok-free.dev/brightstart-web/login.php | **Project board:** https://dev.azure.com/ST104519320228/BrightStart-Part2

> The site is served from XAMPP through ngrok, because the Azure subscription hosting the original Azure App Service was disabled by the subscription administrator. The site is online whenever XAMPP and ngrok are running.

> Team: Dennis Moima (St10451932), Kgothatso Magatsela (St10448288), Keabetswe Modiba (St10438507), Chantal Mafa (St10444319)

## Demo accounts (sample data)

| Role | Email | Password |
|---|---|---|
| Admin | admin@brightstart.org | Admin@123 |
| Coordinator | coordinator@brightstart.org | Pass@1234 |
| Volunteer | lerato.khumalo@brightstart.org | Pass@1234 |

You can also click **Continue with Google** on the login page. New Google accounts start as *Volunteer* and must be approved by an Admin.

## Features

- Sign in with email + password (bcrypt-hashed) or Google single sign-on (Firebase)
- Registration with validation and Admin approval
- Settings: theme, text size, dashboard slider, profile, change password
- Help Centre (help.php)
- Students, volunteers, programs, sessions, enrolments, attendance, performance, donations, reports
- Role-based menus and a responsive phone layout

## Tech stack

| Part | Technology |
|---|---|
| Backend | PHP 8.x |
| Database | MySQL 8 (XAMPP locally; Azure Database for MySQL when hosted on Azure) |
| Front end | HTML, CSS, JavaScript |
| Authentication | PHP sessions + bcrypt, Firebase Authentication |
| Hosting | XAMPP + ngrok (Azure App Service workflow included, currently disabled) |
| DevOps | Azure Boards, Azure Repos, GitHub, GitHub Actions |
| Tests | PHPUnit 10 |

## Database

10 tables, created by `db/schema.sql` (with 10+ sample rows each). They use primary keys, foreign keys, unique keys and CHECK rules.

![ERD](docs/erd.png)

## API

All endpoints are under `/api/` and return JSON `{ "success": true/false, ... }`. The Android app calls the same API.

Base URL: `https://cartoon-decency-unblessed.ngrok-free.dev/brightstart-web/api/`

| Endpoint | Method | Purpose |
|---|---|---|
| login.php, register.php, sso_login.php | POST | Sign in / create account / Google sign-in |
| get_settings.php, update_settings.php | GET / POST | User settings |
| dashboard_stats.php | GET | Dashboard totals |
| get_students.php?search= | GET | List / search students |
| get_admin_users.php, approve_user.php, reject_user.php | GET / POST | Admin: approve or reject new accounts |
| add_, update_, delete_ + resource name | POST / DELETE | Create, edit, delete records |

## Mobile app (Android, Kotlin)

The Android app uses this website's API, so both share one database.

- Repository: https://github.com/MoimaDennis/BrightStart-App
- Features: email login, registration, Google single sign-on, dashboard with the same hero banners, all module lists, settings (theme, text size, banner slider) and admin tools (register student, approve users)
- Demo video: About to upload

## Run it locally (XAMPP)

1. Install XAMPP and start **Apache** and **MySQL**.
2. Copy this folder into `C:\xampp\htdocs\` and name it `brightstart-web`.
3. Open http://localhost/phpmyadmin, click **Import**, choose `db/schema.sql`, click **Import**.
4. Open http://localhost/brightstart-web/ and sign in with a demo account.

## Share it online with ngrok

1. Install ngrok and run `ngrok config add-authtoken YOUR_TOKEN` once.
2. Run `ngrok http --url=cartoon-decency-unblessed.ngrok-free.dev 80` and leave the window open.
3. Open https://cartoon-decency-unblessed.ngrok-free.dev/brightstart-web/login.php
4. In Firebase (Authentication, Settings, Authorized domains) the ngrok domain must be added so Google sign-in works.

## Automated testing and CI/CD

- `.github/workflows/ci.yml` runs on every push: PHP syntax check, then builds the MySQL database, starts the site and runs PHPUnit.
- The Azure deployment workflow deploys `main` to Azure App Service when the subscription is active.
- Run tests yourself: `composer install` then `vendor/bin/phpunit --testdox`

## DevOps life cycle

| Phase | Tool |
|---|---|
| Plan | Azure Boards |
| Code | Git + Azure Repos + GitHub (commits linked with AB#id) |
| Build | GitHub Actions |
| Test | PHPUnit |
| Deploy | XAMPP + ngrok (GitHub Actions to Azure App Service when available) |
| Monitor | Azure App Service logs and metrics; ngrok request inspector |

## Known limitations

- Some Part 1 API endpoints are open (no token). Use sample data only.
- The Azure subscription was disabled, so the site runs from XAMPP through ngrok and is only online while that computer is running.

## Licence

Educational project for the Independent Institute of Education (IIE). All sample data is fictional.