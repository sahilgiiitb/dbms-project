USE covid_vaccination;

INSERT INTO Citizens (citizen_id, citizen_name, aadhaar_no, mobile) VALUES
(1, 'Rajesh Kumar', '123456789012', '9876543210'),
(2, 'Priya Sharma', '234567890123', '9876543211'),
(3, 'Amit Verma', '345678901234', '9876543212'),
(4, 'Sunita Patel', '456789012345', '9876543213'),
(5, 'Rahul Singh', '567890123456', '9876543214'),
(6, 'Ananya Roy', '678901234567', '9876543215'),
(7, 'Vikas Yadav', '789012345678', '9876543216'),
(8, 'Pooja Nair', '890123456789', '9876543217');

INSERT INTO VaccinationCenters (center_id, center_name, district, state) VALUES
(1, 'City Civil Hospital', 'Bhopal', 'Madhya Pradesh'),
(2, 'Community Health Center', 'Indore', 'Madhya Pradesh'),
(3, 'Metro Care Center', 'Lucknow', 'Uttar Pradesh'),
(4, 'Urban Primary Health Center', 'Jaipur', 'Rajasthan');

INSERT INTO Vaccines (vaccine_id, vaccine_name, manufacturer, doses_required) VALUES
(1, 'Covishield', 'Serum Institute of India', 2),
(2, 'Covaxin', 'Bharat Biotech', 2),
(3, 'Sputnik V', 'Gamaleya Research Institute', 2);

INSERT INTO Vaccinations (vaccination_id, citizen_id, vaccine_id, center_id, vaccination_date) VALUES
(1, 1, 1, 1, '2023-01-15'),
(2, 1, 1, 1, '2023-04-10'),
(3, 2, 2, 2, '2023-02-01'),
(4, 2, 2, 2, '2023-03-05'),
(5, 3, 1, 1, '2023-05-12'),
(6, 4, 3, 3, '2023-03-10'),
(7, 4, 3, 3, '2023-04-02'),
(8, 5, 2, 4, '2023-06-15'),
(9, 6, 1, 1, '2023-02-20'),
(10, 6, 1, 2, '2023-05-25'),
(11, 7, 3, 4, '2023-07-01'),
(12, 7, 3, 4, '2023-07-25');
