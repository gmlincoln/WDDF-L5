# E-Commerce Planning Template - UrbanFit BD

## 1. Executive Summary & Purpose
**Store Name:** UrbanFit BD  
**Business Model:** B2C (Business-to-Consumer) Small Fashion Store  
**Target Market:** Online fashion shoppers in Bangladesh looking for Men's clothing, Women's fashion, and Accessories.  
**Core Objective:** Provide a seamless, secure, and user-friendly online shopping experience featuring 8 initial products with mandatory size selection, stock tracking, local EMS shipping, and dual payment options (Mobile Banking & Cash on Delivery).

---

## 2. Platform Selection & Justification
**Selected Platform:** WordPress with WooCommerce  
**Justification:**
1. **Cost Efficiency:** WooCommerce is 100% open-source and free, eliminating ongoing platform licensing fees for a small fashion business.
2. **Customization & Flexibility:** Provides extensive support for product variations (sizes S, M, L, XL), custom shipping rates (EMS), and local payment gateways (bKash, Nagad, COD).
3. **Speed of Deployment:** Pre-built commerce workflows allow complete setup, customization, product import, and end-to-end testing within the 1 hour 30 minute timeframe.

---

## 3. Site Structure & Navigation Architecture
```
[Home Page]
   │
   ├───► [Shop / Catalog]
   │        ├── Men's Fashion
   │        ├── Women's Fashion
   │        └── Accessories
   │
   ├───► [Product Detail Page] ──► (Mandatory Size Selection & Stock Check)
   │        └── Add to Cart
   │
   ├───► [Cart Page] ────────────► (EMS Shipping Calculation & Cart Total)
   │
   ├───► [Checkout Page (HTTPS/SSL)]
   │        ├── Path A: Mobile Banking (bKash/Nagad)
   │        └── Path B: Cash on Delivery (COD)
   │
   ├───► [Order Confirmation] ────► (Triggers Email & Simulated SMS Notification)
   │
   └───► [Contact Page]
```

---

## 4. Payment, Shipping & Notification Architecture

### 4.1 Payment Gateways
- **Mobile Banking (Path A):** Supports bKash / Nagad payment options. Requires customer to input sender mobile number and Transaction ID (TrxID) during checkout.
- **Cash on Delivery - COD (Path B):** Pay upon delivery at destination address.

### 4.2 Shipping Gateway
- **EMS (Express Mail Service - Local Delivery):** Configured as local shipping method with flat rate charge (e.g., 100 BDT) automatically calculated at cart and checkout.

### 4.3 Order Notification System
- **Email Notification:** Automated order receipt dispatched to customer email address upon order creation.
- **SMS Notification:** Automated SMS payload containing Order ID, Customer Phone, Total Amount, and Items dispatched and logged to system log (`sms_log.txt`).

---

## 5. Security & HTTPS/SSL Plan
- **SSL/HTTPS Enforcement:** Enforced across all Cart, Checkout, and Account pages via SSL certificate configuration and forced HTTPS redirect rules.
- **Admin Panel Security:** Secure admin credentials, restricted file permissions (`755` directories / `644` files), and input validation on checkout forms to prevent SQL injection and cross-site scripting (XSS).
- **Cart Validation:** Server-side block preventing zero-stock items from being added to cart or checked out.
