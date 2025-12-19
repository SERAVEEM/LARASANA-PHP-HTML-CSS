🚀 **LARASANA**

**Native PHP Web Application (PHP, HTML, CSS)**

LARASANA is a simple web application built using **native PHP** without any major framework. It combines PHP for backend logic, HTML and CSS for the user interface, and MySQL as the database.

## 📌 Project Overview

This application provides basic user management features such as registration and login, along with simple admin initialization using a seed script.

**Main technologies used:**

* PHP (native, no framework)
* HTML & CSS
* MySQL
* Apache (recommended via XAMPP or similar)

---

## 📂 Project Structure

```
/
├─ .github/               # GitHub-related configuration
├─ .vscode/               # VS Code settings
├─ assets/                # Static assets (CSS, images, etc.)
├─ config/
│  └─ database.php        # Database configuration
├─ database/
│  └─ schema.sql          # Database schema
├─ public/                # Publicly accessible PHP files
├─ src/                   # Core application logic
├─ uploads/
│  └─ products/           # Uploaded product images
├─ .htaccess              # Apache configuration
├─ adminseed.php          # Admin account seeding script
├─ package.json
├─ package-lock.json
└─ README.md
```

---

## 🗄️ Database Setup

The application uses **MySQL** as its database.

1. Create a new database in MySQL.
2. Import the SQL file located at:

   ```
   database/schema.sql
   ```
3. Configure database credentials in:

   ```
   config/database.php
   ```

---

## ⚙️ Installation & Running the Project

Follow these steps to run the project locally:

### 1️⃣ Clone the Repository

```
git clone https://github.com/SERAVEEM/LARASANA-PHP-HTML-CSS.git
```

### 2️⃣ Setup Local Server

* Use **XAMPP**, **Laragon**, or any Apache + PHP environment.
* Place the project inside the `htdocs` directory (if using XAMPP).

### 3️⃣ Configure Database

Edit `config/database.php`:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "your_database_name";
```

### 4️⃣ Run the Application

* Start Apache & MySQL
* Open your browser and access:


```
cd public
```
php -S localhost:8000
```
http://localhost/LARASANA-PHP-HTML-CSS/public
```

---

## 👤 Admin Account Seeding

To create an admin account automatically:

1. Run the following file once in your browser:

```
adminseed.php
```
and then you have to manually seed into the database
```

⚠️ **Important:**
Delete or disable `adminseed.php` after running it to avoid security issues.

---

## ✨ Features

* User registration
* User login & logout
* Admin account seeding
* File upload support (product images)
* Simple and clean folder structure
* No external PHP framework

---

## ⚠️ Notes & Limitations

* Passwords may not be hashed (not recommended for production)
* No advanced role-based access control
* No MVC pattern implementation
* Intended for learning or small-scale projects

---

## 📄 License

This project is open for learning and development purposes.
You are free to modify and improve it.
---
