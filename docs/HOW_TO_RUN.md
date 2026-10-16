# How to Run, Test, and Deploy to the Cloud

## 1. Running Locally on Your Machine

Your system has two easy ways to run the project locally.

---

### Option A: Running via XAMPP (Standard Approach)

1. **Start Apache and MySQL:**
   - Open the **XAMPP Control Panel**.
   - Click **Start** next to **Apache**.
   - Click **Start** next to **MySQL**.

2. **Verify Project in `htdocs`:**
   - Ensure the project folder is inside your XAMPP web root directory:
     ```text
     C:\xampp\htdocs\covid-vaccination-system\
     ```
   *(If not present, copy the files from `d:\DBMS project` into `C:\xampp\htdocs\covid-vaccination-system`).*

3. **Import Database (One-time Setup):**
   - Open your browser and navigate to:
     ```text
     http://localhost/phpmyadmin/
     ```
   - Click on the **SQL** tab at the top.
   - Open and copy the contents of `sql/schema.sql`, paste it into the SQL box, and click **Go**.
   - Open and copy the contents of `sql/sample_data.sql`, paste it into the SQL box, and click **Go**.

4. **Open the Website in Browser:**
   - Open your web browser and go to:
     ```text
     http://localhost/covid-vaccination-system/
     ```

---

### Option B: Running via PHP Built-in Server (Terminal Command)

If Apache is ever unavailable or occupied, PHP has its own built-in web server that can run directly from the terminal without needing Apache.

1. Open Windows PowerShell or Command Prompt.
2. Navigate to your project directory:
   ```powershell
   cd "d:\DBMS project"
   ```
3. Run the following command:
   ```powershell
   C:\xampp\php\php.exe -S localhost:8000
   ```
   *(Or just `php -S localhost:8000` if PHP is added to your system PATH).*
4. Open your browser and visit:
   ```text
   http://localhost:8000/
   ```
5. Press `Ctrl + C` in the terminal whenever you wish to stop the server.

---

## 2. How to Use the Application (Page-by-Page Guide)

### 1. Dashboard (`index.php`)
- Displays real-time total counts of Citizens, Centers, Vaccines, and Vaccinations directly from MySQL.
- Provides quick links to register a citizen, record a vaccination, or view reports.

### 2. Citizens Management (`citizens.php`)
- **Add Citizen:** Fill in Full Name, 12-digit numeric Aadhaar number, and 10-digit mobile number, then click "Register Citizen".
- **Search:** Enter a partial name, Aadhaar number, or mobile number into the search bar and click "Search" to filter the directory. Click "Reset" to clear filters.
- **Edit (`citizen_edit.php`):** Click the "Edit" button next to any citizen to modify their name or mobile number.
- **Delete:** Click the red "Delete" button. If the citizen has vaccination logs, deletion is blocked to protect database integrity.

### 3. Vaccination Centers (`centers.php`)
- **Add Center:** Enter Center Name, District, and State, then click "Add Center".
- **State Filter:** Select a state from the dropdown and click "Filter" to show only centers in that state.
- **Edit (`center_edit.php`):** Modify center name or location.
- **Delete:** Blocked if any vaccination record was administered at this center.

### 4. Vaccines Inventory (`vaccines.php`)
- **Add Vaccine:** Enter Vaccine Name (e.g. Novavax), Manufacturer, and Required Doses (e.g. 2).
- **Edit (`vaccine_edit.php`):** Update dose requirements or manufacturer.
- **Delete:** Blocked if the vaccine has already been administered to any citizen.

### 5. Vaccination Records (`vaccinations.php`)
- **Record Vaccination:**
  1. Select a citizen from the citizen dropdown.
  2. Select the vaccine administered.
  3. Select the vaccination center.
  4. Select the vaccination date (future dates are blocked).
  5. Click "Save Record".
- **Dose Validation:** If the citizen has already received the maximum allowed doses for that vaccine, the system rejects the entry with an error message.
- **Table Display:** Displays logs with human-readable citizen names, vaccine names, and center names using a multi-table SQL `JOIN`.

### 6. Reports & Analytics (`reports.php`)
Displays 5 automated analytical reports:
1. Center-wise vaccination totals.
2. Vaccine-wise total doses administered.
3. Citizens who have completed all required doses.
4. Citizens with doses still pending.
5. Chronological vaccination logs summary.

---

## 3. Test Cases Verification Checklist

Use this checklist from TRD Section 11 to test the system:

