# Technical Requirements Document (TRD)

**Project Title:** Municipal Property Tax System
**Subject:** Database Management Systems (DBMS)
**Name:** Sahil Gupta
**Scholar Number:** 25U020128

---

## 1. Technology Used

| Part | Technology |
|------|------------|
| Frontend | HTML5 and CSS3 only (no JavaScript, no React, no frameworks, no icon library) |
| Backend | PHP (plain, procedural, with mysqli) |
| Database | MySQL (XAMPP) |
| Server | Apache (XAMPP) |
| Editor | Antigravity |

Plain HTML and CSS cannot connect to a database, so PHP is used as the bridge. Every page is a `.php` file that prints HTML.

## 2. How the System Works

```
Browser  --->  Apache (PHP runs here)  --->  MySQL
Browser  <---  HTML page  <---------------  rows
```

1. The user opens a page such as `owners.php`.
2. Apache runs the PHP code of that file.
3. PHP runs the SQL query on MySQL and gets the rows.
4. PHP prints the rows inside HTML and sends the finished page to the browser.

Forms use the POST method for insert, update and delete. Pages that only show or filter data use GET. After every successful POST the page redirects back to the list page so that a refresh does not submit the form again.

## 3. Folder Structure

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

- `includes/db.php` has the database connection and starts the session. Every page includes it.
- `includes/header.php` has the top of the page and the navigation bar. `includes/footer.php` has the footer links and closes the page.

## 4. Database Design

Database name: `property_tax`

### 4.1 Schema (schema.sql)

```sql
CREATE DATABASE property_tax;
USE property_tax;

CREATE TABLE PropertyOwners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_name VARCHAR(100) NOT NULL,
    phone CHAR(10) NOT NULL,
    email VARCHAR(100)
);

CREATE TABLE Properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    property_address VARCHAR(255) NOT NULL,
    property_type VARCHAR(30) NOT NULL,
    FOREIGN KEY (owner_id) REFERENCES PropertyOwners(owner_id)
);

CREATE TABLE TaxAssessments (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    assessment_year INT NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL CHECK (tax_amount > 0),
    FOREIGN KEY (property_id) REFERENCES Properties(property_id)
);

CREATE TABLE Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL CHECK (amount_paid > 0),
    FOREIGN KEY (assessment_id) REFERENCES TaxAssessments(assessment_id)
);
```

### 4.2 Relationships

```
PropertyOwners (1) ----< (M) Properties (1) ----< (M) TaxAssessments (1) ----< (M) Payments
```

### 4.3 Normalization

- **1NF:** every column holds a single value and there are no repeating groups.
- **2NF:** every table has a single column primary key, so no partial dependency exists.
- **3NF:** owner details are stored only in PropertyOwners, property details only in Properties and so on. Paid and pending amounts are not stored, they are calculated from Payments, so there is no derived data to go out of date.

## 5. Page Wise Implementation

### 5.1 index.php
Runs `SELECT COUNT(*)` on the four tables and the SUM queries for total assessed, collected and pending. Shows them in a simple two column table. Below it, a table of the last five payments (JOIN of Payments, TaxAssessments, Properties and PropertyOwners).

### 5.2 owners.php
- Form to add an owner (INSERT).
- Search form (GET) that filters with `WHERE owner_name LIKE ? OR phone = ?`.
- Table of owners with Edit and Delete links.

### 5.3 owner_edit.php
Loads one owner by id from the URL, shows the form with the old values and updates on submit (UPDATE).

### 5.4 properties.php and property_edit.php
Same pattern for the Properties table. The owner is chosen from a dropdown filled from PropertyOwners. A GET filter by property type is available. The list uses a JOIN to show the owner name.

### 5.5 assessments.php and assessment_edit.php
- Form with a property dropdown (shows owner name and address), a year field and a tax amount field.
- Filters by year and by status (GET).
- Table with owner, property, year, tax amount, paid, pending and status.
- The edit page allows changing the tax amount only if the assessment has no payments.

### 5.6 payments.php
- Form with an assessment dropdown (shows owner, address, year and pending amount), a date field and an amount field.
- Table of all payments with a Delete link.

### 5.7 delete.php
One page for all deletes. It is opened as `delete.php?type=owner&id=3`. It works in two steps:

1. GET: shows a plain confirmation message and a form with a Confirm button and a Cancel link.
2. POST: checks that the record is not used by another table, deletes it and redirects back with a message.

