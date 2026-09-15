/**
 * Import Controller - Uses Adaptor Pattern to import legacy JSON student data
 */
const DatabaseFactory = require('../patterns/DatabaseFactory');
const LegacyJSONAdapter = require('../patterns/LegacyJSONAdapter');

const importLegacyStudents = async (req, res) => {
  try {
    const legacyPayload = req.body;

    if (!legacyPayload || (Array.isArray(legacyPayload) && legacyPayload.length === 0)) {
      return res.status(400).json({
        success: false,
        message: 'No legacy student data provided in request body.'
      });
    }

    // Adapt legacy JSON structure to standard student entities using Adaptor Pattern
    const standardizedStudents = LegacyJSONAdapter.adapt(legacyPayload);

    if (standardizedStudents.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Could not adapt any valid student records from provided JSON.'
      });
    }

    const pool = DatabaseFactory.getConnection();
    let insertedCount = 0;
    let updatedCount = 0;
    const importedRecords = [];

    for (const student of standardizedStudents) {
      // Check if student_id exists (Parameterized query)
      const [existing] = await pool.execute(
        'SELECT id FROM students WHERE student_id = ?',
        [student.student_id]
      );

      if (existing.length > 0) {
        // Update existing (Parameterized query)
        await pool.execute(
          'UPDATE students SET name = ?, department = ?, marks = ?, email = ? WHERE student_id = ?',
          [student.name, student.department, student.marks, student.email, student.student_id]
        );
        updatedCount++;
      } else {
        // Insert new student (Parameterized query)
        await pool.execute(
          'INSERT INTO students (student_id, name, department, marks, email) VALUES (?, ?, ?, ?, ?)',
          [student.student_id, student.name, student.department, student.marks, student.email]
        );
        insertedCount++;
      }

      importedRecords.push(student);
    }

    return res.status(200).json({
      success: true,
      message: `Legacy data imported successfully using Adaptor Pattern. Created: ${insertedCount}, Updated: ${updatedCount}.`,
      patternUsed: 'Adaptor Pattern (LegacyJSONAdapter)',
      stats: {
        totalRecords: standardizedStudents.length,
        insertedCount,
        updatedCount
      },
      data: importedRecords
    });
  } catch (error) {
    console.error('Legacy Data Import Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Failed to import legacy JSON student data.',
      error: error.message
    });
  }
};

module.exports = {
  importLegacyStudents
};
