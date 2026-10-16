# Technical Requirements Document (TRD)

**Project Title:** COVID Vaccination Management System
**Subject:** Database Management Systems (DBMS)
**Name:** Sahil Gupta
**Scholar Number:** 25U020128

---

## 1. Technology Used

| Part | Technology |
|------|------------|
| Frontend | HTML5, CSS3 (no JavaScript, no React, no frameworks) |
| Backend | PHP (plain, with mysqli) |
| Database | MySQL |
| Server | XAMPP (Apache + MySQL) |
| Editor | Antigravity |

Plain HTML and CSS cannot connect to a database, so PHP is used only as the bridge between the web pages and MySQL.

## 2. How the System Works

1. User opens a page in the browser (for example `citizens.php`).
2. The browser sends the request to Apache.
3. The PHP file runs the SQL query on MySQL.
4. MySQL sends the data back to PHP.
5. PHP puts the data inside HTML and sends the page back to the browser.

Forms use the POST method for insert, update and delete. Pages that only show data use GET.

## 3. Folder Structure

```
covid-vaccination-system/
|-- index.php
|-- db.php
|-- citizens.php
|-- citizen_edit.php
|-- centers.php
|-- center_edit.php
|-- vaccines.php
|-- vaccine_edit.php
|-- vaccinations.php
|-- reports.php
|-- header.php
|-- footer.php
|-- css/
|   |-- style.css
|-- sql/
|   |-- schema.sql
|   |-- sample_data.sql
```

- `db.php` has the database connection and is included in every page.
- `header.php` has the navigation bar and `footer.php` closes the page, so the layout is not repeated in every file.

## 4. Database Design

Database name: `covid_vaccination`

### 4.1 Schema (schema.sql)

```sql
CREATE DATABASE covid_vaccination;
USE covid_vaccination;

CREATE TABLE Citizens (
    citizen_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_name VARCHAR(100) NOT NULL,
    aadhaar_no CHAR(12) NOT NULL UNIQUE,
    mobile CHAR(10) NOT NULL
);

CREATE TABLE VaccinationCenters (
    center_id INT AUTO_INCREMENT PRIMARY KEY,
    center_name VARCHAR(100) NOT NULL,
    district VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL
);

CREATE TABLE Vaccines (
    vaccine_id INT AUTO_INCREMENT PRIMARY KEY,
    vaccine_name VARCHAR(50) NOT NULL,
    manufacturer VARCHAR(100) NOT NULL,
    doses_required INT NOT NULL CHECK (doses_required > 0)
);

CREATE TABLE Vaccinations (
    vaccination_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    vaccine_id INT NOT NULL,
    center_id INT NOT NULL,
    vaccination_date DATE NOT NULL,
    FOREIGN KEY (citizen_id) REFERENCES Citizens(citizen_id),
    FOREIGN KEY (vaccine_id) REFERENCES Vaccines(vaccine_id),
    FOREIGN KEY (center_id) REFERENCES VaccinationCenters(center_id)
);
```

### 4.2 Relationships

```
Citizens (1) ----< (M) Vaccinations (M) >---- (1) Vaccines
                          |
                         (M)
                          |
                         (1)
                  VaccinationCenters
```

### 4.3 Normalization

- **1NF:** all columns hold single values, no repeating groups.
- **2NF:** every non key column depends on the full primary key (all tables have a single column primary key).
- **3NF:** no transitive dependency. Citizen, center and vaccine details are stored once in their own tables and only their IDs are used in Vaccinations.

## 5. Page Wise Implementation

### 5.1 index.php
Shows four counts (citizens, centers, vaccines, vaccinations) using `SELECT COUNT(*)` and links to other pages.

### 5.2 citizens.php
- Form to add a citizen (INSERT).
- Search box (`WHERE citizen_name LIKE ... OR aadhaar_no = ... OR mobile = ...`).
- Table of all citizens with Edit and Delete buttons.

### 5.3 citizen_edit.php
Loads one citizen by id and updates it (UPDATE).

### 5.4 centers.php and center_edit.php
Same pattern as citizens for the VaccinationCenters table. Has a filter by state.

### 5.5 vaccines.php and vaccine_edit.php
Same pattern for the Vaccines table.

### 5.6 vaccinations.php
- Form with three dropdowns (citizen, vaccine, center) filled from the database and a date field.
- Table of all records using JOIN.
- Delete button for each record.

### 5.7 reports.php
Shows the output of the report queries given below.

## 6. Important SQL Queries

