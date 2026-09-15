# WDDF Level 5 Assessment Repository

**Candidate Name:** Md. Golam Maula  
**Candidate ID:** 1040  
**Program:** Web Design & Development Framework (WDDF) Level 5  

---

## 📌 Project Overview

This repository contains the complete assessment submission for **WDDF Level 5**, organized into distinct task modules (**Jobs**). The portfolio demonstrates comprehensive skills in modern full-stack web development, RESTful API architecture, software design patterns, database design, e-commerce platform implementation (both custom PHP and WordPress/WooCommerce), payment gateway integration (SSLCommerz Sandbox), and technical project proposal documentation.

---

## 📁 Repository Directory & File Structure

```text
1040_Md.Golam Maula/
├── README.md                                    # Root Documentation & Repository Overview
├── Job-1/                                       # Frontend Web Application (React + Vite)
│   ├── CODING_PLAN.md                           # Architecture & Development Roadmap
│   ├── Sample Menu Data.json                    # Sample Dataset for Food Menu
│   ├── TESTING_CHECKLIST.md                     # Quality Assurance & Testing Document
│   ├── index.html                               # HTML Template Entry Point
│   ├── package.json                             # Dependencies & Scripts Configuration
│   ├── package-lock.json                        # Dependency Lock File
│   ├── vite.config.js                           # Vite Bundler Configuration
│   ├── public/                                  # Static Assets & Public Files
│   └── src/                                     # React Source Code
│       ├── App.jsx                              # Root Component & Layout
│       ├── index.css                            # Global Styles & Design System
│       ├── main.jsx                             # Application Entry Point
│       ├── components/                          # UI Components (Navbar, Menu, Cart, etc.)
│       ├── data/                                # Local Data & Constants
│       └── pages/                               # Page Components (Home, Menu, Reservation, etc.)
│
├── Job-2/                                       # RESTful Backend API (Node.js + Express + MySQL)
│   ├── Coding_Plan.md                           # Backend Architecture & API Specifications
│   ├── ER_Diagram_Database_Schema.md            # ER Diagram & Schema Design Notes
│   ├── Postman_Collection.json                  # Postman API Test Suite
│   ├── Postman_Test_Cases.md                    # Detailed API Test Case Documentation
│   ├── Sample Student Data.json                 # Sample Dataset for Student Import
│   ├── Security_Checklist.md                    # Security Audit & Implementation Rules
│   ├── package.json                             # Node.js Dependencies & NPM Scripts
│   ├── package-lock.json                        # Package Lock File
│   ├── schema.sql                               # MySQL Database DDL & Initial Schema
│   ├── .env                                     # Environment Variables Configuration
│   ├── test/                                    # Automated Unit & Integration Tests
│   │   └── api.test.js                          # Automated API Test Suite
│   └── src/                                     # Backend Source Code
│       ├── server.js                            # Express Server Entry Point & Middleware Pipeline
│       ├── setupDb.js                           # Automated Database Setup & Seeding Script
│       ├── controllers/                         # Request Controllers (Auth, Student, Course, etc.)
│       │   ├── authController.js                # Authentication & JWT Management
│       │   ├── courseController.js              # Course CRUD Operations
│       │   ├── enrollmentController.js          # Student-Course Enrollment Logic
│       │   ├── importController.js              # Data Import Controller
│       │   └── studentController.js             # Student CRUD Operations
│       ├── middleware/                          # Custom Express Middleware
│       │   ├── authMiddleware.js                # JWT Verification & Role-Based Access (RBAC)
│       │   └── xssMiddleware.js                 # Cross-Site Scripting (XSS) Sanitization
│       └── patterns/                            # Software Design Patterns Implementation
│           ├── DatabaseFactory.js               # Factory Pattern for Query Execution
│           ├── DatabaseSingleton.js             # Singleton Pattern for DB Connections
│           └── LegacyJSONAdapter.js             # Adapter Pattern for Legacy JSON Import
│
├── Job-3 with PHP/                              # E-Commerce Platform (Custom PHP + MySQL)
│   ├── 1_E-Commerce_Planning_Template.md        # Architecture & Requirements Planning
│   ├── 2_Platform_Selection_Report.md           # Evaluation Report (PHP vs CMS Platform)
│   ├── 3_Cart_Test_Checklist.md                 # Shopping Cart QA Checklist
│   ├── 4_Test_Report_and_Checklist.md           # End-to-End System Test Report
│   ├── 5_SSLCommerz_Free_Sandbox_Guide.md       # Payment Gateway Setup & Testing Guide
│   ├── Sample Product Data.json                 # Sample Product Catalog Data
│   ├── config.php                               # Database Connection & Global Configuration
│   ├── schema.sql                               # Database Table Structures (Users, Products, Orders)
│   ├── seed.php                                 # Database Seeder Script
│   ├── index.php                                # Storefront Home Page
│   ├── shop.php                                 # Product Catalog & Search Page
│   ├── product.php                              # Product Details Page
│   ├── cart.php                                 # Shopping Cart Management
│   ├── checkout.php                             # Order Checkout & Payment Selector
│   ├── sslcommerz_api.php                       # SSLCommerz Payment Gateway Handler
│   ├── sslcommerz_sandbox.php                   # Built-in SSLCommerz Gateway Emulator
│   ├── order_success.php                        # Order Confirmation & Invoice View
│   ├── admin.php                                # Store Administration Dashboard
│   ├── contact.php                              # Customer Contact & Support Page
│   ├── header.php                               # Shared Header Partial
│   ├── footer.php                               # Shared Footer Partial
│   ├── test_suite.php                           # Automated PHP Unit/Integration Test Suite
│   ├── test_sslcommerz_call.php                 # Payment Gateway Integration Verification
│   ├── run_api_test.php                         # API Test Execution Utility
│   └── assets/                                  # CSS Stylesheets, Images, and Client JS
│
├── Job-3 with WooCommerce/                      # E-Commerce Platform (WordPress + WooCommerce)
│   └── urbanfit/                                # Full WordPress Site Workspace
│       ├── wp-config.php                        # WordPress Configuration
│       ├── activate_storefront_and_setup_home.php # Automated Theme & Homepage Setup Script
│       ├── configure_store.php                  # Store & Currency Configuration Script
│       ├── import_products.php                  # Automated WooCommerce Product Importer
│       ├── run_cart_test_suite.php              # Automated E2E Test Suite for WooCommerce
│       ├── setup_ecommerce_visuals.php          # Visual Styling & Banner Automation Script
│       ├── schema.sql                           # Database Schema Backup
│       ├── sslcommerz_sandbox.php               # Payment Gateway Testing Helper
│       ├── wp-content/                          # Themes (Storefront), Plugins (WooCommerce)
│       └── [WordPress Core Files]               # Standard WordPress Core Architecture
│
└── Job-4/                                       # Project Proposal & Business Documentation
    ├── UrbanFit_BD_Ecommerce_Project_Proposal.docx # Comprehensive Technical & Business Proposal (DOCX)
    └── UrbanFit_BD_Ecommerce_Project_Proposal.pdf  # Comprehensive Technical & Business Proposal (PDF)
```

