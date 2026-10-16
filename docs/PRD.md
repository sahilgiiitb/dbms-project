# Product Requirements Document (PRD)

**Project Title:** COVID Vaccination Management System
**Subject:** Database Management Systems (DBMS)
**Name:** Sahil Gupta
**Scholar Number:** 25U020128
**Institute:** IIIT Bhopal
**Database:** MySQL
**Frontend:** HTML and CSS

---

## 1. Introduction

During COVID-19, vaccination was done at a very large scale and a lot of data had to be handled: who got vaccinated, which vaccine they got, where they got it and on what date. Handling this on paper or in separate files causes mistakes like duplicate entries and lost records.

This project is a small web based system that stores all this information in a MySQL database and lets the user add, view, update and delete records through simple web pages.

## 2. Problem Statement

There is no single place where a vaccination center can keep track of citizens and their vaccination details. Because of this:

- The same person can get registered more than once.
- It is hard to check how many doses a person has taken.
- It is hard to find how many people were vaccinated at a particular center or with a particular vaccine.

The system should solve these problems using a properly designed relational database.

## 3. Objectives

- Design a database in a normalized form with proper primary keys, foreign keys and unique constraints.
- Store citizen, center, vaccine and vaccination details.
- Prevent duplicate citizens using the unique Aadhaar number.
- Show the vaccination records by joining the tables.
- Give basic reports like center wise and vaccine wise vaccination count.

## 4. Users

| User | What they do |
|------|--------------|
| Admin / Center Staff | Registers citizens, adds centers and vaccines, records vaccinations, views reports |

There is only one type of user, so no login system is required (kept out of scope to keep the project simple).

## 5. Scope

**In scope**

- CRUD operations on Citizens, Vaccination Centers and Vaccines
- Recording and viewing vaccinations
- Search by citizen name, Aadhaar or mobile
- Simple reports using SQL queries (JOIN, GROUP BY, COUNT)

**Out of scope**

- Login and roles
- Online slot booking
- SMS or email alerts
- Certificate generation

## 6. Database Overview

The database has four tables.

1. **Citizens** (citizen_id PK, citizen_name, aadhaar_no UNIQUE, mobile)
2. **VaccinationCenters** (center_id PK, center_name, district, state)
3. **Vaccines** (vaccine_id PK, vaccine_name, manufacturer, doses_required)
4. **Vaccinations** (vaccination_id PK, citizen_id FK, vaccine_id FK, center_id FK, vaccination_date)

**Relationships**

- One citizen can have many vaccinations (1 : M)
- One vaccine can be used in many vaccinations (1 : M)
- One center can have many vaccinations (1 : M)
- Vaccinations is the table that connects the other three.

## 7. Functional Requirements

### 7.1 Citizens
- Add a new citizen with name, Aadhaar number and mobile number.
- View the list of all citizens.
- Search a citizen.
- Edit citizen details.
- Delete a citizen (only if no vaccination record exists for them).

### 7.2 Vaccination Centers
- Add, view, edit and delete centers.
- Filter centers by state or district.

### 7.3 Vaccines
- Add, view, edit and delete vaccines.
- Each vaccine stores how many doses are required (for example 1 or 2).

### 7.4 Vaccinations
- Record a vaccination by selecting a citizen, a vaccine, a center and a date.
- View all vaccination records with citizen name, vaccine name and center name.
- Delete a wrong vaccination entry.

### 7.5 Reports
- Total vaccinations at each center.
- Total vaccinations for each vaccine.
- List of citizens who have completed all required doses.
- List of citizens whose doses are still pending.

## 8. Business Rules

1. Aadhaar number must be exactly 12 digits and cannot repeat.
2. Mobile number must be exactly 10 digits.
3. A citizen cannot get more doses of a vaccine than its `doses_required`.
4. Vaccination date cannot be a future date.
5. A citizen, vaccine or center cannot be deleted if it is used in the Vaccinations table.

## 9. Pages of the Website

| Page | Purpose |
|------|---------|
| Home | Shows total counts and links to all pages |
| Citizens | Add, list, search, edit, delete citizens |
| Centers | Add, list, edit, delete centers |
| Vaccines | Add, list, edit, delete vaccines |
| Vaccinations | Record and list vaccinations |
| Reports | Shows the report queries output |

## 10. Non Functional Requirements

- Should work on any modern browser.
- Pages should load quickly on a local server (XAMPP).
- Simple and clean design that is easy to use.
- All user input must be checked before saving to the database.

## 11. Limitations

- No login, so anyone with the link can use it.
- Works on a local server only.
- No JavaScript, so validations are done on the server side.

## 12. Future Improvements

- Login for admin and staff
- Vaccination certificate in PDF
- Slot booking for citizens
- Reminder for the second dose
