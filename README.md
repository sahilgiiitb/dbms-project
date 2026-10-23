# Municipal Property Tax System

Database Management Systems (DBMS) Assignment Project  
Indian Institute of Information Technology Bhopal (IIIT Bhopal)  
Student Name: Sahil Gupta  
Scholar Number: 25U020128  

---

## 1. Project Overview

The Municipal Property Tax System is a web-based database application designed for municipal offices to manage property owners, property records, yearly tax assessments, and tax payment collections.

In paper-based or un-normalized spreadsheet records, municipalities frequently encounter duplicate entries, mismatched records, and errors in calculating pending dues when property owners make partial payments. This system resolves these issues using a 3NF relational database in MySQL with a plain PHP backend and a clean HTML/CSS frontend.

---

## 2. Technology Stack

- Frontend: HTML5 and CSS3 (Zero JavaScript, no external UI frameworks, no icon libraries)
- Backend: PHP (Procedural style, mysqli extension with prepared statements)
- Database: MySQL / MariaDB (via XAMPP)
- Web Server: Apache (XAMPP)

---

## 3. System Architecture

The project follows a standard 3-tier architecture:

1. Presentation Tier (HTML and CSS):
   - Pure HTML pages styled by a single stylesheet (css/style.css).
   - Form inputs use HTML attributes (required, pattern, min, max, type="date") for initial validation.
   - All delete operations use a two-step confirmation page (delete.php) to eliminate the need for JavaScript confirm dialogs.

2. Application Tier (PHP):
   - Server-side validation checks every input against business constraints.
   - All dynamic SQL statements are executed using prepared statements (mysqli_prepare, mysqli_stmt_bind_param, mysqli_stmt_execute) to protect against SQL injection.
   - All output values are escaped using htmlspecialchars() to prevent cross-site scripting (XSS).
   - Post/Redirect/Get (PRG) pattern is implemented with session messages to avoid accidental double form submissions on page refresh.

3. Data Tier (MySQL):
   - Relational database named property_tax.
   - Enforces referential integrity through foreign keys and check constraints.
   - Derived fields (paid amount, pending due, and payment status) are dynamically computed using SQL aggregates and are never stored in the database.

---

## 4. Database Schema and Design

Database Name: property_tax

The schema consists of exactly four normalized tables:

### 4.1 Tables and Columns

1. PropertyOwners
   - owner_id (INT, Primary Key, AUTO_INCREMENT)
   - owner_name (VARCHAR(100), NOT NULL)
   - phone (CHAR(10), NOT NULL)
   - email (VARCHAR(100), NULL)

2. Properties
   - property_id (INT, Primary Key, AUTO_INCREMENT)
   - owner_id (INT, Foreign Key referencing PropertyOwners(owner_id))
   - property_address (VARCHAR(255), NOT NULL)
   - property_type (VARCHAR(30), NOT NULL) [Residential, Commercial, Industrial, Agricultural]

3. TaxAssessments
   - assessment_id (INT, Primary Key, AUTO_INCREMENT)
   - property_id (INT, Foreign Key referencing Properties(property_id))
   - assessment_year (INT, NOT NULL)
   - tax_amount (DECIMAL(10,2), NOT NULL, CHECK (tax_amount > 0))

4. Payments
   - payment_id (INT, Primary Key, AUTO_INCREMENT)
   - assessment_id (INT, Foreign Key referencing TaxAssessments(assessment_id))
   - payment_date (DATE, NOT NULL)
   - amount_paid (DECIMAL(10,2), NOT NULL, CHECK (amount_paid > 0))

### 4.2 Entity Relationships

- PropertyOwners (1) to Properties (M): One owner can own multiple properties.
- Properties (1) to TaxAssessments (M): One property receives yearly tax assessments.
- TaxAssessments (1) to Payments (M): One assessment can receive multiple partial payments.

### 4.3 Database Normalization

- 1NF: Every attribute stores an atomic value, and there are no repeating groups.
- 2NF: All non-key attributes are fully functionally dependent on their table primary key.
- 3NF: No transitive dependencies exist. Paid amount, pending due, and payment status (Paid, Partial, Unpaid) are derived on demand via SUM(amount_paid) rather than stored as redundant columns.

