CREATE TABLE users (
    `id` INT NOT NULL,
    `name` VARCHAR(30) NOT NULL,
    `username` VARCHAR(25) NOT NULL,
    `salt` VARCHAR(32) NOT NULL,
    `password` VARCHAR(40) NOT NULL,
    `degree` VARCHAR(30) NOT NULL,
    `major` VARCHAR(30) NOT NULL,
    `year` INT NOT NULL,
    `instrument` VARCHAR(30) NOT NULL,
    `phone` VARCHAR(10) NOT NULL,
    `email` VARCHAR(60) NOT NULL,
    `mailing_address` VARCHAR(100) NOT NULL,
    `permission_to_publish_personal_info` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (student_id),
    UNIQUE KEY (username)
) ENGINE = InnoDB;

CREATE TABLE groups (
    `id` INT AUTO_INCREMENT NOT NULL,
    `name` VARCHAR(30) NOT NULL,
    `role` VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE users_groups(
    user_student_id INT NOT NULL,
    group_id INT NOT NULL,
    PRIMARY KEY (user_student_id, group_id)
) ENGINE = InnoDB;

ALTER TABLE users_groups
ADD FOREIGN KEY (user_student_id) REFERENCES users(student_id)
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE users_groups
ADD FOREIGN KEY (group_id) REFERENCES groups(id)
ON DELETE CASCADE ON UPDATE CASCADE;