# Postman Test Case Execution Guide (Job 02)

This document maps all test requirements from the client specification sheet to their corresponding Postman test requests.

---

## Summary Matrix of Required Test Cases

| Test Case # | Description | HTTP Method | Endpoint | Authorization | Expected Status Code | Verification Result |
| :---: | :--- | :---: | :--- | :--- | :---: | :---: |
| **TC-01** | User Registration & Login | `POST` | `/api/login` | None (Public) | `200 OK` | Returns JWT bearer token |
| **TC-02** | Admin Student & Course CRUD | `POST` | `/api/students` | Admin Bearer Token | `201 Created` | Admin write permitted |
| **TC-03** | Staff Write/Delete Block | `DELETE` | `/api/students/STU-001` | Staff Bearer Token | `403 Forbidden` | Staff write/delete blocked |
| **TC-04** | Duplicate Enrollment Rejection | `POST` | `/api/enrollments` | Bearer Token | `400 Bad Request` | Returns duplicate error msg |
| **TC-05** | Full Course Capacity Rejection | `POST` | `/api/enrollments` | Bearer Token | `400 Bad Request` | Returns capacity full error |
| **TC-06** | Legacy JSON Data Import | `POST` | `/api/import-legacy` | Bearer Token | `200 OK` | Adaptor transforms fields |

---

## Detailed Test Case Steps

### Test Case 1: User Login & Token Generation
- **Endpoint**: `POST http://localhost:3000/api/login`
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "username": "admin",
    "password": "admin123"
  }
  ```
- **Expected Status**: `200 OK`
- **Expected Payload**:
  ```json
  {
    "success": true,
    "message": "Login successful.",
    "token": "eyJhbGciOiJIUzI1Ni...",
    "user": { "id": 1, "username": "admin", "role": "admin" }
  }
  ```

---

### Test Case 2: Admin CRUD Permission Check
- **Endpoint**: `POST http://localhost:3000/api/students`
- **Headers**: `Authorization: Bearer <ADMIN_JWT_TOKEN>`, `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "student_id": "STU-100",
    "name": "Mahfuzur Rahman",
    "department": "Computer Science",
    "marks": 91.0,
    "email": "mahfuz@institute.edu"
  }
  ```
- **Expected Status**: `201 Created`

---

### Test Case 3: Staff Role Permission Enforcement (Write/Delete Restricted)
- **Endpoint**: `DELETE http://localhost:3000/api/students/STU-100`
- **Headers**: `Authorization: Bearer <STAFF_JWT_TOKEN>`
- **Expected Status**: `403 Forbidden`
- **Expected Response**:
  ```json
  {
    "success": false,
    "message": "Forbidden. Action restricted to roles: [admin]. Current role: 'staff'"
  }
  ```

---

### Test Case 4: Business Logic Test - Duplicate Enrollment Prevention
- **Step 1**: Enroll student `STU-001` in course `CRS-103`.
  - **Status**: `201 Created`
- **Step 2**: Re-send the exact same enrollment request (`STU-001` in `CRS-103`).
- **Expected Status**: `400 Bad Request`
- **Expected Response**:
  ```json
  {
    "success": false,
    "message": "Duplicate enrollment rejected. Student is already enrolled in this course."
  }
  ```

---

### Test Case 5: Business Logic Test - Full Course Capacity Rejection
- **Setup**: Create course `CRS-FULL-101` with `capacity: 1`.
- **Step 1**: Enroll `STU-001` in `CRS-FULL-101`. (Capacity reached: 1/1) -> `201 Created`.
- **Step 2**: Attempt to enroll second student `STU-002` in `CRS-FULL-101`.
- **Expected Status**: `400 Bad Request`
- **Expected Response**:
  ```json
  {
    "success": false,
    "message": "Full course enrollment rejected. Course 'Limited Capacity Seminar' capacity (1) has been reached."
  }
  ```

---

### Test Case 6: Legacy Data Import via Adaptor Pattern
- **Endpoint**: `POST http://localhost:3000/api/import-legacy`
- **Headers**: `Authorization: Bearer <JWT_TOKEN>`, `Content-Type: application/json`
- **Request Body** (Legacy JSON format):
  ```json
  [
    {
      "student_code": "STU-LEG-101",
      "full_name": "Tariqul Islam",
      "dept": "Software Engineering",
      "score": 91.5,
      "contact_email": "tariqul.islam@legacy.edu"
    }
  ]
  ```
- **Expected Status**: `200 OK`
- **Expected Response**:
  ```json
  {
    "success": true,
    "message": "Legacy data imported successfully using Adaptor Pattern. Created: 1, Updated: 0.",
    "patternUsed": "Adaptor Pattern (LegacyJSONAdapter)"
  }
  ```
