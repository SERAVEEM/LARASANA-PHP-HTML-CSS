Larasana - Pure PHP backend (minimal)
------------------------------------
This archive contains a basic pure-PHP backend project including:
- Authentication (register/login) using PHP sessions
- Role support (user / admin)
- Admin-only CRUD for products
- Simple PDO-based DB connection (MySQL)
- Example public pages and admin pages

How to use
1. Extract files to your PHP host (document root should point to `public/`).
2. Update `config/database.php` with your DB credentials.
3. Import `database/schema.sql` into MySQL.
4. Visit `/register.php` and create an account (or insert an admin row in DB).
5. Login and test pages.

Notes
- This is a minimal, educational template. Add CSRF protection, input validation,
  file upload security, and stronger error handling for production.
