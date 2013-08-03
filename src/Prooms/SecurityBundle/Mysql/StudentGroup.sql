CREATE TABLE student_groups (
    `id` INT NOT NULL AUTO_INCREMENT,
    `degree` VARCHAR(30),
    `major` VARCHAR(30),
    `year` INT,
    `instrument` VARCHAR(30),
    `gpa_floor` INT,
    `max_hours` INT,
    `opening_datetime` DATETIME,
    PRIMARY KEY (id),
    UNIQUE KEY (degree, major, instrument, year, gpa_floor)
) ENGINE = InnoDB;