/**
 * Enrollment Controller - Business Logic Rules:
 * 1. Must prevent duplicate enrollment (same student + same course twice).
 * 2. Must reject enrollment when course capacity is full.
 */
const DatabaseFactory = require('../patterns/DatabaseFactory');

// POST /enrollments - Enroll student in course (Staff and Admin)
const createEnrollment = async (req, res) => {
  try {
    const { student_id, course_id, status } = req.body;

    if (!student_id || !course_id) {
      return res.status(400).json({
        success: false,
        message: 'student_id and course_id are required.'
      });
    }

    const pool = DatabaseFactory.getConnection();

    // 1. Verify Student Exists
    const [students] = await pool.execute('SELECT * FROM students WHERE student_id = ?', [student_id]);
    if (students.length === 0) {
      return res.status(404).json({
        success: false,
        message: `Student with ID '${student_id}' does not exist.`
      });
    }

    // 2. Verify Course Exists
    const [courses] = await pool.execute('SELECT * FROM courses WHERE course_id = ?', [course_id]);
    if (courses.length === 0) {
      return res.status(404).json({
        success: false,
        message: `Course with ID '${course_id}' does not exist.`
      });
    }

    const targetCourse = courses[0];

    // 3. Business Rule Check: Duplicate Enrollment
    const [existingEnrollment] = await pool.execute(
      'SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?',
      [student_id, course_id]
    );

    if (existingEnrollment.length > 0) {
      return res.status(400).json({
        success: false,
        message: 'Duplicate enrollment rejected. Student is already enrolled in this course.'
      });
    }

    // 4. Business Rule Check: Full Course Capacity
    const [countResult] = await pool.execute(
      'SELECT COUNT(*) as totalEnrolled FROM enrollments WHERE course_id = ?',
      [course_id]
    );

    const totalEnrolled = countResult[0].totalEnrolled || 0;

    if (totalEnrolled >= targetCourse.capacity) {
      return res.status(400).json({
        success: false,
        message: `Full course enrollment rejected. Course '${targetCourse.title}' capacity (${targetCourse.capacity}) has been reached.`
      });
    }

    // 5. Insert Enrollment Record (SQL Injection Prevention: Parameterized Query)
    const enrollmentStatus = status || 'enrolled';
    let result;
    try {
      [result] = await pool.execute(
        'INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, ?)',
        [student_id, course_id, enrollmentStatus]
      );
    } catch (dbError) {
      // Catch UNIQUE constraint violation (ER_DUP_ENTRY / code 1062)
      if (dbError.code === 'ER_DUP_ENTRY' || dbError.errno === 1062) {
        return res.status(400).json({
          success: false,
          message: 'Duplicate enrollment rejected. Student is already enrolled in this course.'
        });
      }
      throw dbError;
    }

    return res.status(201).json({
      success: true,
      message: 'Student enrolled in course successfully.',
      data: {
        id: result.insertId,
        student_id,
        course_id,
        status: enrollmentStatus,
        student_name: students[0].name,
        course_title: targetCourse.title
      }
    });
  } catch (error) {
    console.error('Create Enrollment Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to complete enrollment process.',
      error: error.message
    });
  }
};

// GET /enrollments - View all enrollments with student and course details
const getAllEnrollments = async (req, res) => {
  try {
    const pool = DatabaseFactory.getConnection();
    const [enrollments] = await pool.execute(`
      SELECT 
        e.id,
        e.student_id,
        s.name AS student_name,
        s.email AS student_email,
        e.course_id,
        c.title AS course_title,
        c.capacity AS course_capacity,
        e.status,
        e.created_at
      FROM enrollments e
      LEFT JOIN students s ON e.student_id = s.student_id
      LEFT JOIN courses c ON e.course_id = c.course_id
      ORDER BY e.id DESC
    `);

    return res.status(200).json({
      success: true,
      count: enrollments.length,
      data: enrollments
    });
  } catch (error) {
    console.error('Get Enrollments Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to fetch enrollments list.',
      error: error.message
    });
  }
};

module.exports = {
  createEnrollment,
  getAllEnrollments
};
