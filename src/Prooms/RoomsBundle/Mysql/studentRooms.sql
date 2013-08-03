CREATE TABLE users_rooms(
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    day_of_week INT NOT NULL,
    start_time INT NOT NULL,
    duration INT NOT NULL,
    term INT NOT NULL,
    PRIMARY KEY (user_id, room_id, day_of_week, start_time, term)
) ENGINE = InnoDB;

ALTER TABLE users_rooms
ADD FOREIGN KEY (user_id) REFERENCES users(id)
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE users_rooms
ADD FOREIGN KEY (room_id) REFERENCES rooms(id)
ON DELETE CASCADE ON UPDATE CASCADE;