# Pine Tree Management and Payment Tracking System

PTMPT is a web-based management system for Rwanda's forestry sector and NAEB. It records pine plantations, tree growth, estimated timber values, plantation locations, and payments made to plantation owners.

The application is built with:

- PHP without a framework
- MySQL and `mysqli`
- Bootstrap 5
- Chart.js
- Leaflet and OpenStreetMap
- PHP sessions for authentication

## Main features

- Role-based authentication and page access
- Plantation, owner, and buyer management
- Tree growth and quantity records
- Automatic timber value calculation
- Planting, cutting, and tree buying approval requests
- GIS map of plantation locations
- Payment creation, approval, and rejection
- Printable plantation, owner, payment, timber, and request reports
- Role-specific dashboards and charts
- Owner data isolation

## User roles

### Administrator

The administrator is focused on governance:

- Create and manage user accounts
- Generate every report type
- View system-wide dashboard statistics

### Forestry Officer

The forestry officer manages field information:

- Approve or reject planting license requests
- Approve or reject cutting license requests
- Approve or reject tree buying requests
- Register and update plantations
- Record tree species, quantity, age, and growth status
- View timber value estimates
- View plantation locations on the GIS map

### NAEB Officer

The NAEB officer manages financial records:

- Register NAEB officer and forestry officer accounts
- Create payments for plantation owners
- Approve or reject pending payments
- Filter payments by status
- View financial dashboard charts

### Plantation Owner

The owner has read-only access to their records:

- View their plantations
- View their tree records and growth stages
- View estimated timber value
- View their payment history and status
- Request approval before planting trees
- Request approval before cutting trees

Owner queries are filtered using the logged-in user's ID. Owners cannot view another owner's information.

### Buyer

The buyer can:

- Register a buyer account
- Submit requests to buy trees from available plantations
- Track whether a forestry officer approved or rejected each buying request

## How the system works

### Plantation workflow

1. A plantation owner submits a planting license request.
2. A forestry officer approves or rejects the request.
3. After approval, the forestry officer registers the plantation.
4. The plantation is linked to its owner.
5. Its district, sector, area, latitude, and longitude are recorded.
6. The location becomes available on the GIS map.

### Tree and timber workflow

1. A forestry officer selects a plantation.
2. They record the species, quantity, age, and growth status.
3. The system calculates the estimated timber value automatically:

```text
Estimated value = quantity × age × 45,000 RWF
```

Available growth statuses are:

- Seedling
- Growing
- Mature
- Ready for harvest

### Forest request workflow

1. Owners request permission to plant trees or cut trees.
2. Buyers request permission to buy trees from a plantation.
3. Forestry officers review all forest requests.
4. Only approved requests should continue to field work, cutting, or buying.
5. Rejected requests remain visible with their final status.

### Payment workflow

1. A NAEB officer creates a payment.
2. The plantation selection automatically determines the owner.
3. A new payment can be pending, approved, or rejected.
4. Pending payments can later be approved or rejected.
5. Owners can see payment results from their dashboard and payment history.

### Reports

The system provides five report types:

- Plantation report
- Owner report
- Payment report
- Timber value report
- Forest requests report

Reports are available to the administrator and can be printed using the **Print report** button.

## Requirements

Install the following software:

- PHP 8.0 or newer
- PHP `mysqli` extension
- MySQL 8 or MariaDB
- A modern web browser
- Internet access for Bootstrap, Chart.js, Leaflet, and map tiles

## Database configuration

The application uses the following connection in `conn.php`:

```php
$con = mysqli_connect('localhost', 'francois', '123', 'ptmpt');
```

If your MySQL username or password is different, update `conn.php`.

## Database schema

The database contains six related tables:

- `users` stores account details and user roles.
- `plantations` belongs to an owner from the `users` table.
- `trees` belongs to a plantation.
- `payments` belongs to both an owner and a plantation.
- `forest_requests` stores planting, cutting, and buying approval requests.
- `reports` records who generated each report.

The complete schema is:

```sql
CREATE DATABASE IF NOT EXISTS ptmpt
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ptmpt;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  role ENUM(
    'admin',
    'forestry_officer',
    'naeb_officer',
    'owner',
    'buyer'
  ) NOT NULL DEFAULT 'owner',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plantations (
  plantation_id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  plantation_name VARCHAR(150) NOT NULL,
  district VARCHAR(100) NOT NULL,
  sector VARCHAR(100) NOT NULL,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  area_size DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id)
    REFERENCES users(user_id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS trees (
  tree_id INT AUTO_INCREMENT PRIMARY KEY,
  plantation_id INT NOT NULL,
  species VARCHAR(100) NOT NULL,
  quantity INT NOT NULL,
  age_years INT NOT NULL,
  growth_status ENUM(
    'seedling',
    'growing',
    'mature',
    'ready_for_harvest'
  ) NOT NULL,
  estimated_value DECIMAL(15,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (plantation_id)
    REFERENCES plantations(plantation_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  plantation_id INT NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  payment_date DATE NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  status ENUM(
    'pending',
    'approved',
    'rejected'
  ) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id)
    REFERENCES users(user_id)
    ON DELETE RESTRICT,
  FOREIGN KEY (plantation_id)
    REFERENCES plantations(plantation_id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forest_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  requester_id INT NOT NULL,
  plantation_id INT NULL,
  request_type ENUM(
    'plant_license',
    'cut_license',
    'buy_trees'
  ) NOT NULL,
  species VARCHAR(100),
  quantity INT,
  area_size DECIMAL(10,2),
  reason TEXT NOT NULL,
  status ENUM(
    'pending',
    'approved',
    'rejected'
  ) NOT NULL DEFAULT 'pending',
  reviewed_by INT NULL,
  reviewed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (requester_id)
    REFERENCES users(user_id)
    ON DELETE RESTRICT,
  FOREIGN KEY (plantation_id)
    REFERENCES plantations(plantation_id)
    ON DELETE SET NULL,
  FOREIGN KEY (reviewed_by)
    REFERENCES users(user_id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
  report_id INT AUTO_INCREMENT PRIMARY KEY,
  report_type ENUM(
    'plantation',
    'owner',
    'payment',
    'timber_value',
    'requests'
  ) NOT NULL,
  generated_by INT NOT NULL,
  generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (generated_by)
    REFERENCES users(user_id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;
```