| Test # | Test Case Description | How to Test | Expected Behavior |
|:---:|:---|:---|:---|
| **1** | Valid Citizen Registration | Enter valid name, unique 12-digit Aadhaar, 10-digit mobile | Success message; citizen appears in table |
| **2** | Duplicate Aadhaar Check | Enter Aadhaar `123456789012` (Rajesh Kumar's Aadhaar) | Red error: *"A citizen with this Aadhaar number already exists."* |
| **3** | Aadhaar Length Validation | Enter 11 digits in Aadhaar | Form/PHP blocks submission: *"Aadhaar number must be exactly 12 digits."* |
| **4** | Mobile Format Validation | Enter letters or 9 digits in mobile | Form/PHP blocks submission: *"Mobile number must be exactly 10 digits."* |
| **5** | Future Date Validation | Select tomorrow's date on `vaccinations.php` | Red error: *"Vaccination date cannot be in the future."* |
| **6** | Dose Limit Validation | Select Rajesh Kumar and try adding a 3rd dose of Covishield | Red error: *"Citizen has already received all required doses (2) for this vaccine."* |
| **7** | Referential Integrity (Delete) | Try deleting Rajesh Kumar (has vaccination logs) | Red error: *"Cannot delete citizen: vaccination records exist."* |
| **8** | Safe Deletion | Delete Pooja Nair (has 0 vaccination logs) | Citizen deleted cleanly; success message shown |
| **9** | Search Directory | Type "Priya" or "2345" in the citizens search box | Table filters to show only Priya Sharma's row |
| **10** | Analytical Reports | Open `reports.php` | All 5 report tables display correct SQL calculations |

---

## 4. How to Upload and Deploy to the Cloud

To share your project with teachers, classmates, or on your resume, you can deploy it to a free cloud hosting service.

### Recommended Free Cloud Host: InfinityFree (or AwardSpace / 000webhost)

These platforms provide free PHP and MySQL hosting with a web-based control panel (cPanel) and phpMyAdmin.

#### Step 1: Export Your Local Database
1. Open Windows PowerShell and navigate to your project directory:
   ```powershell
   cd "d:\DBMS project"
   ```
2. Export your database into a single SQL backup file:
   ```powershell
   & "C:\xampp\mysql\bin\mysqldump.exe" --port=3307 -u root covid_vaccination > full_backup.sql
   ```
   *(Alternatively, in local phpMyAdmin, click `covid_vaccination` -> **Export** -> **Go**).*

#### Step 2: Create a Cloud Account
1. Visit [InfinityFree.com](https://www.infinityfree.com/) (or any free cPanel PHP host) and sign up for a free account.
2. In the dashboard, click **Create Account**.
3. Choose a free subdomain (for example: `covid-vaccination.infinityfreeapp.com`).
4. Wait 1-2 minutes for the account to be activated.

#### Step 3: Create MySQL Database on Cloud
1. In your cloud hosting dashboard, open the **Control Panel** (cPanel).
2. Click **MySQL Databases**.
3. Create a new database named `covid_vaccination`.
4. Note down the following cloud database connection credentials shown on your screen:
   - **MySQL Hostname:** (e.g. `sql123.infinityfree.com`)
   - **Database Name:** (e.g. `if0_12345678_covid_vaccination`)
   - **Username:** (e.g. `if0_12345678`)
   - **Password:** (Your account password)

#### Step 4: Import Database into Cloud phpMyAdmin
1. In your cloud control panel, click **phpMyAdmin**.
2. Select your newly created database.
3. Click the **Import** tab at the top.
4. Click **Choose File**, select `sql/schema.sql` and click **Go**.
5. Repeat for `sql/sample_data.sql` (or upload your `full_backup.sql`).
6. All 4 tables and sample records are now hosted in the cloud.

#### Step 5: Upload Project Files
1. In your cloud control panel, click **Online File Manager**.
2. Navigate into the **`htdocs`** (or `public_html`) directory.
3. Upload all project files:
   - `index.php`
   - `citizens.php`, `citizen_edit.php`
   - `centers.php`, `center_edit.php`
   - `vaccines.php`, `vaccine_edit.php`
   - `vaccinations.php`
   - `reports.php`
   - `header.php`, `footer.php`
   - `css/style.css` (inside a `css` folder)
   - `db.php`

#### Step 6: Update `db.php` for Cloud Credentials
In the cloud File Manager, edit `db.php` so that it connects using your cloud database details:
```php
<?php
$con = mysqli_connect(
    "sql123.infinityfree.com", // Your cloud MySQL hostname
    "if0_12345678",            // Your cloud MySQL username
    "your_password_here",      // Your cloud MySQL password
    "if0_12345678_covid_vaccination" // Your cloud database name
);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

#### Step 7: Test Your Live Website
Visit your free public domain (e.g. `http://covid-vaccination.infinityfreeapp.com/`). Your COVID Vaccination Management System is now live on the internet!
