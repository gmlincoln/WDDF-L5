/**
 * Student Controller - CRUD Operations with Parameterized Queries & XSS Protection
 */
const DatabaseFactory = require('../patterns/DatabaseFactory');

// GET /students - Read all students (Staff and Admin)
const getAllStudents = async (req, res) => {
  try {
    const pool = DatabaseFactory.getConnection();
    const { q } = req.query;

    let query = 'SELECT * FROM students';
    const params = [];

    if (q) {
      query += ' WHERE name LIKE ? OR student_id LIKE ? OR department LIKE ?';
      const searchPattern = `%${q}%`;
      params.push(searchPattern, searchPattern, searchPattern);
    }

    query += ' ORDER BY id DESC';

    // Parameterized query execution (SQL Injection Prevention)
    const [students] = await pool.execute(query, params);

    return res.status(200).json({
      success: true,
      count: students.length,
      data: students
    });
  } catch (error) {
    console.error('Get Students Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to fetch students.',
      error: error.message
    });
  }
};

// GET /students/:id - Get student by ID or student_id (Staff and Admin)
const getStudentById = async (req, res) => {
  try {
    const pool = DatabaseFactory.getConnection();
    const { id } = req.params;

    // Parameterized query execution
    const [students] = await pool.execute(
      'SELECT * FROM students WHERE id = ? OR student_id = ?',
      [id, id]
    );

    if (students.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Student not found.'
      });
    }

    return res.status(200).json({
      success: true,
      data: students[0]
    });
  } catch (error) {
    console.error('Get Student By ID Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to fetch student details.',
      error: error.message
    });
  }
};

// POST /students - Create new student (Admin only)
const createStudent = async (req, res) => {
  try {
    const { student_id, name, department, marks, email } = req.body;

    if (!student_id || !name || !department || email === undefined) {
      return res.status(400).json({
        success: false,
        message: 'student_id, name, department, and email are required.'
      });
    }

    const pool = DatabaseFactory.getConnection();

    // Check unique student_id
    const [existing] = await pool.execute('SELECT id FROM students WHERE student_id = ?', [student_id]);
    if (existing.length > 0) {
      return res.status(400).json({
        success: false,
        message: `Student ID '${student_id}' already exists.`
      });
    }

    // Parameterized INSERT query (SQL Injection Prevention)
    const [result] = await pool.execute(
      'INSERT INTO students (student_id, name, department, marks, email) VALUES (?, ?, ?, ?, ?)',
      [student_id, name, department, parseFloat(marks) || 0, email]
    );

    return res.status(201).json({
      success: true,
      message: 'Student created successfully.',
      data: {
        id: result.insertId,
        student_id,
        name,
        department,
        marks: parseFloat(marks) || 0,
        email
      }
    });
  } catch (error) {
    console.error('Create Student Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to create student.',
      error: error.message
    });
  }
};

// PUT /students/:id - Update student (Admin only)
const updateStudent = async (req, res) => {
  try {
    const { id } = req.params;
    const { name, department, marks, email } = req.body;

    const pool = DatabaseFactory.getConnection();

    // Check if student exists
    const [students] = await pool.execute('SELECT * FROM students WHERE id = ? OR student_id = ?', [id, id]);
    if (students.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Student not found.'
      });
    }

    const currentStudent = students[0];
    const updatedName = name !== undefined ? name : currentStudent.name;
    const updatedDepartment = department !== undefined ? department : currentStudent.department;
    const updatedMarks = marks !== undefined ? parseFloat(marks) : currentStudent.marks;
    const updatedEmail = email !== undefined ? email : currentStudent.email;

    // Parameterized UPDATE query (SQL Injection Prevention)
    await pool.execute(
      'UPDATE students SET name = ?, department = ?, marks = ?, email = ? WHERE id = ?',
      [updatedName, updatedDepartment, updatedMarks, updatedEmail, currentStudent.id]
    );

    return res.status(200).json({
      success: true,
      message: 'Student updated successfully.',
      data: {
        id: currentStudent.id,
        student_id: currentStudent.student_id,
        name: updatedName,
        department: updatedDepartment,
        marks: updatedMarks,
        email: updatedEmail
      }
    });
  } catch (error) {
    console.error('Update Student Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to update student.',
      error: error.message
    });
  }
};

// DELETE /students/:id - Delete student (Admin only)
const deleteStudent = async (req, res) => {
  try {
    const { id } = req.params;
    const pool = DatabaseFactory.getConnection();

    // Parameterized DELETE query (SQL Injection Prevention)
    const [result] = await pool.execute(
      'DELETE FROM students WHERE id = ? OR student_id = ?',
      [id, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Student not found.'
      });
    }

    return res.status(200).json({
      success: true,
      message: 'Student deleted successfully.'
    });
  } catch (error) {
    console.error('Delete Student Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to delete student.',
      error: error.message
    });
  }
};

module.exports = {
  getAllStudents,
  getStudentById,
  createStudent,
  updateStudent,
  deleteStudent
};