The `type` value is matched against a fixed list (owner, property, assessment, payment) and anything else is rejected. This is how deletion is confirmed without JavaScript.

### 5.8 reports.php
Shows the four reports one below the other, each in its own table with a heading.

### 5.9 terms.php and privacy.php
Static pages with short plain text.

## 6. Important SQL Queries

**Assessments with paid amount, pending amount and status**
```sql
SELECT a.assessment_id, o.owner_name, p.property_address, a.assessment_year,
       a.tax_amount,
       IFNULL(x.paid, 0) AS paid,
       a.tax_amount - IFNULL(x.paid, 0) AS due,
       CASE
           WHEN IFNULL(x.paid, 0) = 0 THEN 'Unpaid'
           WHEN a.tax_amount - IFNULL(x.paid, 0) <= 0 THEN 'Paid'
           ELSE 'Partial'
       END AS status
FROM TaxAssessments a
JOIN Properties p ON a.property_id = p.property_id
JOIN PropertyOwners o ON p.owner_id = o.owner_id
LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
           FROM Payments GROUP BY assessment_id) x
       ON a.assessment_id = x.assessment_id
ORDER BY a.assessment_year DESC;
```
The year filter is added with `WHERE a.assessment_year = ?` and the status filter with `HAVING status = ?`.

**Defaulters**
```sql
SELECT o.owner_name, o.phone, p.property_address, a.assessment_year,
       a.tax_amount - IFNULL(SUM(pay.amount_paid), 0) AS due
FROM TaxAssessments a
JOIN Properties p ON a.property_id = p.property_id
JOIN PropertyOwners o ON p.owner_id = o.owner_id
LEFT JOIN Payments pay ON a.assessment_id = pay.assessment_id
GROUP BY a.assessment_id, o.owner_name, o.phone, p.property_address,
         a.assessment_year, a.tax_amount
HAVING due > 0
ORDER BY due DESC;
```

**Year wise collection**
```sql
SELECT a.assessment_year,
       SUM(a.tax_amount) AS total_assessed,
       IFNULL(SUM(x.paid), 0) AS total_collected,
       SUM(a.tax_amount) - IFNULL(SUM(x.paid), 0) AS total_pending
FROM TaxAssessments a
LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
           FROM Payments GROUP BY assessment_id) x
       ON a.assessment_id = x.assessment_id
GROUP BY a.assessment_year
ORDER BY a.assessment_year DESC;
```

**Property type wise tax**
```sql
SELECT p.property_type,
       COUNT(a.assessment_id) AS assessments,
       SUM(a.tax_amount) AS total_assessed,
       IFNULL(SUM(x.paid), 0) AS total_collected
FROM Properties p
JOIN TaxAssessments a ON p.property_id = a.property_id
LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
           FROM Payments GROUP BY assessment_id) x
       ON a.assessment_id = x.assessment_id
GROUP BY p.property_type;
```

**Owner wise pending amount**
```sql
SELECT o.owner_name,
       SUM(a.tax_amount) - IFNULL(SUM(x.paid), 0) AS pending
FROM PropertyOwners o
JOIN Properties p ON o.owner_id = p.owner_id
JOIN TaxAssessments a ON p.property_id = a.property_id
LEFT JOIN (SELECT assessment_id, SUM(amount_paid) AS paid
           FROM Payments GROUP BY assessment_id) x
       ON a.assessment_id = x.assessment_id
GROUP BY o.owner_id, o.owner_name
HAVING pending > 0
ORDER BY pending DESC;
```

**Checks before inserting**
```sql
SELECT COUNT(*) FROM TaxAssessments WHERE property_id = ? AND assessment_year = ?;

SELECT a.tax_amount - IFNULL(SUM(pay.amount_paid), 0) AS due
FROM TaxAssessments a
LEFT JOIN Payments pay ON a.assessment_id = pay.assessment_id
WHERE a.assessment_id = ?
GROUP BY a.assessment_id, a.tax_amount;
```
The first query blocks a second assessment for the same property and year. The second one blocks a payment bigger than the pending amount.

**Checks before deleting**
```sql
SELECT COUNT(*) FROM Properties WHERE owner_id = ?;
SELECT COUNT(*) FROM TaxAssessments WHERE property_id = ?;
SELECT COUNT(*) FROM Payments WHERE assessment_id = ?;
```

## 7. Validation

Validation is done twice. The HTML form has `required`, `maxlength`, `pattern`, `min`, `max` and `type="date"` for a basic check, and PHP checks everything again after the form is submitted.

