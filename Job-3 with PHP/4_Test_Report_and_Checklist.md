# UrbanFit BD - Comprehensive Test Report & Client Checklist

**Candidate Name:** Md. Golam Maula (Roll ID: 1040)  
**Job Title:** Job 03: Fashion E-Commerce Store – UrbanFit BD  
**Assessment Standard:** Level 5 Web Design and Development  

---

## 1. Client Specification Verification Checklist

| # | Specification Item | Target Requirement | Status | Evidence / Verification Notes |
| :---: | :--- | :--- | :---: | :--- |
| **1** | **Platform Installation** | Platform installed and configured under local server. | **[X] PASSED** | Custom PHP 8.2 & MySQL application configured and live on XAMPP. |
| **2** | **8 Products Uploaded** | 8 items uploaded from `Sample Product Data.json` with categories & prices. | **[X] PASSED** | All 8 products loaded into database (Men's, Women's, Accessories). |
| **3** | **Size Selection** | Customers must select size before adding to cart. | **[X] PASSED** | Dynamic size picker (S, M, L, XL) enforced on `product.php`. |
| **4** | **Out-of-Stock Blocking** | Product with stock = 0 cannot be added to cart. | **[X] PASSED** | `Limited Edition Silk Scarf` (Stock 0) has disabled button & "Out of Stock" badge. |
| **5** | **Mobile Banking Path** | Mobile banking (bKash/Nagad/Rocket) end-to-end payment path works. | **[X] PASSED** | Order `#UB-1001` processed via bKash with transaction ID `BK89X23M1A`. |
| **6** | **COD Payment Path** | Cash on Delivery end-to-end payment path works. | **[X] PASSED** | Order `#UB-1002` processed via COD with pending cash collection flag. |
| **7** | **EMS Shipping Rate** | EMS (local shipping) rate displayed at checkout. | **[X] PASSED** | Flat rate EMS shipping of ৳120 automatically added to order total. |
| **8** | **Email Notification** | Email order confirmation generated upon checkout. | **[X] PASSED** | Automated email log generated and rendered on order success receipt. |
| **9** | **SMS Notification** | SMS notification logged/simulated with timestamp & order ID. | **[X] PASSED** | Real-time SMS logs recorded in database and admin log dashboard. |
| **10** | **HTTPS/SSL on Checkout** | HTTPS/SSL active on cart and checkout pages with padlock indicator. | **[X] PASSED** | SSL Padlock badge active; security headers enforced. |
| **11** | **Admin Panel Secured** | Admin panel secured with strong password and authorization checks. | **[X] PASSED** | Secured session login at `/admin.php` protecting inventory & order logs. |

---

## 2. Test Execution Findings Summary

- **Total Test Cases Executed:** 11 / 11
- **Passed:** 11
- **Failed:** 0
- **Overall Result:** 100% Compliance with Job 03 Specifications.

---

## 3. Final Sign-off

The web application for **UrbanFit BD** fully satisfies all functional requirements, security constraints, payment gateway integrations, and client brief guidelines set forth in Job 03.
