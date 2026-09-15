# Coding Plan - Student Management Backend API with Roles (Job 02)

## 1. Project Overview & Target Audience
- **Purpose**: A secure RESTful API built for a training institute to manage students, courses, enrollments, and legacy data imports with Role-Based Access Control (RBAC).
- **Target Audience**:
  - **Admin**: System administrators responsible for full CRUD management of students and courses.
  - **Staff**: Institute staff members with read-only access to students and courses, and ability to manage enrollments.

## 2. Functional Requirements
1. **User Authentication & Authorization**:
   - Register and Login endpoints returning JWT bearer tokens.
   - Role-Based Access Control (`admin` vs `staff`).
2. **Student Management (CRUD)**:
   - `GET /students`, `GET /students/:id` (Admin & Staff)
   - `POST /students`, `PUT /students/:id`, `DELETE /students/:id` (Admin only; Staff blocked with `403 Forbidden`)
3. **Course Management (CRUD)**:
   - `GET /courses`, `GET /courses/:id` (Admin & Staff)
   - `POST /courses`, `PUT /courses/:id`, `DELETE /courses/:id` (Admin only; Staff blocked with `403 Forbidden`)
4. **Enrollment Management**:
   - Enforce **Duplicate Enrollment Prevention** (reject enrolling same student in same course twice).
   - Enforce **Course Capacity Validation** (reject enrollment when course enrollment count meets or exceeds `capacity`).
   - `GET /enrollments` (Admin & Staff)
5. **Legacy JSON Import**:
   - `POST /import-legacy` endpoint implementing Adaptor pattern to import legacy JSON files into standard MySQL database records.

## 3. Design Patterns Implementation

### A. Factory Pattern (`DatabaseFactory.js`)
- **Purpose**: Encapsulates database connection creation logic.
- **Implementation**: `DatabaseFactory.createConnection('mysql', config)` initializes the database connection and returns the managed connection pool, decoupling application startup from specific DB driver setup.

### B. Singleton Pattern (`DatabaseSingleton.js`)
- **Purpose**: Guarantees a single database connection pool instance exists throughout the node process.
- **Implementation**: Uses JavaScript class singleton instance wrapping `mysql2.createPool()`. Prevents connection leaks and ensures optimal connection management.

### C. Adaptor Pattern (`LegacyJSONAdapter.js`)
- **Purpose**: Translates legacy JSON fields into standard database entities.
- **Implementation**: Maps legacy fields (`student_code`, `full_name`, `dept`, `score`, `contact_email`) to target database columns (`student_id`, `name`, `department`, `marks`, `email`) before executing DB queries.

## 4. API Endpoint & RBAC Permission Matrix

| Method | Endpoint | Description | Access Role | Security / Rules |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Register new user account | Public | Role selection (`admin`/`staff`) |
| `POST` | `/api/login` | Authenticate and obtain JWT | Public | Returns signed JWT token |
| `GET` | `/api/students` | List all students | Admin & Staff | XSS sanitized search query `?q=` |
| `GET` | `/api/students/:id` | Get student details | Admin & Staff | Parameterized SQL query |
| `POST` | `/api/students` | Create student record | Admin Only | Input XSS sanitization, 403 for Staff |
| `PUT` | `/api/students/:id` | Update student record | Admin Only | Parameterized SQL, 403 for Staff |
| `DELETE` | `/api/students/:id` | Delete student record | Admin Only | Parameterized SQL, 403 for Staff |
| `GET` | `/api/courses` | List all courses | Admin & Staff | Parameterized SQL query |
| `POST` | `/api/courses` | Create course record | Admin Only | Set capacity & fee, 403 for Staff |
| `PUT` | `/api/courses/:id` | Update course record | Admin Only | Parameterized SQL, 403 for Staff |
| `DELETE` | `/api/courses/:id` | Delete course record | Admin Only | Parameterized SQL, 403 for Staff |
| `POST` | `/api/enrollments` | Enroll student in course | Admin & Staff | Duplicate check & Capacity check |
| `GET` | `/api/enrollments` | List enrollments | Admin & Staff | JOIN students and courses |
| `POST` | `/api/import-legacy` | Bulk import legacy JSON | Admin & Staff | Uses `LegacyJSONAdapter` pattern |

## 5. Security Integration Summary
1. **Authentication / Authorization**: JWT bearer tokens verified on protected routes; `requireRole(['admin'])` middleware blocks unauthorized staff mutations.
2. **SQL Injection Prevention**: All queries execute using `mysql2` parameterized prepared statements (`pool.execute(sql, [params])`). No raw SQL interpolation from user input.
3. **XSS Prevention**: `xssSanitizer` middleware automatically strips malicious scripts and HTML tags from request body, query, and parameter data.