The same SQL is available in `schema.sql`, which should be treated as the executable schema file.

## Installation

### 1. Open the project directory

```bash
cd /home/francois/Documents/Leanr/projects/ptmtp
```

### 2. Start MySQL

On systems using `mysqld`:

```bash
sudo systemctl start mysqld
```

On systems using `mysql`:

```bash
sudo systemctl start mysql
```

### 3. Create the database tables

Import the included schema:

```bash
mysql -u francois -p < schema.sql
```

Enter the MySQL password when requested. The configured password is `123`.

The schema creates the `ptmpt` database and these tables:

- `users`
- `plantations`
- `trees`
- `payments`
- `forest_requests`
- `reports`

### 4. Seed demo data

This creates demo users for every role, one plantation, one tree record, one payment, and sample forest requests:

```bash
mysql -u francois -p ptmpt < seed.sql
```

All seeded accounts use this password:

```text
password123
```

Demo login emails:

- `admin@ptmpt.local`
- `naeb@ptmpt.local`
- `forestry@ptmpt.local`
- `owner@ptmpt.local`
- `buyer@ptmpt.local`

### 5. Start the PHP development server

```bash
php -S localhost:8000
```

Keep this terminal open while using the system.

### 6. Open the application

Visit:

```text
http://localhost:8000
```

## First-time administrator setup

If no administrator exists, the login page displays a first-administrator setup link.

Open:

```text
http://localhost:8000/setup_admin.php
```

Create the administrator account and then sign in through:

```text
http://localhost:8000/login.php
```

The setup page becomes unavailable after the first administrator is created.

## Recommended first-use sequence

1. Create the first administrator.
2. Sign in as administrator.
3. Sign in as NAEB officer to register Forestry Officer and NAEB Officer accounts.
4. Create an owner or buyer account, or ask them to register.
5. Owner submits a planting/cutting license request.
6. Buyer submits a tree buying request.
7. Forestry officer approves or rejects forest requests.
8. Forestry officer records approved plantation and tree information.
9. NAEB officer creates and processes payments.

## Important files

| File | Purpose |
|---|---|
| `conn.php` | MySQL connection |
| `schema.sql` | Database and table definitions |
| `seed.sql` | Demo users and sample records |
| `auth.php` | Authentication, role checks, and shared helpers |
| `login.php` | User login |
| `setup_admin.php` | First administrator setup |
| `dashboard.php` | Role-specific dashboard and charts |
| `plantations.php` | Plantation listing |
| `trees.php` | Tree records |
| `tree_save.php` | Tree saving and timber calculation |
| `timber_value.php` | Timber value summaries |
| `map.php` | Interactive plantation map |
| `payments.php` | Payment history and approval actions |
| `requests.php` | Forest planting, cutting, and buying approval workflow |
| `reports.php` | Printable reports |
| `users.php` | Administrator user management |
| `style.css` | Black, white, and green interface design |

## Security behavior

- Passwords are stored using `password_hash()`.
- Login uses `password_verify()`.
- Successful login regenerates the session ID.
- Protected pages verify the active session.
- Pages use role guards to block unauthorized roles.
- SQL input is escaped before being used.
- Owner-visible queries are filtered by the owner's user ID.

## Stopping the server

Return to the terminal running PHP and press:

```text
Ctrl+C
```

## Troubleshooting

### Database connection failed

Confirm MySQL is running and verify the credentials in `conn.php`.

Test the configured connection with:

```bash
mysql -u francois -p -D ptmpt
```

### Tables do not exist

Import the schema again:

```bash
mysql -u francois -p < schema.sql
```

### Charts or styles do not appear

The interface loads external CDN resources. Check your internet connection and refresh the browser with:

```text
Ctrl+Shift+R
```

### GIS map does not load

Leaflet and OpenStreetMap require internet access. Also confirm that plantation latitude and longitude values are valid.

### Port 8000 is already in use

Run the application on another port:

```bash
php -S localhost:8080
```

Then visit `http://localhost:8080`.

### PHP displays a PostgreSQL extension warning

The application uses MySQL, not PostgreSQL. A warning about the `pgsql` extension is an environment configuration issue and does not affect PTMPT when `mysqli` works correctly.
