CREATE TABLE rooms (
    id INT NOT NULL PRIMARY KEY,
    piano_type VARCHAR(30),
    piano_detail VARCHAR(50),
    max_people_allowed INT NOT NULL,
    description VARCHAR(100) NOT NULL,
    image VARCHAR(50)
);

CREATE TABLE rooms_unavailable_hours (
    room_id INT NOT NULL,
    day_of_week INT NOT NULL,
    start_time INT NOT NULL,
    duration INT NOT NULL,
    term INT NOT NULL,
    PRIMARY KEY (room_id, day_of_week, start_time, term),
    FOREIGN KEY (room_id) REFERENCES rooms(id) 
    ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE rooms_constraints (
    room_id INT NOT NULL,
    instrument VARCHAR(30) NOT NULL,
    PRIMARY KEY (room_id, instrument),
    FOREIGN KEY (room_id) REFERENCES rooms(id) 
    ON UPDATE CASCADE ON DELETE CASCADE
);