---

## 🛠️ Modules Breakdown & Technical Summary

### 🍔 Job-1: Spice Garden Restaurant Website (Frontend)
- **Tech Stack:** React 18, Vite, Lucide React Icons, Modern Custom CSS.
- **Key Features:**
  - Interactive online food menu with category filtering and real-time search.
  - Dynamic shopping cart and food ordering workflow.
  - Table reservation system with input validation.
  - Customer feedback submission and contact interface.
  - Fully responsive design optimized for desktop and mobile viewports.
- **Key Documentation:** [CODING_PLAN.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-1/CODING_PLAN.md), [TESTING_CHECKLIST.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-1/TESTING_CHECKLIST.md).
- **Execution Instructions:**
  ```bash
  cd "Job-1"
  npm install
  npm run dev
  ```

---

### 🎓 Job-2: Student Management System (RESTful API Backend)
- **Tech Stack:** Node.js, Express.js, MySQL (`mysql2`), JWT (`jsonwebtoken`), `bcryptjs`, `xss`.
- **Key Features:**
  - User Authentication with hashed password storage (`bcryptjs`) and JWT access tokens.
  - Role-Based Access Control (RBAC) supporting **Admin**, **Teacher**, and **Student** roles.
  - Full CRUD operations for Students, Courses, and System Users.
  - Course Enrollment and Grade Assignment workflows.
  - Implementation of Software Design Patterns:
    - **Singleton Pattern:** [DatabaseSingleton.js](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/src/patterns/DatabaseSingleton.js) for database pool management.
    - **Factory Pattern:** [DatabaseFactory.js](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/src/patterns/DatabaseFactory.js) for abstracted query execution.
    - **Adapter Pattern:** [LegacyJSONAdapter.js](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/src/patterns/LegacyJSONAdapter.js) for transforming legacy JSON data into relational tables.
  - Security hardening against XSS attacks, SQL Injection, and unauthenticated requests.
  - Automated integration test suite (`api.test.js`) and Postman collection.