**All vaccinations with names (JOIN)**
```sql
SELECT v.vaccination_id, c.citizen_name, vc.vaccine_name,
       ce.center_name, v.vaccination_date
FROM Vaccinations v
JOIN Citizens c ON v.citizen_id = c.citizen_id
JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
JOIN VaccinationCenters ce ON v.center_id = ce.center_id
ORDER BY v.vaccination_date DESC;
```

**Vaccinations at each center**
```sql
SELECT ce.center_name, COUNT(v.vaccination_id) AS total
FROM VaccinationCenters ce
LEFT JOIN Vaccinations v ON ce.center_id = v.center_id
GROUP BY ce.center_id, ce.center_name;
```

**Vaccinations of each vaccine**
```sql
SELECT vc.vaccine_name, COUNT(v.vaccination_id) AS total
FROM Vaccines vc
LEFT JOIN Vaccinations v ON vc.vaccine_id = v.vaccine_id
GROUP BY vc.vaccine_id, vc.vaccine_name;
```

**Citizens who completed all doses**
```sql
SELECT c.citizen_name, vc.vaccine_name, COUNT(*) AS doses_taken
FROM Vaccinations v
JOIN Citizens c ON v.citizen_id = c.citizen_id
JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
GROUP BY c.citizen_id, vc.vaccine_id, c.citizen_name, vc.vaccine_name, vc.doses_required
HAVING COUNT(*) >= vc.doses_required;
```

**Citizens with pending doses**
```sql
SELECT c.citizen_name, vc.vaccine_name, COUNT(*) AS doses_taken, vc.doses_required
FROM Vaccinations v
JOIN Citizens c ON v.citizen_id = c.citizen_id
JOIN Vaccines vc ON v.vaccine_id = vc.vaccine_id
GROUP BY c.citizen_id, vc.vaccine_id, c.citizen_name, vc.vaccine_name, vc.doses_required
HAVING COUNT(*) < vc.doses_required;
```

**Dose count check before inserting a vaccination**
```sql
SELECT COUNT(*) FROM Vaccinations
WHERE citizen_id = ? AND vaccine_id = ?;
```
If this count is already equal to `doses_required` of that vaccine, the insert is rejected.

## 7. Validation

All validation is done in PHP because JavaScript is not used.

| Field | Rule |
|-------|------|
| citizen_name | Not empty |
| aadhaar_no | Exactly 12 digits, must not already exist |
| mobile | Exactly 10 digits |
| doses_required | Number greater than 0 |
| vaccination_date | Valid date, not in the future |
| Delete | Blocked if the record is used in Vaccinations |

HTML attributes like `required`, `maxlength`, `pattern` and `type="date"` are also added in the forms as a first check.

## 8. Security and Error Handling

- Prepared statements (`mysqli_prepare`) are used for every query that takes user input to avoid SQL injection.
- Output is passed through `htmlspecialchars()` before showing on the page.
- Duplicate Aadhaar error and foreign key errors are caught and shown as a simple message on the page.
- After every insert, update or delete, the page redirects back to the list so that refreshing does not submit the form again.

## 9. CSS and UI

- Single file `css/style.css` for the whole site.
- Navigation bar on top with links to all pages.
- Tables with borders, light header colour and hover effect on rows.
- Forms with labels above the inputs.
- Buttons: blue for save, red for delete.
- Green box for success message and red box for error message.
- Layout should fit on both laptop and mobile screen using simple media query.

## 10. Setup Steps

1. Install XAMPP and start Apache and MySQL.
2. Copy the project folder into `htdocs`.
3. Open phpMyAdmin and run `sql/schema.sql`, then `sql/sample_data.sql`.
4. Check the username and password in `db.php` (default is `root` with empty password).
5. Open `http://localhost/covid-vaccination-system/` in the browser.

## 11. Testing

| No. | Test | Expected Result |
|-----|------|-----------------|
| 1 | Add citizen with valid data | Saved and shown in list |
| 2 | Add citizen with an existing Aadhaar | Error message, not saved |
| 3 | Aadhaar with less than 12 digits | Error message |
| 4 | Mobile with letters | Error message |
| 5 | Record vaccination with future date | Error message |
| 6 | Record more doses than required | Error message |
| 7 | Delete a citizen who has vaccinations | Not deleted, message shown |
| 8 | Delete a citizen with no vaccinations | Deleted |
| 9 | Search by name, Aadhaar and mobile | Correct rows shown |
| 10 | Open reports page | All five reports show correct data |
