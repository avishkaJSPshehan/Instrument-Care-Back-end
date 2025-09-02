CREATE TABLE service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Personal Details
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    physical_address VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,

    -- Institute Details
    institute_name VARCHAR(150) NOT NULL,
    institute_address VARCHAR(255) NOT NULL,

    -- Instrument Details
    instrument_name VARCHAR(150) NOT NULL,
    instrument_brand VARCHAR(100) NOT NULL,
    instrument_model VARCHAR(100) NOT NULL,
    instrument_manufacturer VARCHAR(150) NOT NULL,
    manufactured_year YEAR NOT NULL,
    product_testing_type VARCHAR(150) NOT NULL,
    testing_parameter VARCHAR(150) NOT NULL,
    consumption_period VARCHAR(100) NOT NULL,

    -- Issue Description
    issue_description TEXT NOT NULL,

    -- Technician Assignment
    technician_id INT NULL,

    -- Metadata
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Key
    CONSTRAINT fk_technician FOREIGN KEY (technician_id)
        REFERENCES technician_details(user_id)
        ON DELETE SET NULL
);