- **Key Documentation:** [Coding_Plan.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/Coding_Plan.md), [ER_Diagram_Database_Schema.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/ER_Diagram_Database_Schema.md), [Security_Checklist.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/Security_Checklist.md), [Postman_Test_Cases.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-2/Postman_Test_Cases.md).
- **Execution Instructions:**
  ```bash
  cd "Job-2"
  npm install
  # Configure database settings in .env
  npm run setup-db
  npm start
  npm test
  ```

---

### 🛒 Job-3: UrbanFit BD E-Commerce Platform

#### Option A: Custom PHP Implementation (`Job-3 with PHP/`)
- **Tech Stack:** PHP 8, MySQL, Custom CSS/JS, SSLCommerz Sandbox Integration.
- **Key Features:**
  - Product catalog browsing, search, and detail views.
  - Session-based Shopping Cart with dynamic tax, shipping, and total calculations.
  - Integrated checkout supporting **Cash on Delivery (COD)** and **SSLCommerz Sandbox Payment Gateway**.
  - Built-in SSLCommerz Payment Emulator ([sslcommerz_sandbox.php](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/sslcommerz_sandbox.php)) for complete payment lifecycle verification without external network dependency.
  - Admin management panel ([admin.php](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/admin.php)) for order status updates, product management, and inventory tracking.
  - Automated PHP test runner ([test_suite.php](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/test_suite.php)).
- **Key Documentation:** [1_E-Commerce_Planning_Template.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/1_E-Commerce_Planning_Template.md), [2_Platform_Selection_Report.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/2_Platform_Selection_Report.md), [3_Cart_Test_Checklist.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/3_Cart_Test_Checklist.md), [4_Test_Report_and_Checklist.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/4_Test_Report_and_Checklist.md), [5_SSLCommerz_Free_Sandbox_Guide.md](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20PHP/5_SSLCommerz_Free_Sandbox_Guide.md).

#### Option B: WooCommerce WordPress Implementation (`Job-3 with WooCommerce/`)
- **Tech Stack:** WordPress CMS, WooCommerce, Storefront Theme, Automated Setup Utilities.
- **Key Features:**
  - Automated platform setup and Storefront theme configuration via custom CLI scripts.
  - Programmatic product importer script ([import_products.php](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20WooCommerce/urbanfit/import_products.php)).
  - End-to-end shopping cart automated testing script ([run_cart_test_suite.php](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-3%20with%20WooCommerce/urbanfit/run_cart_test_suite.php)).

---

### 📄 Job-4: Project Proposal & Documentation
- **Deliverables:**
  - [UrbanFit_BD_Ecommerce_Project_Proposal.docx](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-4/UrbanFit_BD_Ecommerce_Project_Proposal.docx)
  - [UrbanFit_BD_Ecommerce_Project_Proposal.pdf](file:///d:/WDDF%20L5/Assessment/1040_Md.Golam%20Maula/Job-4/UrbanFit_BD_Ecommerce_Project_Proposal.pdf)
- **Content Summary:** A formal enterprise e-commerce proposal detailing project objectives, scope of work, technical architecture, cost breakdown, risk management plan, and milestone timeline for UrbanFit BD.

---

## ⚙️ System Requirements & Environment Setup

To run and verify the modules in this repository, ensure the following software tools are installed on your environment:

1. **Node.js Environment:**
   - Node.js `v18.0.0` or higher
   - NPM `v9.0.0` or higher
2. **PHP Environment:**
   - PHP `v8.0` or higher
   - MySQL Server `v8.0` / MariaDB `v10.4`
   - Web Server: Apache / Nginx or PHP Built-in Web Server (`php -S localhost:8000`)
3. **Database Management:**
   - phpMyAdmin or MySQL Workbench / HeidiSQL
4. **API Testing:**
   - Postman or cURL CLI

---

## 📋 Assessment Summary Table

| Module | Project Name | Main Technologies | Status |
| :--- | :--- | :--- | :---: |
| **Job-1** | Spice Garden Restaurant | React, Vite, CSS, Lucide Icons | ✅ Completed |
| **Job-2** | Student Management API | Node.js, Express, MySQL, JWT, Design Patterns | ✅ Completed |
| **Job-3 (PHP)** | UrbanFit BD E-Commerce | Native PHP, MySQL, SSLCommerz Sandbox | ✅ Completed |
| **Job-3 (WooCommerce)** | UrbanFit BD E-Commerce | WordPress, WooCommerce, Custom Automation | ✅ Completed |
| **Job-4** | E-Commerce Project Proposal | Word (DOCX), PDF Documentation | ✅ Completed |

---
*Created for WDDF Level 5 Assessment Submission by Candidate ID 1040 (Md. Golam Maula).*