---

## 5. File Structure and Responsibilities

```
property-tax-system/
|-- index.php             Dashboard showing summary totals and recent 5 payments
|-- owners.php            Add, list, and search property owners
|-- owner_edit.php        Edit details for an existing owner
|-- properties.php        Add, list, and filter properties by type
|-- property_edit.php     Edit details for an existing property
|-- assessments.php       Create assessments, filter by year/status, and view calculated balances
|-- assessment_edit.php   Update tax amount (permitted only when paid amount is 0)
|-- payments.php          Record payment transactions and view payment ledger
|-- delete.php            Safe two-step confirmation page with foreign key dependency checks
|-- reports.php           Four SQL analytical reports with aggregate queries
|-- terms.php             Terms of Use static page
|-- privacy.php           Privacy Policy static page
|-- includes/
|   |-- db.php            Database connection handler and session initializer
|   |-- header.php        HTML head, stylesheet link, navigation bar, and session alerts
|   |-- footer.php        Footer navigation and closing tags
|-- css/
|   |-- style.css         Complete styling without shadows, animations, or icon packs
|-- sql/
|   |-- schema.sql        DDL script for database creation and table definitions
|   |-- sample_data.sql   Realistic Indian sample dataset for immediate testing
|-- README.md             Team documentation and setup instructions
```

---

## 6. Business Rules Enforced in the System

1. Owner Phone Number: Must contain exactly 10 numeric digits.
2. Owner Email: Optional, but if entered, it must pass email format validation.
3. Tax Amount: Must be a positive numeric value greater than zero.
4. Unique Yearly Assessment: A property can have only one tax assessment for a given year. Secondary assessments for the same property and year are rejected.
5. Payment Amount: Must be greater than zero and cannot exceed the current pending due of that assessment.
6. Payment Date: Cannot be set to a future date.
7. Edit Restriction: An assessment tax amount cannot be edited once any payment has been recorded against it.
8. Deletion Protection: Records referenced by other tables cannot be deleted. An owner with properties, a property with assessments, or an assessment with payments cannot be removed until child records are handled.

---

## 7. How to Set Up and Run on XAMPP

### Prerequisites
- XAMPP installed on your machine (includes Apache, PHP, and MySQL/MariaDB).
- Any standard web browser (Chrome, Firefox, Edge).

### Step 1: Start XAMPP Services
1. Open the XAMPP Control Panel.
2. Click Start for Apache.
3. Click Start for MySQL.

### Step 2: Copy Project Files
Place this project folder into your XAMPP htdocs directory:
```
C:\xampp\htdocs\property-tax-system
```

### Step 3: Set Up the Database in phpMyAdmin
1. Open your browser and go to:
   http://localhost/phpmyadmin
2. Click on the SQL tab at the top.
3. Open sql/schema.sql in a text editor, copy all queries, paste them into the SQL tab, and click Go. This will create the property_tax database and the 4 tables.
4. Select the property_tax database from the left-hand navigation list.
5. Click on the SQL tab again, copy all contents from sql/sample_data.sql, paste them into the box, and click Go. This loads the sample owners, properties, assessments, and payments.

### Step 4: Verify Database Connection Settings
Open includes/db.php. The connection is pre-configured to handle standard XAMPP setups:
- Host: 127.0.0.1 / localhost
- User: root
- Password: "" (empty)
- Database: property_tax
- Ports: Automatically checks default port 3306 first, and falls back to port 3307 if MySQL 8.0 is installed on port 3306.

### Step 5: Open the Application
Navigate to the following address in your browser:
```
http://localhost/property-tax-system/
```

### Quick Commands (NPM Workflow in VS Code)

If working inside VS Code, you can use these npm scripts directly in the terminal:

- Start server and open browser automatically:
  ```bash
  npm run dev
  ```
- Start server without opening browser:
  ```bash
  npm start
  ```
- Open the website in your browser:
  ```bash
  npm run open
  ```
