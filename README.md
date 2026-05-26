# COS30043 Modern Web Application



## Mercury link:
https://mercury.swin.edu.au/cos30043/s105385294/MyWay/#/ 


## Admin account for testing:
Email: **newadmin@example.com**

Password: **Password123!**

## Project Overview

This project is a full-stack web application developed for COS30043 using:

* **Frontend:** Vue 3 + Vite
* **Backend:** PHP (REST-style API)
* **Database:** MariaDB (Swinburne Feenix)

The system demonstrates a **content-based platform** with:

* User authentication (register/login/logout)
* Role-based access control (admin, adminstaff, user)
* Content management (CRUD)
* Comment system
* Like and favourite system
* User profile management

---

## Project Structure

```
COS30043-MODERN-WEBAPP/
├── api/                  # PHP backend (REST-style API)
│   ├── db.php
│   ├── helpers.php
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── users.php
│   ├── profile.php
│   ├── contents.php
│   ├── comments.php
│   ├── likes.php
│   └── favourites.php
├── src/                  # Vue frontend source code
├── public/
├── index.html
├── package.json
├── vite.config.js
└── README.md
```

---

## Prerequisites

* Node.js (v18+ recommended)
* npm
* PHP (for local backend testing)

---

## Setup Instructions

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd COS30043-MODERN-WEBAPP
```

---

### 2. Install frontend dependencies

```bash
npm install
```

---

### 3. Run frontend

```bash
npm run dev
```

Open:

```
http://localhost:5173
```

---

### 4. Run backend (local testing)

```bash
php -S localhost:8000
```

API base URL:

```
http://localhost:8000/api
```

---

## Authentication

The system uses **token-based authentication**.

After login or registration, a token is returned:

```json
{
  "token": "xxxxx",
  "user": { ... }
}
```

For protected APIs, include:

```
Authorization: Bearer <token>
```

---

## API Documentation

### Auth

```
POST   /api/login.php
POST   /api/register.php
POST   /api/logout.php
```

---

### User Management (Admin only)

```
GET    /api/users.php
PUT    /api/users.php        (change role / restore user)
DELETE /api/users.php        (soft delete user)
```

---

### Profile (Current User)

```
GET    /api/profile.php
PUT    /api/profile.php
```

---

### Content

```
GET    /api/contents.php
GET    /api/contents.php?id=1
POST   /api/contents.php
PUT    /api/contents.php
DELETE /api/contents.php
```

---

### Comment

```
GET    /api/comments.php?contentId=1
POST   /api/comments.php
DELETE /api/comments.php
```

---

### Like

```
GET    /api/likes.php?contentId=1
POST   /api/likes.php
```

---

### Favourite

```
GET    /api/favourites.php
POST   /api/favourites.php
```

---

## Role Permissions

### Guest

* View content list
* View content details
* View comments

---

### User

* Update personal profile
* Comment and reply
* Like / unlike content
* Add / remove favourites
* View favourite list

---

### Adminstaff

* Create content
* Update content
* Delete content
* Delete comments

---

### Admin

* Manage users
* Change roles
* Soft delete and restore users
* Full content control
* Delete comments

---

## Database

The project connects to Swinburne Feenix MariaDB:

```php
$host = "feenix-mariadb.swin.edu.au";
$dbnm = "s105385294_db";
```

⚠️ **Important:**

Do NOT commit your database password.

---

## Development Notes

* Backend follows REST-style API design
* Token is stored on frontend (localStorage / Postman environment)
* All protected routes require Authorization header
* Content uses soft delete (`isDeleted`)
* Users use soft delete (`isActive`)

---

## Scripts

```bash
npm run dev
npm run build
npm run preview
```

---

## Deployment

* Backend deployed on Mercury (PHP environment)
* Frontend can be deployed separately or hosted via Mercury static files
* No additional backend setup required

---

## Current Features

* User authentication (register / login / logout)
* Role-based access control
* Content CRUD system
* Comment system with reply support
* Like system (toggle)
* Favourite system (toggle)
* User profile management

---

## Future Improvements

* Frontend UI enhancement
* Vue Router integration
* Better error handling
* Pagination UI
* Advanced search & filtering
* Token expiration handling

---

## Notes for Team Members

* Do NOT commit sensitive data
* Use consistent API endpoints
* Keep frontend and backend aligned
* Use token-based authentication correctly