| Field | Rule |
|-------|------|
| owner_name | Not empty |
| phone | Exactly 10 digits |
| email | Valid format if given (`filter_var` with `FILTER_VALIDATE_EMAIL`) |
| property_address | Not empty |
| property_type | One of Residential, Commercial, Industrial, Agricultural |
| assessment_year | 4 digit year |
| tax_amount | Number greater than 0 |
| amount_paid | Number greater than 0 and not more than due |
| payment_date | Valid date and not in the future |
| Delete | Blocked if a child record exists |

## 8. Security and Error Handling

- Every query that takes input uses `mysqli_prepare` with `bind_param`.
- Everything printed on a page goes through `htmlspecialchars()`.
- Ids from the URL are converted with `(int)` before use.
- Messages are stored in `$_SESSION['msg']` and `$_SESSION['error']`, shown once on the next page and then cleared.
- Database errors are not printed on the page. A short message like "Something went wrong, please try again" is shown instead.
- Database host, user, password and name are set in one place, `includes/db.php`.

## 9. Styling Rules

Everything is in `css/style.css`. The following must be followed:

- Background: warm off white (for example #f5f3ee), not pure white. Tables and forms may use a slightly lighter shade.
- Main color: one dark muted blue (for example #234060). Error color: brick red (for example #a33a2a). Success color: dull green. No gradients.
- Border radius 0 to 3px only.
- No box-shadow anywhere. Use `border: 1px solid` instead.
- Font: Source Sans 3 or Noto Sans with a system font fallback. Do not use Inter, Geist or Space Grotesk.
- No icon libraries, no emojis, no SVG sparkles or arrows. Buttons and links use plain text.
- The totals on the home page are shown in a simple two column table, not in a row of three cards.
- Alerts are plain boxes with a full 1px border, no colored left stripe.
- Hover only changes background color, with no transition and no transform.
- No animations of any kind.
- No bento layouts, no dot patterns, no glowing shapes, no glass effect.
- Layout works on a mobile screen. Tables sit inside a box that scrolls sideways.

## 10. Text Rules

- Do not use em dashes in any text on the website, in the sample data or in the documents.
- Do not write marketing style sentences or sentences in the form "it is not X, it is Y".
- Do not use checkmark bullet lists.
- No testimonials, pricing sections or hero banners with slogans. The first page is the home page with the totals.
- Terms of Use and Privacy Policy are short plain pages written for this system (what data is stored, who can see it, that it is a college project database, no data is shared).

## 11. Sample Data (sample_data.sql)

Realistic data so the project works fully on first run:

- About 10 owners with real looking Indian names, 10 digit phone numbers and emails.
- About 14 properties with proper addresses (house number, locality, city) covering all four property types.
- Assessments for the years 2023, 2024 and 2025 for most properties.
- About 20 payments with a mix of fully paid, partly paid and unpaid assessments, so every report has data.
- No placeholder text like "test", "abc", "John Doe" or "Lorem ipsum".

## 12. Setup Steps

1. Install XAMPP and start Apache and MySQL from the control panel.
2. Copy the `property-tax-system` folder into `C:\xampp\htdocs`.
3. Open phpMyAdmin at `http://localhost/phpmyadmin` and run `sql/schema.sql`, then `sql/sample_data.sql`.
4. Check the user and password in `includes/db.php` (default in XAMPP is `root` with an empty password).
5. Open `http://localhost/property-tax-system/` in the browser.

## 13. Testing

| No. | Test | Expected Result |
|-----|------|-----------------|
| 1 | Add owner with valid data | Saved and shown in the list |
| 2 | Add owner with phone of 8 digits | Error message |
| 3 | Add second assessment for same property and year | Error message |
| 4 | Add assessment with tax amount 0 or negative | Error message |
| 5 | Record a payment bigger than the pending amount | Error message |
| 6 | Record a part payment | Status changes to Partial and pending reduces |
| 7 | Pay the full remaining amount | Status changes to Paid |
| 8 | Payment with a future date | Error message |
| 9 | Delete an owner who has properties | Not deleted, message shown |
| 10 | Delete a payment and confirm | Pending amount increases again |
| 11 | Open delete.php with a wrong type in the URL | Rejected |
| 12 | Open every report | Correct data matching the tables |
| 13 | Search owners by name and by phone | Correct rows shown |
| 14 | Open Terms and Privacy pages from the footer | Pages open |
