# Employee Management System (EMS)

A complete web application for managing employees, attendance, leave requests, and roles. Built with a separated frontend and backend architecture.

## Project Summary
- Description: HR management web app with user registration, role-based access (admin, manager, employee), attendance check-in/out, leave management, and employee profiles.
- Goal: Provide an administrative dashboard and tailored interfaces for managers and employees to automate HR workflows.

## Key Responsibilities & Achievements (CV-friendly)
- Designed and implemented a full-stack EMS: RESTful API backend and reactive frontend.
- Implemented role-based access control and multi-role permissions (admin, manager, employee).
- Built database seeders and initial data scripts to bootstrap the system (see `backend/database/seeders/DatabaseSeeder.php`).
- Integrated Postman collection and test suites for API verification.

## Main Features
- User and role management
- Attendance system (check-in / check-out)
- Leave request lifecycle with approval states
- Employee reporting and status tracking
- JWT authentication and seeded demo accounts

## Tech Stack
- Backend: Laravel (PHP), Eloquent ORM
- Frontend: Vue.js + Vite
- Database: MySQL / MariaDB (configurable via `.env`)
- Testing & Docs: Postman collection, Pest/PHPUnit

## Project Structure (brief)
- `backend/`: server code, models, migrations, seeders, API controllers
- `frontend/`: Vue app built with Vite
- `postman/`: Postman collection and environment

## Setup & Run (quick)

Backend (PHP / Composer):

```bash
cd backend
composer install
copy .env.example .env   # Windows
# cp .env.example .env  # Unix-like
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Frontend (Node.js / npm):

```bash
cd frontend
npm install
npm run dev
```

## Default Credentials (seeded)
- Email: admin@ems.com  | Password: password
- See seeder: `backend/database/seeders/DatabaseSeeder.php` for other seeded users.

## Testing
- Run backend tests:

```bash
cd backend
php artisan test
# or
vendor/bin/pest
```

## API Documentation
- Import `postman/EMS.postman_collection.json` into Postman to explore endpoints and ready-made scenarios.

## Developer Notes
- Adjust database and JWT settings in `backend/.env`.
- Inspect domain logic in `app/Models` and custom exceptions in `app/Exceptions`.

## Next Steps (suggested)
- Create a concise CV bullet list in English based on the "Key Responsibilities & Achievements" section.
- Replace the Arabic README with this English version or keep both files.

---

If you want, I can now generate a one-page CV-ready bullet list in English extracted from this README.
