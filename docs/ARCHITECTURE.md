# System Architecture and Data Storage

## 1. High-Level Architecture (3-Tier Model)

The application follows the classic 3-Tier Client-Server Architecture commonly taught in computer science degree programs.

```text
+-------------------------------------------------------------+
|                     PRESENTATION TIER                       |
|                 Client Browser (Chrome, Firefox)            |
|             HTML5 Forms & Tables + Pure CSS3 Styling        |
+-------------------------------------------------------------+
                              |
                     HTTP / HTTPS Requests
                              v
+-------------------------------------------------------------+
|                     APPLICATION TIER                        |
|                  Apache Web Server (XAMPP)                  |
|                 PHP 8.x Runtime Engine (mysqli)             |
|   - Input Validation (12-digit Aadhaar, dose count checks)  |
|   - Query Preparation & Execution                           |
|   - HTML Page Generation & Rendering                        |
+-------------------------------------------------------------+
                              |
                    TCP/IP Connection (SQL)
                              v
+-------------------------------------------------------------+
|                       DATABASE TIER                         |
|                    MySQL / MariaDB Server                   |
|   - Relational Tables (Citizens, Centers, Vaccines, Logs)   |
|   - Primary & Foreign Key Constraints                       |
|   - Disk Storage Engine (InnoDB)                            |
+-------------------------------------------------------------+
```

### The Three Tiers Explained:
1. **Presentation Tier (Frontend):**
   - Pure HTML5 renders input boxes, dropdown lists, tables, and buttons.
   - `css/style.css` provides the visual layout with sharp rectangular borders and high contrast.
   - No JavaScript is used; all validation errors are rendered cleanly by the server.

2. **Application Tier (Backend Logic):**
   - Handled by PHP running inside the Apache web server.
   - It reads form inputs from `$_POST` or `$_GET`.
   - Validates data formats (e.g. `ctype_digit()`, `strlen()`, date comparison).
   - Formulates parameterized SQL statements with `mysqli_prepare` to prevent SQL Injection.
   - Dispatches queries to MySQL and receives back rows.

3. **Database Tier (Persistent Storage):**
   - The MySQL database daemon (`mysqld.exe`) manages relational tables.
   - Enforces referential integrity (foreign keys prevent deleting records that have associated vaccinations).
   - Persists all records into physical disk files.

---

## 2. Request-Response Lifecycle

Here is what happens under the hood when a staff member records a vaccination:

1. **User Action:** Staff opens `vaccinations.php`, selects "Rajesh Kumar", selects "Covishield", selects "City Civil Hospital", picks a date, and clicks "Save Record".
2. **HTTP POST Request:** The browser packages the form inputs and sends an HTTP POST request to the Apache web server.
3. **Script Execution:** Apache invokes `vaccinations.php`.
4. **Validation Check:**
   - PHP checks if the date is in the future compared to `date('Y-m-d')`. If yes, it aborts and shows an error.
   - PHP queries MySQL: `SELECT COUNT(*) FROM Vaccinations WHERE citizen_id = ? AND vaccine_id = ?`.
   - If the taken dose count is already equal to or greater than `doses_required`, PHP halts and sets an error message.
5. **Database Execution:** If valid, PHP sends `INSERT INTO Vaccinations (...) VALUES (...)`.
6. **Redirect (PRG Pattern):** After successful insert, PHP issues a `header("Location: vaccinations.php?msg=...")` redirect. This prevents duplicate submissions if the user refreshes the page.
7. **HTML Rendering:** The browser requests the fresh page, PHP fetches all vaccination logs using SQL `JOIN`, embeds the rows into the HTML `<table>`, and returns the complete page to the browser.

---

## 3. Database Schema and Normalization

Database Name: `covid_vaccination`

### Entity Relationship Model

```text
+-------------------+             +-----------------------+
|     Citizens      |             |   VaccinationCenters  |
+-------------------+             +-----------------------+
| citizen_id (PK)   |<----+       | center_id (PK)        |<----+
| citizen_name      |     |       | center_name           |     |
| aadhaar_no UNIQUE |     |       | district              |     |
| mobile            |     |       | state                 |     |
+-------------------+     |       +-----------------------+     |
                          |                                     |
                          |   +-----------------------+         |
                          |   |     Vaccinations      |         |
                          |   +-----------------------+         |
                          +---| vaccination_id (PK)   |         |
                              | citizen_id (FK)       |         |
                              | vaccine_id (FK)-------+         |
                              | center_id (FK)--------|---------+
                              | vaccination_date      |
                              +-----------------------+
                                          |
                                          v
                              +-----------------------+
                              |       Vaccines        |
                              +-----------------------+
                              | vaccine_id (PK)       |
                              | vaccine_name          |
                              | manufacturer          |
                              | doses_required        |
                              +-----------------------+
```

