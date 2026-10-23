CREATE DATABASE IF NOT EXISTS property_tax;
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
