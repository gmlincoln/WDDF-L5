/**
 * Database Setup & Seeding Script
 * Initializes MySQL database 'student_mgmt_db' and creates default tables & seed data.
 */
require('dotenv').config();
const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');

const setupDatabase = async () => {
  let connection;
  try {
    console.log('[Setup DB] Connecting to MySQL server...');
    connection = await mysql.createConnection({
      host: process.env.DB_HOST || 'localhost',
      port: process.env.DB_PORT || 3306,
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD !== undefined ? process.env.DB_PASSWORD : ''
    });

    console.log('[Setup DB] Creating database student_mgmt_db if not exists...');
    await connection.query('CREATE DATABASE IF NOT EXISTS student_mgmt_db');
    await connection.query('USE student_mgmt_db');

    console.log('[Setup DB] Creating tables...');
    await connection.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    await connection.query(`
      CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        department VARCHAR(100) NOT NULL,
        marks FLOAT NOT NULL DEFAULT 0,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    await connection.query(`
      CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id VARCHAR(50) NOT NULL UNIQUE,
        title VARCHAR(150) NOT NULL,
        capacity INT NOT NULL DEFAULT 30,
        fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    await connection.query(`
      CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        course_id VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'enrolled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT unique_student_course UNIQUE (student_id, course_id),
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE ON UPDATE CASCADE
      )
    `);

    console.log('[Setup DB] Seeding default users (Admin & Staff)...');
    const adminPassword = await bcrypt.hash('admin123', 10);
    const staffPassword = await bcrypt.hash('staff123', 10);

    await connection.query(`
      INSERT INTO users (username, password, role) 
      VALUES (?, ?, 'admin') 
      ON DUPLICATE KEY UPDATE role='admin'
    `, ['admin', adminPassword]);

    await connection.query(`
      INSERT INTO users (username, password, role) 
      VALUES (?, ?, 'staff') 
      ON DUPLICATE KEY UPDATE role='staff'
    `, ['staff', staffPassword]);

    console.log('[Setup DB] Seeding sample courses...');
    await connection.query(`
      INSERT INTO courses (course_id, title, capacity, fee)
      VALUES 
        ('CRS-101', 'Web Development Level 5', 2, 5000.00),
        ('CRS-102', 'Python Software Engineering', 30, 4500.00),
        ('CRS-103', 'Database Architecture & Security', 25, 4000.00)
      ON DUPLICATE KEY UPDATE title=VALUES(title), capacity=VALUES(capacity), fee=VALUES(fee)
    `);

    console.log('[Setup DB] Seeding sample students...');
    await connection.query(`
      INSERT INTO students (student_id, name, department, marks, email)
      VALUES 
        ('STU-001', 'Md. Golam Maula', 'Computer Technology', 94.5, 'golam.maula@institute.edu'),
        ('STU-002', 'Rahim Uddin', 'Web Design & Development', 88.0, 'rahim@institute.edu'),
        ('STU-003', 'Karim Ahmed', 'Database Systems', 82.5, 'karim@institute.edu')
      ON DUPLICATE KEY UPDATE name=VALUES(name), department=VALUES(department), marks=VALUES(marks), email=VALUES(email)
    `);

    console.log('[Setup DB] Database setup completed successfully!');
    return true;
  } catch (error) {
    console.error('[Setup DB Error]:', error);
    throw error;
  } finally {
    if (connection) await connection.end();
  }
};

if (require.main === module) {
  setupDatabase()
    .then(() => process.exit(0))
    .catch(() => process.exit(1));
}

module.exports = setupDatabase;
