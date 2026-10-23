# Architecture

**Project Title:** Municipal Property Tax System
**Subject:** Database Management Systems (DBMS)
**Name:** Sahil Gupta
**Scholar Number:** 25U020128

---

## 1. Overview

The project follows a three tier architecture. The browser shows HTML and CSS pages, Apache runs the PHP code that holds the logic and validation, and MySQL stores all the data. The browser never talks to the database directly. Every page is built by PHP on the server and sent to the browser as plain HTML.

```
+--------------------+     HTTP request      +--------------------+      SQL      +--------------------+
|   Presentation     | --------------------> |   Application      | ------------> |   Data             |
|   HTML and CSS     |                       |   PHP on Apache    |               |   MySQL            |
|   (browser)        | <-------------------- |   localhost:80     | <------------ |   localhost:3306   |
+--------------------+     HTML page         +--------------------+     rows      +--------------------+
```

## 2. Tiers and Their Work

### 2.1 Presentation tier (HTML and CSS)
- Pages are plain HTML printed by the PHP files.
- `css/style.css` styles the whole site.
- Forms use `required`, `pattern`, `maxlength`, `min` and `type="date"` for a first check.
- No JavaScript is used anywhere.

### 2.2 Application tier (PHP)
- One PHP file for each page or module.
- Validates every form again on the server and applies the business rules.
- Runs the SQL queries using prepared statements.
- Prints the result inside HTML, or redirects to another page after a save.

### 2.3 Data tier (MySQL)
- MySQL database named `property_tax`, run from XAMPP.
- Four related tables with primary keys, foreign keys and check constraints.
- Paid and pending amounts are not stored. They are calculated with SUM from the Payments table whenever needed.

## 3. Request Flow

Example: recording a payment.

```
1. User opens payments.php
2. PHP reads the assessments with their pending amount and fills the dropdown
3. User selects an assessment, enters the date and amount and clicks Save
4. The browser sends a POST request to payments.php
5. PHP checks that the amount is above zero and the date is not in the future
6. PHP runs a query to find the pending amount of that assessment
7. If the amount is more than the pending amount, an error is saved in the session
8. Otherwise PHP runs INSERT INTO Payments with a prepared statement
9. PHP saves a success message in the session and redirects to payments.php
10. The page loads again, shows the message once and lists the new payment
```

Example: deleting a record.

```
1. User clicks Delete next to a row, which opens delete.php?type=payment&id=5
2. delete.php shows a confirmation message with Confirm and Cancel
3. Confirm sends a POST request
4. PHP checks the type against the allowed list and checks the record is not in use
5. PHP runs the DELETE query and redirects back with a message
```

## 4. Folder Structure

```
property-tax-system/
|-- index.php
|-- owners.php
|-- owner_edit.php
|-- properties.php
|-- property_edit.php
|-- assessments.php
|-- assessment_edit.php
|-- payments.php
|-- delete.php
|-- reports.php
|-- terms.php
|-- privacy.php
|-- includes/
|   |-- db.php
|   |-- header.php
|   |-- footer.php
|-- css/
|   |-- style.css
|-- sql/
|   |-- schema.sql
|   |-- sample_data.sql
```

## 5. File Responsibilities

| File | Work |
|------|------|
| includes/db.php | Connects to MySQL with mysqli and starts the session |
| includes/header.php | Opens the HTML page, links the CSS, prints the navigation bar and shows session messages |
| includes/footer.php | Prints the footer links to Terms and Privacy and closes the HTML page |
| index.php | Totals and the last five payments |
| owners.php | Add, list and search owners |
| owner_edit.php | Update one owner |
| properties.php | Add, list and filter properties |
| property_edit.php | Update one property |
| assessments.php | Add, list and filter assessments with paid, pending and status |
| assessment_edit.php | Update the tax amount of an assessment without payments |
| payments.php | Record and list payments |
| delete.php | Confirmation and deletion for all four record types |
| reports.php | Defaulters, year wise, type wise and owner wise reports |
| terms.php | Terms of Use |
| privacy.php | Privacy Policy |
| css/style.css | All the styling |

Every page starts with `include 'includes/db.php'` and `include 'includes/header.php'` and ends with `include 'includes/footer.php'`, so the layout is written only once.

## 6. Database Architecture

### 6.1 Tables

