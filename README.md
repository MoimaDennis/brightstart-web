# BrightStart Education Support – Website

PHP / MySQL website, JSON API and admin dashboard for **BrightStart**, a non-profit education initiative in South Africa. Staff and volunteers use it to manage students, programs, sessions, attendance, performance and donations.

**Module:** XISD6329 – Part 2 | **Live site:** https://
BrightStart-Part2.azurewebsites.net | **Project board:** https://dev.azure.com/ST104519320228
/BrightStart-Part2

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
| Database | MySQL 8 (Azure Database for MySQL) |
| Front end | HTML, CSS, JavaScript |
| Authentication | PHP sessions + bcrypt, Firebase Authentication |
| Hosting | Azure App Service |
| DevOps | Azure Boards, GitHub, GitHub Actions |
| Tests | PHPUnit 10 |

## Database

10 tables, created by `db/schema.sql` (with 10+ sample rows each). They use primary keys, foreign keys, unique keys and CHECK rules.

![ERD](docs/erd.png)

## API

All endpoints are under `/api/` and return JSON `{ "success": true/false, ... }`.

| Endpoint | Method | Purpose |
|---|---|---|
| login.php, register.php, sso_login.php | POST | Sign in / create account / Google sign-in |
| get_settings.php, update_settings.php | GET / POST | User settings |
| dashboard_stats.php | GET | Dashboard totals |
| get_students.php?search= | GET | List / search students |
| add_, update_, delete_ + resource name | POST / DELETE | Create, edit, delete records |

## Run it locally (XAMPP)

1. Install XAMPP and start **Apache** and **MySQL**.
2. Copy this folder into `C:\xampp\htdocs\`.
3. Open http://localhost/phpmyadmin, click **Import**, choose `db/schema.sql`, click **Import**.
4. Open http://localhost/brightstart-web
/ and sign in with a demo account.

## Automated testing and CI/CD

- `.github/workflows/ci.yml` runs on every push: PHP syntax check, then builds the MySQL database, starts the site and runs PHPUnit.
- The Azure deployment workflow deploys `main` to Azure App Service automatically.
- Run tests yourself: `composer install` then `vendor/bin/phpunit --testdox`

## DevOps life cycle

| Phase | Tool |
|---|---|
| Plan | Azure Boards |
| Code | Git + GitHub (commits linked with AB#id) |
| Build | GitHub Actions |
| Test | PHPUnit |
| Deploy | GitHub Actions to Azure App Service |
| Monitor | Azure App Service logs and metrics |

## Known limitations

- Some Part 1 API endpoints are open (no token). Use sample data only.
- Free Azure tiers can be slow on the first request after idle time.

## Licence

Educational project for the Independent Institute of Education (IIE). All sample data is fictional.
