-- Initialization script for the grade_center DB
create database grade_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use database grade_center;

CREATE TABLE school (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    street VARCHAR(255) NOT NULL,
    street_num INT NOT NULL,
    director_id INT,
    FOREIGN KEY (director_id) REFERENCES user(id)
);

CREATE TABLE class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade INT NOT NULL CHECK (grade BETWEEN 1 AND 12),
    letter ENUM('A', 'B', 'V', 'G', 'D') NOT NULL,
    school_id INT NOT NULL,
    FOREIGN KEY (school_id) REFERENCES school(id)
);

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    class_id INT,
    FOREIGN KEY (class_id) REFERENCES class(id)
);

CREATE TABLE parents_children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    child_id INT NOT NULL,
    FOREIGN KEY (parent_id) REFERENCES user(id),
    FOREIGN KEY (child_id) REFERENCES user(id)
);

CREATE TABLE subject (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE grade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade DECIMAL(3, 2) NOT NULL CHECK (grade BETWEEN 2.00 AND 6.00),
    user_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (subject_id) REFERENCES subject(id)
);

CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('admin', 'director', 'teacher', 'parent', 'student') NOT NULL
);

CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (role_id) REFERENCES role(id)
);

CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES user(id),
    FOREIGN KEY (subject_id) REFERENCES subject(id)
);

CREATE TABLE time_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_week ENUM('mon', 'tue', 'wed', 'thr', 'fri', 'sat', 'sun') NOT NULL,
    time_start TIME NOT NULL,
    time_end TIME NOT NULL,
    semester VARCHAR(50) NOT NULL,
    teacher_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES user(id),
    FOREIGN KEY (class_id) REFERENCES class(id),
    FOREIGN KEY (subject_id) REFERENCES subject(id)
);

CREATE TABLE absences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_of_absence DATE NOT NULL,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES user(id),
    FOREIGN KEY (subject_id) REFERENCES subject(id)
);