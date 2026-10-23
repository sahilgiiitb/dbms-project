# Product Requirements Document (PRD)

**Project Title:** Municipal Property Tax System
**Subject:** Database Management Systems (DBMS)
**Name:** Sahil Gupta
**Scholar Number:** 25U020128
**Institute:** IIIT Bhopal
**Database:** MySQL
**Frontend:** HTML and CSS
**Backend:** PHP

---

## 1. Introduction

Every municipality collects property tax from property owners every year. The tax is calculated for each property, the owner pays it fully or in parts, and the office has to keep track of who has paid and who still owes money. When this is done on paper or in loose spreadsheets, records get lost, payments are entered twice and it becomes hard to find defaulters.

This project is a web based system that stores owners, properties, yearly tax assessments and payments in a MySQL database. The staff of the municipal office can use it to manage these records and see how much tax is pending.

## 2. Problem Statement

The municipal office has no single place to track property tax. Because of this:

- It is difficult to know which properties belong to which owner.
- It is difficult to know how much tax is assessed for a property in a given year.
- Part payments are hard to track, so the pending amount is often wrong.
- Finding defaulters takes a lot of manual work.

The system should solve these problems using a properly designed relational database.

## 3. Objectives

- Design a normalized database with primary keys, foreign keys and constraints.
- Store owner, property, tax assessment and payment details.
- Calculate the paid amount and the pending amount of every assessment automatically from the payments table.
- Show yearly, property type wise and owner wise reports using SQL queries (JOIN, GROUP BY, SUM, HAVING).
- Keep the interface plain, clear and easy for office staff.

## 4. Users

| User | What they do |
|------|--------------|
| Municipal office staff | Adds owners and properties, creates yearly assessments, records payments, views reports |

There is only one type of user. Login is out of scope to keep the project focused on the database.

## 5. Technology Constraints

- Frontend must be written in plain HTML and CSS only.
- No React, no React Native, no JavaScript framework and no JavaScript code in the pages.
- Backend must be written in plain PHP.
- Data must be stored in a MySQL database (XAMPP).
- No CSS framework or UI library (no Bootstrap, no Tailwind) and no icon library.

## 6. Scope

**In scope**

- Add, view, edit and delete owners, properties, assessments and payments
- Search owners and properties
- Automatic paid, pending and status (Paid, Partial, Unpaid) for each assessment
- Reports using SQL queries
- Static Terms of Use and Privacy Policy pages

**Out of scope**

- Login and roles
- Online payment gateway
- Receipt PDF generation
- SMS or email notifications
- Tax calculation formulas (the tax amount is entered by the staff)

## 7. Database Overview

The database has four tables.

1. **PropertyOwners** (owner_id PK, owner_name, phone, email)
2. **Properties** (property_id PK, owner_id FK, property_address, property_type)
3. **TaxAssessments** (assessment_id PK, property_id FK, assessment_year, tax_amount)
4. **Payments** (payment_id PK, assessment_id FK, payment_date, amount_paid)

**Relationships**

- One owner can own many properties (1 : M)
- One property can have many yearly assessments (1 : M)
- One assessment can have many payments because of part payments (1 : M)

## 8. Functional Requirements

### 8.1 Owners
- Add an owner with name, phone and email.
- View all owners and search by name or phone.
- Edit and delete an owner.
- An owner who has properties cannot be deleted.

### 8.2 Properties
- Add a property by selecting an owner, entering the address and choosing the type (Residential, Commercial, Industrial, Agricultural).
- View all properties with the owner name.
- Filter by property type.
- Edit and delete a property.
- A property that has assessments cannot be deleted.

### 8.3 Tax Assessments
- Create an assessment by selecting a property, entering the year and the tax amount.
- View all assessments with owner, property, tax amount, amount paid, amount pending and status.
- Filter by year and by status.
- Edit the tax amount if no payment has been made yet.
- An assessment that has payments cannot be deleted.

### 8.4 Payments
- Record a payment by selecting an assessment, the date and the amount.
- View all payments with owner and property details.
- Delete a wrongly entered payment.

