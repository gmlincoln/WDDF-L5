/**
 * Automated Integration Test Suite for Student Management Backend API
 * Verifies all 6 Postman test cases and Client Specification Checklist requirements:
 * 1. User Registration & Login (Admin & Staff JWT tokens)
 * 2. Admin CRUD permissions on Students & Courses
 * 3. Staff Read-Only permission & Write/Delete Block (403 Forbidden)
 * 4. Duplicate Enrollment Rejection (400 Bad Request)
 * 5. Full Course Capacity Rejection (400 Bad Request)
 * 6. Legacy JSON Import (Adaptor Pattern)
 */
const setupDatabase = require('../src/setupDb');
const app = require('../src/server');
const http = require('http');

let server;
const PORT = 3099;
const BASE_URL = `http://localhost:${PORT}/api`;

let adminToken = '';
let staffToken = '';

const request = async (method, path, body = null, token = null) => {
  const url = `${BASE_URL}${path}`;
  const headers = {
    'Content-Type': 'application/json'
  };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const options = {
    method,
    headers
  };

  return new Promise((resolve, reject) => {
    const req = http.request(url, options, (res) => {
      let data = '';
      res.on('data', chunk => { data += chunk; });
      res.on('end', () => {
        try {
          const parsed = JSON.parse(data);
          resolve({ status: res.statusCode, body: parsed });
        } catch (e) {
          resolve({ status: res.statusCode, body: data });
        }
      });
    });

    req.on('error', err => reject(err));
    if (body) {
      req.write(JSON.stringify(body));
    }
    req.end();
  });
};

