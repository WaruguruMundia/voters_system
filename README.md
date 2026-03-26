# 🗳️ Voters System

> A Secure Electronic Voting Platform

**COMP 440 – Group Project | PHP + MySQL + XAMPP**

---

## 📋 Project Overview

The Voters System is a secure web-based electronic voting application built with PHP and MySQL. It provides a clean, user-friendly interface for conducting elections, enabling voters to cast their ballots online and allowing administrators to manage the entire voting process  from registering voters and candidates to monitoring real-time results.

This system was developed as a group project for COMP 440 and demonstrates full-stack PHP development with session management, database transactions, and role-based access control.

---

## ✨ Features

### Voter Portal
- 🔑 Voter authentication using unique Voter IDs
- 🗳️ Ballot paper with candidates grouped by position
- 🚫 One-vote-per-voter enforcement (database + session level)
- ✅ Vote confirmation dialog before final submission
- 🔄 Secure database transaction all votes committed atomically
- 🙏 Automatic redirect after voting with a Thank You page

### Admin Panel
- 🛡️ Secure admin login with MD5-hashed password authentication
- 📊 Dashboard with live statistics: total voters, votes cast, turnout percentage
- 📈 Visual voter turnout progress bar
- 🏆 Real-time vote results with bar charts per position and trophy icon for the leader
- 👥 Manage Voters: add new voters, view voting status, delete voters (removes their votes too)
- 🏅 Manage Candidates: add candidates per position, view vote counts, delete candidates
- 💡 Smart position data list reuse existing positions or create new ones

---

## 🛠️ Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Backend | PHP 7.4+ | Server-side logic & sessions |
| Database | MySQL (via MySQLi) | Data storage & queries |
| Frontend | HTML5 + CSS3 | UI layout & styling |
| Server | Apache (XAMPP) | Local development server |
| Admin Tool | phpMyAdmin | Database management |

---

## 📁 Project Structure

```
voters_system/
├── config.php              # DB connection & session init
├── database.sql            # Schema + seed data
├── index.php               # Landing page (Voter / Admin portal links)
├── voter_login.php         # Voter authentication page
├── vote.php                # Ballot page — cast vote
├── thankyou.php            # Post-vote confirmation page
├── logout.php              # Voter session logout
├── css/
│   └── style.css           # Global stylesheet
└── admin/
    ├── auth_guard.php      # Admin session protection
    ├── login.php           # Admin login page
    ├── logout.php          # Admin session logout
    ├── navbar.php          # Admin navigation bar
    ├── dashboard.php       # Stats, turnout & live results
    ├── voters.php          # Voter management (CRUD)
    └── candidates.php      # Candidate management (CRUD)
```

---

## 🗄️ Database Schema

**Database name:** `voters_system`

| Table | Column | Type | Description |
|-------|--------|------|-------------|
| `admin` | `id` | INT (PK) | Auto-increment primary key |
| `admin` | `username` | VARCHAR(50) | Unique admin username |
| `admin` | `password` | VARCHAR(255) | MD5-hashed password |
| `voters` | `voter_id` | VARCHAR(20) | Unique voter identifier e.g. VOT-001 |
| `voters` | `full_name` | VARCHAR(100) | Full name of voter |
| `voters` | `has_voted` | TINYINT(1) | 0 = not voted, 1 = voted |
| `candidates` | `full_name` | VARCHAR(100) | Candidate's full name |
| `candidates` | `position` | VARCHAR(100) | Position being contested |
| `candidates` | `votes` | INT | Vote tally for this candidate |
| `votes` | `voter_id` | VARCHAR(20) | Reference to voter who voted |
| `votes` | `candidate_id` | INT | Reference to candidate voted for |
| `votes` | `voted_at` | TIMESTAMP | Auto-set timestamp of vote |

---

## ⚙️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org) (Apache + MySQL)
- PHP 7.4 or higher (included in XAMPP)
- A web browser
- Git (optional, for cloning)

---

### Step 1 — Get the Code

**Option A — Clone via HTTPS:**
```bash
git clone https://github.com/WaruguruMundia/voters_system.git
```

**Option B —** Download the ZIP from GitHub and extract it.

---

### Step 2 — Start XAMPP

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Confirm both show green status ✅

---

### Step 3 — Set Up the Database

1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Enter database name: `voters_system`
4. Click **Create**
5. Click on the `voters_system` database
6. Click the **Import** tab
7. Click **Choose File** and select `database.sql` from your project folder
8. Click **Go** all tables and sample data will be imported ✅

