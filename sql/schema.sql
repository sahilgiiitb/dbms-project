CREATE DATABASE IF NOT EXISTS covid_vaccination;
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
