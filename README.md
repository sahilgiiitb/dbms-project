# COVID Vaccination Management System

## 1. Project Overview

The COVID Vaccination Management System is a database-driven web application designed for vaccination centers. It allows healthcare staff to register citizens, manage vaccination centers, maintain vaccine inventories, record vaccinations, and generate statistical reports.

The project is built specifically as a first-year Database Management Systems (DBMS) project to demonstrate core relational database concepts, including primary keys, foreign keys, unique constraints, referential integrity, and SQL analytical queries.

---

## 2. Technology Stack

- **Frontend:** HTML5 and CSS3 (No JavaScript, no external UI frameworks or libraries).
- **Backend:** Plain procedural PHP with the native `mysqli` extension.
- **Database:** MySQL / MariaDB relational database running on XAMPP.
- **Styling:** Custom CSS located at `css/style.css` (sharp borders, clean layout, no SaaS tropes).

---

## 3. How PHP and MySQL Work (Beginner Guide)

If you have never worked with PHP or SQL before, here is the simplest explanation of how the system works:

### What is HTML & CSS?
- **HTML** gives the structure: headings, text, forms, input boxes, dropdowns, and tables.
- **CSS** gives the appearance: colors, font size, margins, padding, and table borders.
- HTML and CSS by themselves cannot store data permanently. Once you refresh a static HTML page, any entered data is lost.

### What is MySQL?
- MySQL is a **Relational Database Management System (RDBMS)**.
- It stores data in organized grids called **Tables** (like spreadsheets with rows and columns).
- When a user enters data, it gets permanently written to files on your hard drive.

### What is PHP?
- PHP is a **Server-Side Programming Language**.
- It runs on the web server (Apache in XAMPP) before anything is shown to the user.
- PHP acts as the bridge connecting your HTML web pages to your MySQL database:
  1. The user fills out an HTML form and clicks "Submit".
  2. The browser sends this data to a PHP script.
  3. The PHP script checks if the inputs are valid (e.g., Aadhaar has 12 digits).
  4. The PHP script talks to MySQL using SQL queries (e.g., `INSERT INTO Citizens...`).
  5. MySQL saves the data and reports success.
  6. PHP builds a fresh HTML page containing the updated table and sends it back to the browser.

---

## 4. Project Directory Structure

```text
covid-vaccination-system/
|-- index.php             Dashboard overview with record counts and quick links
|-- db.php                Database connection script included across all pages
|-- header.php            Reusable layout header and top navigation bar
|-- footer.php            Reusable layout footer and copyright text
|-- citizens.php          Add, view, search, and delete citizens
|-- citizen_edit.php      Edit citizen information
|-- centers.php           Add, view, filter by state, and delete centers
|-- center_edit.php       Edit vaccination center information
|-- vaccines.php          Add, view, and delete vaccine types
|-- vaccine_edit.php      Edit vaccine details (name, manufacturer, doses)
|-- vaccinations.php      Record vaccinations using dropdowns, view JOIN logs, and delete
|-- reports.php           Five SQL reporting and analytics queries
|-- css/
|   |-- style.css         Single stylesheet for the entire application
|-- sql/
|   |-- schema.sql        Table creation script with keys and constraints
|   |-- sample_data.sql   Initial test records (citizens, centers, vaccines, logs)
|-- docs/
|   |-- README.md         Project overview and fundamentals
|   |-- ARCHITECTURE.md   Technical architecture, data flow, and disk storage locations
|   |-- HOW_TO_RUN.md     Running commands, usage guide, and cloud deployment steps
```

---

## 5. Core Features

1. **Citizens Management:**
   - Register citizens with Full Name, 12-digit Aadhaar number, and 10-digit Mobile number.
   - Prevents duplicate Aadhaar registration.
   - Search directory by Name, Aadhaar, or Mobile.
   - Deletion protection: Citizens with recorded vaccinations cannot be deleted.

2. **Vaccination Centers:**
   - Maintain center name, district, and state.
   - Filter centers directory by state.
   - Deletion protection: Centers with existing vaccination records cannot be deleted.

3. **Vaccines Inventory:**
   - Maintain vaccine name, manufacturer, and required dose count.
   - Deletion protection: Vaccines already administered cannot be deleted.

4. **Vaccinations Administration:**
   - Dropdowns dynamically populated from database tables.
   - Date picker restricted to prevent future dates.
   - Maximum dose check: Automatically blocks entries if a citizen has already completed all doses for that vaccine.
   - Displays all records with citizen, vaccine, and center names using SQL `JOIN`.

5. **Reports & Analytics:**
   - Center-wise vaccination totals.
   - Vaccine-wise total doses administered.
   - List of citizens who completed all doses.
   - List of citizens with pending doses.
   - Complete vaccination log overview.
