-- cd C:\xampp\htdocs\Honor_List_System
-- cd C:\Users\Shainiee\OneDrive\Desktop\BSIT\GitHub\Honor_List_System
--php -S localhost:8080

-- Access: http://localhost:8080/signUp.php

-- Create database (optional, you can use an existing DB)
CREATE DATABASE honorlist;

-- Connect to the new database
\c honorlist;

-- Table: register (main student table)
CREATE TABLE register (
    id SERIAL PRIMARY KEY,
    students_id INTEGER UNIQUE NOT NULL,
    fullname VARCHAR(150) NOT NULL,
    birthdate DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    age INTEGER NOT NULL,
    strand VARCHAR(50) NOT NULL,
    average DECIMAL(5,2) NOT NULL,
    year INTEGER NOT NULL,
    remarks VARCHAR(50)
);

-- Honor tables for each strand
CREATE TABLE honor_abm (
    id SERIAL PRIMARY KEY,
    students_id INTEGER NOT NULL REFERENCES register(students_id) ON DELETE CASCADE,
    fullname VARCHAR(150) NOT NULL,
    average DECIMAL(5,2) NOT NULL,
    year INTEGER NOT NULL,
    remarks VARCHAR(50)
);

CREATE TABLE honor_stem (LIKE honor_abm INCLUDING ALL);
CREATE TABLE honor_humss (LIKE honor_abm INCLUDING ALL);
CREATE TABLE honor_ict (LIKE honor_abm INCLUDING ALL);
CREATE TABLE honor_eim (LIKE honor_abm INCLUDING ALL);
CREATE TABLE honor_he (LIKE honor_abm INCLUDING ALL);
CREATE TABLE honor_plumbing (LIKE honor_abm INCLUDING ALL);

-- Table: signup (user accounts)
CREATE TABLE signup (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    confirm_pass VARCHAR(255) NOT NULL
);

-- Table: teachers_id (authorized teacher IDs)
CREATE TABLE teachers_id (
    id SERIAL PRIMARY KEY,
    teachers_id INTEGER UNIQUE NOT NULL
);