### Relational Normalization Details:
- **First Normal Form (1NF):** Every cell holds an atomic, single value. There are no repeating groups or comma-separated lists of doses.
- **Second Normal Form (2NF):** Every table has a single-column primary key (`citizen_id`, `center_id`, `vaccine_id`, `vaccination_id`). Every non-key attribute depends entirely on the whole primary key.
- **Third Normal Form (3NF):** No transitive dependencies exist. Center details (district, state) belong in `VaccinationCenters`, vaccine details belong in `Vaccines`, and only their IDs are linked in `Vaccinations`.

---

## 4. Where Is the Data Stored on Disk?

Many students assume MySQL data lives inside the web browser or inside PHP files. In reality, MySQL writes all database records into physical binary files on your Windows hard drive.

### Physical Disk Location on Your Machine:
```text
C:\xampp\mysql\data\covid_vaccination\
```

If you open Windows File Explorer and navigate to `C:\xampp\mysql\data\covid_vaccination\`, you will find the following exact physical files:

| File Name | File Purpose |
|:---|:---|
| `citizens.frm` | Stores the table structure/schema definition for Citizens |
| `citizens.ibd` | The InnoDB tablespace file containing all citizen rows, indexes, and unique constraints |
| `vaccinationcenters.frm` | Table schema definition for VaccinationCenters |
| `vaccinationcenters.ibd` | InnoDB tablespace file containing center records |
| `vaccines.frm` | Table schema definition for Vaccines |
| `vaccines.ibd` | InnoDB tablespace file containing vaccine rows and dose rules |
| `vaccinations.frm` | Table schema definition for Vaccinations |
| `vaccinations.ibd` | InnoDB tablespace file containing every vaccination log and foreign key indexes |
| `db.opt` | Database options and character set configuration |

### How MySQL Writes Data:
When an `INSERT` statement executes, the MySQL storage engine (InnoDB) allocates space in its buffer pool in RAM, appends the change to the transaction redo log, and writes the binary record into the corresponding `.ibd` file on your NTFS drive. Even if you turn off your computer, the data remains safely stored inside these `.ibd` files.

---

## 5. How to View and Inspect the Data on Your Machine

There are two primary ways to view the raw data outside of the web application:

### Method 1: Through phpMyAdmin (Graphical Interface)
1. Open your browser and go to: `http://localhost/phpmyadmin/`
2. In the left sidebar, click on `covid_vaccination`.
3. Click on any table (e.g. `Citizens` or `Vaccinations`).
4. Click the **Browse** tab at the top to view all records in a spreadsheet-like view.
5. Click the **Structure** tab to see primary keys, foreign keys, and indexes.

### Method 2: Through the Command Line
1. Open Windows PowerShell or Command Prompt.
2. Run the MySQL client:
   ```powershell
   & "C:\xampp\mysql\bin\mysql.exe" --port=3307 -u root
   ```
3. Type the following SQL commands:
   ```sql
   USE covid_vaccination;
   SHOW TABLES;
   SELECT * FROM Citizens;
   SELECT * FROM Vaccinations;
   ```
4. You will see the tabular data printed directly in your terminal.

---

## 6. Security and Integrity Architecture

1. **SQL Injection Prevention:**
   Every query accepting user data utilizes Prepared Statements (`mysqli_prepare`, `mysqli_stmt_bind_param`). User input is treated strictly as data parameters, never as executable SQL code.

2. **Cross-Site Scripting (XSS) Prevention:**
   Every output string rendered from the database into the HTML output is passed through `htmlspecialchars()`. This neutralizes any malicious `<script>` or HTML tags.

3. **Referential Integrity Protection:**
   Foreign key constraints in MySQL prevent orphaned records. If a staff member attempts to delete a citizen or center that is linked to a vaccination log, the application checks foreign key references and shows an error message instead of corrupting the database.
