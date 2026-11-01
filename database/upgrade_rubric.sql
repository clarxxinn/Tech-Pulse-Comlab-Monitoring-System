-- One-time upgrade for an existing monitoring database. Back up first.
CREATE TABLE courses (
    course_code varchar(50) NOT NULL PRIMARY KEY,
    course_name varchar(150) NOT NULL,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    CONSTRAINT chk_course_name CHECK (CHAR_LENGTH(TRIM(course_name)) > 0),
    CONSTRAINT chk_course_active CHECK (is_active IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO courses (course_code, course_name) VALUES
('ACT','ACT - Associate in Computer Technology'),
('BSIS','BSIS - BS in Information Systems'),
('BSAIS','BSAIS - BS in Accounting Information System'),
('BSOM','BSOM - BS in Office Management'),
('BSCA','BSCA - BS in Customs Administration'),
('BTVTED','BTVTED - BS in Technical-Vocational Teacher Education'),
('DHRMT','DHRMT - Diploma in Hotel and Restaurant Management Technology'),
('HB','HB - Bundled Hotel and Restaurant Services');
-- Unknown historical values remain explicitly inactive until reviewed.
INSERT INTO courses (course_code, course_name, is_active)
SELECT DISTINCT u.course, CONCAT(u.course, ' (legacy - needs review)'), 0
FROM users u LEFT JOIN courses c ON c.course_code=u.course WHERE c.course_code IS NULL;

CREATE TABLE year_levels (
    year_name varchar(50) NOT NULL PRIMARY KEY,
    sort_order tinyint unsigned NOT NULL,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    CONSTRAINT chk_year_name CHECK (CHAR_LENGTH(TRIM(year_name)) > 0),
    CONSTRAINT chk_year_order CHECK (sort_order > 0),
    CONSTRAINT chk_year_active CHECK (is_active IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO year_levels (year_name, sort_order) VALUES
('1st Year',1),('2nd Year',2),('3rd Year',3),('4th Year',4);
UPDATE users SET yearlevel=CONCAT(yearlevel, ' Year') WHERE yearlevel IN ('1st','2nd','3rd','4th');
INSERT INTO year_levels (year_name, sort_order, is_active)
SELECT DISTINCT u.yearlevel, 255, 0 FROM users u
LEFT JOIN year_levels y ON y.year_name=u.yearlevel WHERE y.year_name IS NULL;

ALTER TABLE users
    ADD CONSTRAINT chk_student_id CHECK (StudentID > 0),
    ADD CONSTRAINT chk_firstname CHECK (CHAR_LENGTH(TRIM(firstname)) > 0),
    ADD CONSTRAINT chk_lastname CHECK (CHAR_LENGTH(TRIM(lastname)) > 0),
    ADD CONSTRAINT chk_password_hash CHECK (CHAR_LENGTH(password) BETWEEN 60 AND 255),
    ADD CONSTRAINT fk_user_course FOREIGN KEY (course) REFERENCES courses(course_code),
    ADD CONSTRAINT fk_user_year FOREIGN KEY (yearlevel) REFERENCES year_levels(year_name),
    ADD INDEX ix_user_created (created_at, StudentID);
ALTER TABLE computers ADD CONSTRAINT chk_pc_number CHECK (pc_number BETWEEN 1 AND 45);
ALTER TABLE laboratories
    ADD CONSTRAINT chk_lab_id CHECK (lab_id > 0),
    ADD CONSTRAINT chk_lab_name CHECK (CHAR_LENGTH(TRIM(lab_name)) > 0);
ALTER TABLE computer_sessions
    MODIFY StudentID int(11) NULL,
    ADD CONSTRAINT chk_session_times CHECK (time_out IS NULL OR time_out >= time_in),
    ADD CONSTRAINT chk_session_student CHECK (StudentID IS NOT NULL OR time_out IS NOT NULL),
    ADD INDEX ix_session_recent (time_in, session_id);