---

### Step 4 — Configure Database Connection

Open `config.php` and confirm the connection settings match your XAMPP:

```php
$conn = new mysqli('localhost', 'root', '', 'voters_system');
```

If XAMPP MySQL runs on a custom port (e.g. 3307), update accordingly:

```php
$conn = new mysqli('127.0.0.1:3307', 'root', '', 'voters_system');
```

---

### Step 5 — Run the Project

Navigate to your project folder in the terminal and run:

```bash
php -S localhost:8000
```

Then open your browser and visit:

```
http://localhost:8000
```

---

## 🚀 How to Use

### As a Voter
1. Go to `http://localhost:8000`
2. Click **Voter Login**
3. Enter your Voter ID (e.g. `VOT-001`)
4. Select one candidate per position on the ballot
5. Click **Submit My Vote**
6. Confirm the dialog your vote is recorded and you are redirected to the Thank You page

### As an Admin
1. Go to `http://localhost:8000`
2. Click **Admin Panel**
3. Login with username `admin` and password `admin123`
4. Use the **Dashboard** to monitor voter turnout and live results
5. Go to **Manage Voters** to add or remove registered voters
6. Go to **Manage Candidates** to add candidates per position

---

## 🔐 Default Credentials

| Role | Username / ID | Password |
|------|--------------|----------|
| Admin | `admin` | `admin123` |
| Sample Voter 1 | `VOT-001` | N/A (ID only) |
| Sample Voter 2 | `VOT-002` | N/A (ID only) |
| Sample Voter 3 | `VOT-003` | N/A (ID only) |

> ⚠️ **Important:** Change the default admin password before deploying to any live or shared environment.

---

## 🧪 Sample Data (Pre-loaded)

### Voters
| Voter ID | Full Name |
|----------|-----------|
| VOT-001 | Alice Johnson |
| VOT-002 | Bob Smith |
| VOT-003 | Carol White |
| VOT-004 | David Brown |
| VOT-005 | Eve Davis |

### Candidates
| Name | Position |
|------|----------|
| James Mwangi | President |
| Grace Wanjiku | President |
| Peter Kamau | Vice President |
| Mary Njeri | Vice President |

---

## 🔒 Security Notes

- **Double-vote prevention** `has_voted` flag is verified on every ballot page load at the database level; session manipulation cannot bypass it
- **Candidate validation** before recording a vote, the system verifies each selected candidate actually belongs to the claimed position
- **Atomic transactions** all votes within a single ballot submission are wrapped in a MySQL transaction; partial votes are rolled back on error
- **SQL injection prevention** all database queries use prepared statements with bound parameters
- **Session protection** admin pages are protected by `auth_guard.php` which checks for a valid admin session on every request
- **XSS prevention** all user-facing output is escaped using `htmlspecialchars()`

> ⚠️ **Note:** MD5 is used for admin password hashing in this version. For production use, upgrade to PHP `password_hash()` with bcrypt.

---

## 🛠️ Troubleshooting

| Error / Issue | Solution |
|---------------|----------|
| `mysqli` connection refused | Start MySQL in XAMPP and verify the port in `config.php` (default: `3306`) |
| Unknown database `voters_system` | Create the database in phpMyAdmin and import `database.sql` |
| Invalid Voter ID on login | Check the voter exists in the `voters` table in phpMyAdmin |
| "Already voted" message | Reset with `UPDATE voters SET has_voted=0;` and `DELETE FROM votes;` in phpMyAdmin |
| Blank page / PHP errors | Add `error_reporting(E_ALL); ini_set('display_errors', 1);` at the top of `config.php` |
| Port conflict on 8000 | Use a different port: `php -S localhost:9000` |

---

## 🤝 Contributing

1. Fork the repository on GitHub
2. Create a new branch: `git checkout -b feature/your-feature-name`
3. Make your changes and commit: `git commit -m "Add: description of change"`
4. Push to your fork: `git push origin feature/your-feature-name`
5. Open a **Pull Request** on GitHub

---

## 📄 License & Credits

This project was built for academic purposes as part of **COMP 440**.

- Built with PHP, MySQL, and plain CSS
- Database managed via phpMyAdmin / XAMPP
- Repository: [github.com/WaruguruMundia/voters_system](https://github.com/WaruguruMundia/voters_system)

---

<p align="center">Made with ❤️ for COMP 440</p>
