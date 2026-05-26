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

## Notes for Team Members

* Do NOT commit sensitive data
* Use consistent API endpoints
* Keep frontend and backend aligned
* Use token-based authentication correctly