- **PropertyOwners** (owner_id PK, owner_name, phone, email)
- **Properties** (property_id PK, owner_id FK, property_address, property_type)
- **TaxAssessments** (assessment_id PK, property_id FK, assessment_year, tax_amount)
- **Payments** (payment_id PK, assessment_id FK, payment_date, amount_paid)

### 6.2 ER Diagram

```
+----------------+       +----------------+       +------------------+       +--------------+
| PropertyOwners |       |   Properties   |       |  TaxAssessments  |       |   Payments   |
+----------------+       +----------------+       +------------------+       +--------------+
| owner_id (PK)  |1    M | property_id PK |1    M | assessment_id PK |1    M | payment_id PK|
| owner_name     |-------| owner_id (FK)  |-------| property_id (FK) |-------| assessment_id|
| phone          |owns   | address        |has    | assessment_year  |gets   |   (FK)       |
| email          |       | property_type  |       | tax_amount       |       | payment_date |
+----------------+       +----------------+       +------------------+       | amount_paid  |
                                                                             +--------------+
```

### 6.3 Relationships

| Relationship | Type | Meaning |
|--------------|------|---------|
| PropertyOwners to Properties | One to many | One owner can own many properties |
| Properties to TaxAssessments | One to many | One property has one assessment for each year |
| TaxAssessments to Payments | One to many | One assessment can be paid in many parts |

### 6.4 Constraints

- Primary key on every table with AUTO_INCREMENT.
- Foreign keys from Properties, TaxAssessments and Payments to their parent tables.
- NOT NULL on all columns except email.
- CHECK (tax_amount > 0) and CHECK (amount_paid > 0).
- One assessment per property per year is checked in PHP before insert.

### 6.5 Normalization

The tables are in third normal form. Every table has a single column primary key, every column depends only on its key, and owner, property and assessment details are stored once in their own tables. Paid and pending amounts are calculated, so no derived value is stored twice.

## 7. Validation Layers

```
HTML form (required, pattern, min, maxlength)
        |
        v
PHP script (checks every field and business rule)
        |
        v
MySQL (NOT NULL, CHECK, FOREIGN KEY)
```

A wrong value has to pass three checks to be saved, so bad data cannot get into the database even if one layer is skipped.

## 8. Session Messages

PHP sessions carry the result of an action from one page to the next.

1. After an insert, update or delete, the script stores a success or error text in the session.
2. It redirects to the list page with `header('Location: ...')`.
3. `header.php` prints the message in a bordered box and removes it from the session.

This also stops the form from being submitted again when the user refreshes the page.

## 9. Error Handling

- Failed validation shows a message like "Phone number must be 10 digits" and keeps the user on the same form.
- A delete on a record that is in use shows "This owner has properties and cannot be deleted".
- If a query fails, the page shows "Something went wrong, please try again" and the real error is not printed.
- An unknown id in the URL shows "Record not found" with a link back to the list.

## 10. Security

- All queries with user input use prepared statements, so SQL injection is not possible through the inputs.
- All output goes through `htmlspecialchars()`, so user text cannot run as HTML.
- Ids from the URL are cast to integers.
- The `type` value in `delete.php` is checked against a fixed list.
- Database credentials are kept in one file, `includes/db.php`.
- No login is included in this version, so the system is meant for a local machine.

## 11. Deployment on a Local Machine

```
Browser  --->  Apache + PHP (XAMPP, port 80)  --->  MySQL (XAMPP, port 3306)
```

1. Start Apache and MySQL in the XAMPP control panel.
2. Put the project folder inside `htdocs`.
3. Run `schema.sql` and `sample_data.sql` in phpMyAdmin.
4. Open `http://localhost/property-tax-system/` in the browser.

## 12. Design Decisions

| Decision | Reason |
|----------|--------|
| PHP with plain HTML and CSS | Matches the project requirement and runs directly on XAMPP |
| Shared header, footer and db files | Avoids repeating the same code in every page |
| Calculate paid and pending amount | Avoids wrong totals when a payment is added or deleted |
| One delete page with a confirmation step | Gives a confirmation without JavaScript |
| Session messages and redirect after POST | Shows feedback and avoids duplicate submissions on refresh |
| One CSS file | The project is small and one file is easy to manage |
| Static Terms and Privacy pages | A system that stores personal data should state how it is used |