### 8.5 Reports
- Defaulters list: assessments with pending amount greater than zero.
- Year wise collection: total assessed, total collected and pending for every year.
- Property type wise tax: assessed and collected amount for each property type.
- Owner wise pending amount.

### 8.6 Home Page
- Total owners, properties, assessments and payments.
- Total assessed, total collected and total pending amount.
- Last five payments.

### 8.7 Static Pages
- Terms of Use and Privacy Policy pages linked from the footer, written in simple language for a municipal office system.

## 9. Business Rules

1. Phone number must be exactly 10 digits.
2. Email, if given, must be in a valid format.
3. Tax amount must be greater than zero.
4. A property can have only one assessment for a given year.
5. Payment amount must be greater than zero and cannot be more than the pending amount of that assessment.
6. Payment date cannot be in the future.
7. Records that are referenced by another table cannot be deleted.

## 10. Pages of the Website

| Page | File | Purpose |
|------|------|---------|
| Home | index.php | Totals and recent payments |
| Owners | owners.php | Add, list, search |
| Edit Owner | owner_edit.php | Update one owner |
| Properties | properties.php | Add, list, filter |
| Edit Property | property_edit.php | Update one property |
| Assessments | assessments.php | Add, list, filter |
| Edit Assessment | assessment_edit.php | Update tax amount |
| Payments | payments.php | Record, list |
| Delete | delete.php | Confirmation page for every delete |
| Reports | reports.php | Four SQL reports |
| Terms of Use | terms.php | Static page |
| Privacy Policy | privacy.php | Static page |

## 11. Design Requirements

The interface should look like a normal office or government records tool written by a student, and it must not look like a generated landing page. The following things must be avoided everywhere in the project, including the website text, the code and the documents:

1. Harsh gradients
2. Lucide icons (and icon packs in general)
3. Pure white page background
4. Rainbow coloring
5. Drop shadows
6. Three feature cards in a row
7. Emojis
8. Liquid glass or glassmorphism
9. Em dashes in any text
10. Inter, Geist or Space Grotesk fonts
11. Colored left stripe on cards or alerts
12. Fake testimonials
13. Bento grids
14. Terminal window mockups
15. The "it is not X, it is Y" style of sentences
16. Checkmark bullet lists
17. Three tier pricing sections
18. Missing real demo content (the app must come with realistic sample data and work end to end)
19. Soft, very round corners (use square or very small radius)
20. Purple and black color scheme
21. Missing loading states (see the note below)
22. Radial glowing orbs
23. Dot grid backgrounds
24. Sparkle icons
25. Animated arrows
26. Missing Terms of Use page
27. Missing Privacy Policy page
28. Hover animations
29. Neon colors
30. Basic pastel colors

**What to use instead**

- Plain tables for data, plain forms for input.
- A warm off white or light grey background and one dark muted blue as the main color with one earthy accent for errors and warnings.
- Thin 1px borders instead of shadows.
- Text labels instead of icons.
- A normal readable font such as Source Sans 3 or Noto Sans, with a system font as fallback.
- Hover only changes the background color of a row or button, with no transition.

**Note on loading states (item 21):** the pages are rendered by PHP on the server and no JavaScript is used, so a skeleton loader cannot be built. Instead, every table shows a plain sentence when it has no data (for example "No payments recorded yet") and every action shows a clear success or error message.

## 12. Non Functional Requirements

- Works on any modern browser on laptop and mobile screens.
- Every user input is checked on the server before it is saved.
- All queries use prepared statements to avoid SQL injection.
- Pages show proper messages for empty data and for errors.

## 13. Limitations

- No login, so it is meant to run on a local machine.
- No JavaScript, so all validation happens after the form is submitted (HTML attributes give a basic first check).
- Tax amount is typed by the user and not calculated from area or rate.

## 14. Future Improvements

- Login for staff and admin
- Receipt generation
- Automatic tax calculation based on area and property type
- Reminder for pending tax