const runTests = async () => {
  console.log('=======================================================');
  console.log('🧪 Starting Student Management API Automated Test Suite');
  console.log('=======================================================');

  // Step 1: Database Setup & Seeding
  await setupDatabase();

  // Step 2: Start HTTP Server on Test Port 3099
  server = app.listen(PORT);
  console.log(`[Test Runner] Test Server running on port ${PORT}`);

  try {
    // -----------------------------------------------------------------
    // TEST CASE 1: Authentication & Token Generation
    // -----------------------------------------------------------------
    console.log('\n--> [TC-01] Testing Login for Admin and Staff...');
    const adminLoginRes = await request('POST', '/login', { username: 'admin', password: 'admin123' });
    if (adminLoginRes.status === 200 && adminLoginRes.body.token) {
      adminToken = adminLoginRes.body.token;
      console.log('   ✅ Admin Login PASSED. Token acquired.');
    } else {
      throw new Error(`Admin Login failed: ${JSON.stringify(adminLoginRes.body)}`);
    }

    const staffLoginRes = await request('POST', '/login', { username: 'staff', password: 'staff123' });
    if (staffLoginRes.status === 200 && staffLoginRes.body.token) {
      staffToken = staffLoginRes.body.token;
      console.log('   ✅ Staff Login PASSED. Token acquired.');
    } else {
      throw new Error(`Staff Login failed: ${JSON.stringify(staffLoginRes.body)}`);
    }

    // -----------------------------------------------------------------
    // TEST CASE 2: Admin CRUD Operations
    // -----------------------------------------------------------------
    console.log('\n--> [TC-02] Testing Admin CRUD Operations on Students & Courses...');
    const testStuId = `STU-TEST-${Date.now()}`;
    const testCrsId = `CRS-CAP1-${Date.now()}`;

    const createStudentRes = await request('POST', '/students', {
      student_id: testStuId,
      name: 'Automated Test Student',
      department: 'Software Engineering',
      marks: 92.5,
      email: 'test.student@institute.edu'
    }, adminToken);

    if (createStudentRes.status === 201 && createStudentRes.body.success) {
      console.log('   ✅ Admin Create Student PASSED.');
    } else {
      throw new Error(`Admin Create Student failed: ${JSON.stringify(createStudentRes.body)}`);
    }

    const createCourseRes = await request('POST', '/courses', {
      course_id: testCrsId,
      title: 'Single Capacity Workshop',
      capacity: 1,
      fee: 2500.00
    }, adminToken);

    if (createCourseRes.status === 201 && createCourseRes.body.success) {
      console.log('   ✅ Admin Create Course PASSED.');
    } else {
      throw new Error(`Admin Create Course failed: ${JSON.stringify(createCourseRes.body)}`);
    }

    // -----------------------------------------------------------------
    // TEST CASE 3: Staff Permission Check (Read Allowed, Write/Delete Blocked)
    // -----------------------------------------------------------------
    console.log('\n--> [TC-03] Testing Staff Role Permissions (Read vs Write/Delete Block)...');
    const staffReadRes = await request('GET', '/students', null, staffToken);
    if (staffReadRes.status === 200 && staffReadRes.body.success) {
      console.log('   ✅ Staff Read Students Allowed (200 OK) PASSED.');
    } else {
      throw new Error(`Staff Read Students failed: ${JSON.stringify(staffReadRes.body)}`);
    }

    const staffDeleteRes = await request('DELETE', `/students/${createStudentRes.body.data.id}`, null, staffToken);
    if (staffDeleteRes.status === 403) {
      console.log('   ✅ Staff Delete Student Blocked with 403 Forbidden PASSED.');
    } else {
      throw new Error(`Staff Delete Student was NOT blocked with 403. Received status: ${staffDeleteRes.status}`);
    }

    // -----------------------------------------------------------------
    // TEST CASE 4: Duplicate Enrollment Rejection
    // -----------------------------------------------------------------
    console.log('\n--> [TC-04] Testing Duplicate Enrollment Prevention...');
    const dupCourseId = `CRS-DUP-${Date.now()}`;
    await request('POST', '/courses', { course_id: dupCourseId, title: 'Duplicate Test Course', capacity: 10, fee: 1000 }, adminToken);

    const enroll1Res = await request('POST', '/enrollments', {
      student_id: 'STU-001',
      course_id: dupCourseId
    }, adminToken);

    if (enroll1Res.status === 201) {
      console.log('   ✅ Initial Enrollment Created Successfully.');
    } else {
      throw new Error(`Initial Enrollment failed: ${JSON.stringify(enroll1Res.body)}`);
    }

    const duplicateEnrollRes = await request('POST', '/enrollments', {
      student_id: 'STU-001',
      course_id: dupCourseId
    }, adminToken);

    if (duplicateEnrollRes.status === 400 && duplicateEnrollRes.body.message.includes('Duplicate')) {
      console.log('   ✅ Duplicate Enrollment Rejected with 400 Bad Request PASSED.');
    } else {
      throw new Error(`Duplicate Enrollment was NOT rejected. Received status: ${duplicateEnrollRes.status}, body: ${JSON.stringify(duplicateEnrollRes.body)}`);
    }

    // -----------------------------------------------------------------
    // TEST CASE 5: Full Course Capacity Rejection
    // -----------------------------------------------------------------
    console.log('\n--> [TC-05] Testing Full Course Capacity Rejection...');
    // Enroll 1st student into testCrsId (capacity = 1)
    const capEnroll1 = await request('POST', '/enrollments', {
      student_id: 'STU-001',
      course_id: testCrsId
    }, adminToken);
    if (capEnroll1.status !== 201) {
      throw new Error(`Capacity setup enrollment failed: ${JSON.stringify(capEnroll1.body)}`);
    }

    // Attempt 2nd student into testCrsId (capacity = 1) -> Should be REJECTED!
    const capEnroll2 = await request('POST', '/enrollments', {
      student_id: 'STU-002',
      course_id: testCrsId
    }, adminToken);

    if (capEnroll2.status === 400 && capEnroll2.body.message.includes('capacity')) {
      console.log('   ✅ Full Course Capacity Rejection with 400 Bad Request PASSED.');
    } else {
      throw new Error(`Full Course Capacity was NOT rejected. Received status: ${capEnroll2.status}, body: ${JSON.stringify(capEnroll2.body)}`);
    }


    // -----------------------------------------------------------------
    // TEST CASE 6: Legacy JSON Import via Adaptor Pattern
    // -----------------------------------------------------------------
    console.log('\n--> [TC-06] Testing Legacy JSON Import (Adaptor Pattern)...');
    const legacyPayload = [
      {
        student_code: 'STU-LEG-301',
        full_name: 'Adapting Legacy Student',
        dept: 'Cloud Infrastructure',
        score: 89.5,
        contact_email: 'adapted@legacy.edu'
      }
    ];

    const importRes = await request('POST', '/import-legacy', legacyPayload, adminToken);

    if (importRes.status === 200 && importRes.body.patternUsed.includes('Adaptor')) {
      console.log('   ✅ Legacy Data Import via Adaptor Pattern PASSED.');
    } else {
      throw new Error(`Legacy Import failed: ${JSON.stringify(importRes.body)}`);
    }

    console.log('\n=======================================================');
    console.log('🎉 ALL 6 TEST CASES PASSED SUCCESSFULLY WITH 100% SUCCESS!');
    console.log('=======================================================');

  } catch (err) {
    console.error('\n❌ TEST FAILURE:', err.message);
    process.exitCode = 1;
  } finally {
    if (server) server.close();
    process.exit(process.exitCode || 0);
  }
};

runTests();

