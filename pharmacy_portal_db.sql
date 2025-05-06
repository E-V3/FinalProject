-- pharmacy_portal_db.sql
-- SQL Script to create all necessary tables, views, stored procedures, and triggers

CREATE DATABASE IF NOT EXISTS pharmacy_portal_db;
USE pharmacy_portal_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS Users (
    userId INT NOT NULL UNIQUE AUTO_INCREMENT,
    userName VARCHAR(45) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contactInfo VARCHAR(200),
    userType ENUM('pharmacist', 'patient') NOT NULL,
    PRIMARY KEY (userId)
);

-- 2. Medications Table
CREATE TABLE IF NOT EXISTS Medications (
    medicationId INT NOT NULL UNIQUE AUTO_INCREMENT,
    medicationName VARCHAR(45) NOT NULL,
    dosage VARCHAR(45) NOT NULL,
    manufacturer VARCHAR(100),
    PRIMARY KEY (medicationId)
);

-- 3. Prescriptions Table
CREATE TABLE IF NOT EXISTS Prescriptions (
    prescriptionId INT NOT NULL UNIQUE AUTO_INCREMENT,
    userId INT NOT NULL,
    medicationId INT NOT NULL,
    prescribedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dosageInstructions VARCHAR(200),
    quantity INT NOT NULL,
    refillCount INT DEFAULT 0,
    PRIMARY KEY (prescriptionId),
    FOREIGN KEY (userId) REFERENCES Users(userId),
    FOREIGN KEY (medicationId) REFERENCES Medications(medicationId)
);

-- 4. Inventory Table
CREATE TABLE IF NOT EXISTS Inventory (
    inventoryId INT NOT NULL UNIQUE AUTO_INCREMENT,
    medicationId INT NOT NULL,
    quantityAvailable INT NOT NULL,
    lastUpdated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (inventoryId),
    FOREIGN KEY (medicationId) REFERENCES Medications(medicationId)
);

-- 5. Sales Table
CREATE TABLE IF NOT EXISTS Sales (
    saleId INT NOT NULL UNIQUE AUTO_INCREMENT,
    prescriptionId INT NOT NULL,
    saleDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    quantitySold INT NOT NULL,
    saleAmount DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (saleId),
    FOREIGN KEY (prescriptionId) REFERENCES Prescriptions(prescriptionId)
);

-- View: MedicationInventoryView
CREATE OR REPLACE VIEW MedicationInventoryView AS
SELECT 
    m.medicationId,
    m.medicationName,
    i.quantityAvailable,
    i.lastUpdated
FROM Medications m
JOIN Inventory i ON m.medicationId = i.medicationId;

-- Fixed Stored Procedure: AddOrUpdateUser
DELIMITER //
CREATE PROCEDURE AddOrUpdateUser(
    IN p_userId INT,
    IN p_userName VARCHAR(45),
    IN p_contactInfo VARCHAR(200),
    IN p_userType VARCHAR(10)
)
BEGIN
    IF p_userId IS NULL THEN
        INSERT INTO Users (userName, contactInfo, userType, password)
        VALUES (p_userName, p_contactInfo, p_userType, '');
    ELSE
        UPDATE Users
        SET userName = p_userName,
            contactInfo = p_contactInfo,
            userType = p_userType
        WHERE userId = p_userId;
    END IF;
END;
//
DELIMITER ;

-- Stored Procedure: RecordSale
DELIMITER //
CREATE PROCEDURE RecordSale(
    IN p_prescriptionId INT,
    IN p_quantitySold INT,
    IN p_saleAmount DECIMAL(10,2)
)
BEGIN
    INSERT INTO Sales (prescriptionId, quantitySold, saleAmount, saleDate)
    VALUES (p_prescriptionId, p_quantitySold, p_saleAmount, NOW());

    UPDATE Inventory
    SET quantityAvailable = quantityAvailable - p_quantitySold,
        lastUpdated = NOW()
    WHERE medicationId = (
        SELECT medicationId FROM Prescriptions WHERE prescriptionId = p_prescriptionId
    );
END;
//
DELIMITER ;

-- Trigger: AutoUpdateInventoryAfterSale
DROP TRIGGER IF EXISTS AutoUpdateInventoryAfterSale;
DELIMITER //
CREATE TRIGGER AutoUpdateInventoryAfterSale
AFTER INSERT ON Sales
FOR EACH ROW
BEGIN
    UPDATE Inventory
    SET quantityAvailable = quantityAvailable - NEW.quantitySold,
        lastUpdated = NOW()
    WHERE medicationId = (
        SELECT medicationId FROM Prescriptions WHERE prescriptionId = NEW.prescriptionId
    );
END;
//
DELIMITER ;

-- Populate Users table
INSERT INTO Users (userName, contactInfo, userType) VALUES
('john_doe', 'john@example.com', 'patient'),
('jane_pharmacist', 'jane@pharmacy.com', 'pharmacist'),
('alice_smith', 'alice@example.com', 'patient');

-- Populate Medications table
INSERT INTO Medications (medicationName, dosage, manufacturer) VALUES
('Amoxicillin', '500mg', 'HealthCorp Inc.'),
('Ibuprofen', '200mg', 'MediCure Ltd.'),
('Metformin', '850mg', 'GlucosePharma');

-- Populate Prescriptions table
INSERT INTO Prescriptions (userId, medicationId, prescribedDate, dosageInstructions, quantity, refillCount) VALUES
(1, 1, NOW(), 'Take one capsule every 8 hours after meals', 30, 1),
(3, 2, NOW(), 'Take one tablet every 6 hours as needed for pain', 20, 0),
(1, 3, NOW(), 'Take one tablet daily with food', 60, 2);

-- Populate Inventory table
INSERT INTO Inventory (medicationId, quantityAvailable, lastUpdated) VALUES
(1, 100, NOW()),
(2, 200, NOW()),
(3, 150, NOW());

-- Populate Sales table
INSERT INTO Sales (prescriptionId, saleDate, quantitySold, saleAmount) VALUES
(1, NOW(), 30, 45.00),
(2, NOW(), 20, 25.00),
(3, NOW(), 60, 90.00);
