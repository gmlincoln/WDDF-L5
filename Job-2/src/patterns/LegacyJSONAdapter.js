/**
 * Adaptor Pattern Implementation for Legacy JSON Student Data Import
 * Adapts legacy JSON data structures into the system's standard student record entity schema:
 * Standard Schema: { student_id, name, department, marks, email }
 */

class LegacyJSONAdapter {
  /**
   * Adapts a single legacy student object or array of objects into standard DB schema.
   * @param {Object|Array} legacyData - Legacy student JSON item(s)
   * @returns {Array} List of standardized student objects ready for DB insertion
   */
  static adapt(legacyData) {
    if (!legacyData) return [];
    
    const items = Array.isArray(legacyData) ? legacyData : [legacyData];
    
    return items.map((item, index) => {
      const student_id = String(
        item.student_id || item.student_code || item.studentId || item.code || `STU-LEGACY-${Date.now()}-${index + 1}`
      ).trim();

      const name = String(
        item.name || item.full_name || item.fullName || item.student_name || 'Unknown Student'
      ).trim();

      const department = String(
        item.department || item.dept || item.department_name || item.stream || 'General'
      ).trim();

      const rawMarks = item.marks !== undefined ? item.marks : (item.score !== undefined ? item.score : item.grade);
      const marks = parseFloat(rawMarks) || 0;

      const email = String(
        item.email || item.contact_email || item.email_address || `${student_id.toLowerCase()}@institute.edu`
      ).trim();

      return {
        student_id,
        name,
        department,
        marks,
        email
      };
    });
  }
}

module.exports = LegacyJSONAdapter;
