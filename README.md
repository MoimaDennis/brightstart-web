# BrightStart (Web App)

PHP/MySQL web platform for **BrightStart**, an education initiative. It serves a public-facing marketing site alongside an admin dashboard for managing students, programs, attendance, donations, volunteers, and performance — and exposes a JSON API consumed by the [BrightStart Android app](https://github.com/<your-username>/brightstart-android).

## Features

**Public site**
- Landing page with program info and image slider (`index.php`)
- Donation page (`donate.php`)
- Admin registration and login (`register.php`, `login.php`)

**Admin dashboard**
- Dashboard overview (`dashboard.php`)
- Student management (`students.php`)
- Enrolments (`enrolments.php`)
- Attendance tracking (`attendance.php`)
- Programs & sessions (`programs.php`, `sessions.php`)
- Volunteer management (`volunteers.php`)
- Donations (`donations.php`)
- Performance tracking (`performance.php`)
- Reports (`reports.php`)
- Admin user management with approval workflow (`admin_users.php`)

**API** (`/api`)

A REST-style JSON API backing both the web dashboard and the Android app, with `get_`, `add_`, `update_`, and `delete_` endpoints for: students, enrolments, attendance, programs, sessions, volunteers, donations, performance, and admin users. Also includes `login.php`, `register.php`, `approve_user.php`, and `reject_user.php` for auth and account approval.

## Tech Stack

- **Backend:** PHP (PDO + MySQL)
- **Frontend:** Server-rendered PHP templates, plain CSS, vanilla JS where needed
- **Auth:** PHP sessions, with `password_hash`/`password_verify` for credentials and an admin-approval gate before login

## Project Structure
BrightStart/

├── api/                # JSON API endpoints (CRUD per resource + auth)

│   └── db.php           # Database connection

├── includes/

│   ├── DBConn.php        # Shared DB connection for page templates

│   ├── auth_guard.php     # Session/login guard for protected pages

│   └── nav.php            # Shared nav partial

├── css/

│   └── style.css

├── images/

├── index.php            # Public landing page

├── donate.php

├── login.php / register.php / logout.php

├── dashboard.php

├── students.php / enrolments.php / attendance.php

├── programs.php / sessions.php

├── volunteers.php / donations.php / performance.php

├── reports.php

└── admin_users.php


## Setup

### Prerequisites

- PHP 7.4+ (with PDO MySQL extension)
- MySQL/MariaDB
- A local server stack such as XAMPP, WAMP, MAMP, or `php -S`

### Database

No SQL schema file is currently checked into the repo. You'll need a `brightstart` database with (at minimum) an `administrator` table matching the fields referenced in `api/login.php` (`Email`, `PasswordHash`, `Verified`, etc.), plus tables for students, enrolments, attendance, programs, sessions, volunteers, donations, and performance records used by the corresponding API endpoints.

> Consider adding a `schema.sql` to this repo so the database can be recreated from scratch.

### Configuration

Database credentials are set in `api/db.php`:

```php
$host = "localhost";
$db   = "brightstart";
$user = "root";
$pass = "";
```

These are local development defaults — update them for your environment, and consider moving them to environment variables before deploying anywhere public.

### Running locally

```bash
git clone https://github.com/<your-username>/brightstart-web.git
cd brightstart-web
```

With XAMPP/WAMP/MAMP: place the folder in your server's web root (e.g. `htdocs/`) and visit `http://localhost/BrightStart/`.

Or with PHP's built-in server:

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000`.

### Connecting the Android app

The Android client expects this API to be reachable at a configured base URL (see its `BASE_URL` build config field). If testing on a real device, expose your local server via a tool like ngrok and point the app at that URL.

## Security Notes

- Passwords are hashed with PHP's `password_hash`; never store plaintext passwords.
- New admin registrations require approval (`Verified` flag) before login succeeds.
- The default DB credentials in `api/db.php` are for local development only — replace them before any public deployment.

## License

No license specified yet — add one (e.g. MIT) if you intend to make this publicly reusable.
