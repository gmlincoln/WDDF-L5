/**
 * Main Express Application Server Entry Point
 * Student Management Backend API with Roles, Design Patterns, and Security Integration
 */
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');

// Patterns & Middleware
const DatabaseFactory = require('./patterns/DatabaseFactory');
const { authenticateToken, requireRole } = require('./middleware/authMiddleware');
const xssSanitizer = require('./middleware/xssMiddleware');

// Controllers
const authController = require('./controllers/authController');
const studentController = require('./controllers/studentController');
const courseController = require('./controllers/courseController');
const enrollmentController = require('./controllers/enrollmentController');
const importController = require('./controllers/importController');

const app = express();
const PORT = process.env.PORT || 3000;

// Initialize Database Connection Pool via Factory (Singleton Pattern)
DatabaseFactory.createConnection('mysql');

// Standard Middleware
app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// XSS Prevention Middleware
app.use(xssSanitizer);

// Serve Web Dashboard Static UI
app.use(express.static(path.join(__dirname, '../public')));

// API Routes

// 1. Auth Routes (Public)
app.post('/api/register', authController.register);
app.post('/api/login', authController.login);

// 2. Student Routes
app.get('/api/students', authenticateToken, studentController.getAllStudents);
app.get('/api/students/:id', authenticateToken, studentController.getStudentById);
app.post('/api/students', authenticateToken, requireRole(['admin']), studentController.createStudent);
app.put('/api/students/:id', authenticateToken, requireRole(['admin']), studentController.updateStudent);
app.delete('/api/students/:id', authenticateToken, requireRole(['admin']), studentController.deleteStudent);

// 3. Course Routes
app.get('/api/courses', authenticateToken, courseController.getAllCourses);
app.get('/api/courses/:id', authenticateToken, courseController.getCourseById);
app.post('/api/courses', authenticateToken, requireRole(['admin']), courseController.createCourse);
app.put('/api/courses/:id', authenticateToken, requireRole(['admin']), courseController.updateCourse);
app.delete('/api/courses/:id', authenticateToken, requireRole(['admin']), courseController.deleteCourse);

// 4. Enrollment Routes (Duplicate Enrollment & Course Capacity checks)
app.post('/api/enrollments', authenticateToken, enrollmentController.createEnrollment);
app.get('/api/enrollments', authenticateToken, enrollmentController.getAllEnrollments);

// 5. Legacy JSON Import Route (Adaptor Pattern)
app.post('/api/import-legacy', authenticateToken, importController.importLegacyStudents);

// Health Check Endpoint
app.get('/api/health', (req, res) => {
  res.json({
    status: 'online',
    timestamp: new Date(),
    patternsLoaded: ['Singleton (Database)', 'Factory (DB Connection)', 'Adaptor (Legacy Import)'],
    securityFeatures: ['JWT Authentication', 'Role Authorization (Admin/Staff)', 'SQL Injection Prevention (Prepared Statements)', 'XSS Protection']
  });
});

// Global Error Handler
app.use((err, req, res, next) => {
  console.error('[Unhandled Express Error]:', err);
  res.status(500).json({
    success: false,
    message: 'An unexpected server error occurred.',
    error: err.message
  });
});

// Start Server with Automatic Port Fallback if occupied
const startServer = (portToTry) => {
  const serverInstance = app.listen(portToTry, () => {
    console.log(`=======================================================`);
    console.log(`🚀 Student Management API Server running on port ${portToTry}`);
    console.log(`🌐 Dashboard UI: http://localhost:${portToTry}`);
    console.log(`=======================================================`);
  });

  serverInstance.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
      console.warn(`\n⚠️ Port ${portToTry} is in use. Retrying on port ${portToTry + 1}...`);
      startServer(portToTry + 1);
    } else {
      console.error('Server startup error:', err);
    }
  });
};

if (require.main === module) {
  startServer(parseInt(PORT, 10) || 3000);
}

module.exports = app;