- Reset and re-load database (schema + sample data):
  ```bash
  npm run db:setup
  ```
- Check total row counts across all tables:
  ```bash
  npm run db:status
  ```
- Open MySQL terminal shell:
  ```bash
  npm run db:shell
  ```
Or if running directly through PHP built-in server:
```
http://localhost:8080/index.php
```

---

## 8. Modules and Features

### 8.1 Dashboard (index.php)
- Displays overall system metrics in a simple two-column table: total owners, total properties, total yearly assessments, total payment transactions, total tax assessed, total tax collected, and total pending due.
- Displays a table of the 5 most recent payment transactions.

### 8.2 Owners Management (owners.php, owner_edit.php)
- Add new property owners with name, phone, and email.
- Real-time search filter by owner name or phone number.
- Edit existing owner details.
- Safe delete button leading to confirmation screen.

### 8.3 Properties Management (properties.php, property_edit.php)
- Add properties linked to an existing owner.
- Filter properties by classification (Residential, Commercial, Industrial, Agricultural).
- View owner name joined with property address.
- Edit address or property classification.

### 8.4 Tax Assessments (assessments.php, assessment_edit.php)
- Create yearly tax assessments by selecting property, year, and tax amount.
- Real-time calculation of amount paid, amount due, and status (Paid, Partial, Unpaid).
- Filter assessments by assessment year or payment status.
- Edit assessment tax amount (only available if paid amount is zero).

### 8.5 Payments Ledger (payments.php)
- Record new payments against assessments that have pending balances.
- Select from dropdown displaying assessment ID, owner name, property, and pending due.
- Date validation preventing future dates.
- Maximum payable amount restricted to remaining balance.

### 8.6 Two-Step Deletion (delete.php)
- Centralized deletion handler for owners, properties, assessments, and payments.
- Step 1 (GET): Displays the exact item description and asks for user confirmation with Confirm and Cancel buttons.
- Step 2 (POST): Verifies foreign key dependencies. If child records exist, deletion is halted with an explanatory error message.

### 8.7 Reports (reports.php)
Contains four SQL aggregation reports:
1. Defaulters Report: Identifies all assessments with pending balances greater than zero.
2. Year-Wise Collection: Aggregates total assessed, total collected, and total pending amounts per year.
3. Property Type-Wise Summary: Aggregates assessments, assessed tax, and collected revenue by property type.
4. Owner-Wise Pending Dues: Groups and sorts owners by their cumulative outstanding tax liabilities.

---

## 9. Verification and Test Scenarios

The following test scenarios verify system correctness:

| Test Case | Steps to Test | Expected Behavior |
| :--- | :--- | :--- |
| Valid Owner Creation | Enter valid name and 10-digit phone on owners.php | Record saved, success message displayed |
| Invalid Phone Length | Enter 8-digit phone on owners.php | Rejected with message: Phone number must be exactly 10 digits |
| Duplicate Assessment | Create assessment for same property and year on assessments.php | Blocked with message: An assessment for this property and year already exists |
| Negative/Zero Tax | Enter 0 or negative tax on assessments.php | Blocked with message: Tax amount must be greater than zero |
| Excess Payment | Enter payment amount higher than pending due on payments.php | Rejected with message: Payment amount cannot be more than the pending amount |
| Partial Payment | Make partial payment on unpaid assessment | Assessment status changes from Unpaid to Partial |
| Full Settlement | Pay remaining balance of partial assessment | Assessment status changes to Paid, Due becomes 0.00 |
| Future Date Payment | Select tomorrow's date on payments.php | Blocked with message: Payment date cannot be in the future |
| Protected Owner Delete | Attempt deleting owner with properties on delete.php | Blocked with message: This owner has properties and cannot be deleted |
| Payment Deletion | Delete a payment record on delete.php | Payment deleted, assessment due amount increases back |
| Invalid Delete URL | Open delete.php?type=unknown&id=1 | Rejected with message: Invalid delete request |
| Search Filter | Search by name substring or phone number on owners.php | Correct matching records returned |