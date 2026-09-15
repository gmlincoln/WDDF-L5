/**
 * Course Controller - CRUD Operations with Parameterized Queries
 */
const DatabaseFactory = require('../patterns/DatabaseFactory');

// GET /courses - Read all courses (Staff and Admin)
const getAllCourses = async (req, res) => {
  try {
    const pool = DatabaseFactory.getConnection();
    const [courses] = await pool.execute('SELECT * FROM courses ORDER BY id DESC');

    return res.status(200).json({
      success: true,
      count: courses.length,
      data: courses
    });
  } catch (error) {
    console.error('Get Courses Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to fetch courses.',
      error: error.message
    });
  }
};

// GET /courses/:id - Get course by ID or course_id (Staff and Admin)
const getCourseById = async (req, res) => {
  try {
    const pool = DatabaseFactory.getConnection();
    const { id } = req.params;

    const [courses] = await pool.execute(
      'SELECT * FROM courses WHERE id = ? OR course_id = ?',
      [id, id]
    );

    if (courses.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Course not found.'
      });
    }

    return res.status(200).json({
      success: true,
      data: courses[0]
    });
  } catch (error) {
    console.error('Get Course By ID Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to fetch course details.',
      error: error.message
    });
  }
};

// POST /courses - Create course (Admin only)
const createCourse = async (req, res) => {
  try {
    const { course_id, title, capacity, fee } = req.body;

    if (!course_id || !title || capacity === undefined) {
      return res.status(400).json({
        success: false,
        message: 'course_id, title, and capacity are required.'
      });
    }

    const pool = DatabaseFactory.getConnection();

    // Check unique course_id
    const [existing] = await pool.execute('SELECT id FROM courses WHERE course_id = ?', [course_id]);
    if (existing.length > 0) {
      return res.status(400).json({
        success: false,
        message: `Course ID '${course_id}' already exists.`
      });
    }

    // Parameterized INSERT query
    const [result] = await pool.execute(
      'INSERT INTO courses (course_id, title, capacity, fee) VALUES (?, ?, ?, ?)',
      [course_id, title, parseInt(capacity, 10) || 30, parseFloat(fee) || 0.0]
    );

    return res.status(201).json({
      success: true,
      message: 'Course created successfully.',
      data: {
        id: result.insertId,
        course_id,
        title,
        capacity: parseInt(capacity, 10) || 30,
        fee: parseFloat(fee) || 0.0
      }
    });
  } catch (error) {
    console.error('Create Course Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to create course.',
      error: error.message
    });
  }
};

// PUT /courses/:id - Update course (Admin only)
const updateCourse = async (req, res) => {
  try {
    const { id } = req.params;
    const { title, capacity, fee } = req.body;

    const pool = DatabaseFactory.getConnection();

    const [courses] = await pool.execute('SELECT * FROM courses WHERE id = ? OR course_id = ?', [id, id]);
    if (courses.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Course not found.'
      });
    }

    const currentCourse = courses[0];
    const updatedTitle = title !== undefined ? title : currentCourse.title;
    const updatedCapacity = capacity !== undefined ? parseInt(capacity, 10) : currentCourse.capacity;
    const updatedFee = fee !== undefined ? parseFloat(fee) : currentCourse.fee;

    await pool.execute(
      'UPDATE courses SET title = ?, capacity = ?, fee = ? WHERE id = ?',
      [updatedTitle, updatedCapacity, updatedFee, currentCourse.id]
    );

    return res.status(200).json({
      success: true,
      message: 'Course updated successfully.',
      data: {
        id: currentCourse.id,
        course_id: currentCourse.course_id,
        title: updatedTitle,
        capacity: updatedCapacity,
        fee: updatedFee
      }
    });
  } catch (error) {
    console.error('Update Course Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to update course.',
      error: error.message
    });
  }
};

// DELETE /courses/:id - Delete course (Admin only)
const deleteCourse = async (req, res) => {
  try {
    const { id } = req.params;
    const pool = DatabaseFactory.getConnection();

    const [result] = await pool.execute(
      'DELETE FROM courses WHERE id = ? OR course_id = ?',
      [id, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Course not found.'
      });
    }

    return res.status(200).json({
      success: true,
      message: 'Course deleted successfully.'
    });
  } catch (error) {
    console.error('Delete Course Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to delete course.',
      error: error.message
    });
  }
};

module.exports = {
  getAllCourses,
  getCourseById,
  createCourse,
  updateCourse,
  deleteCourse
};
