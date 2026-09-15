# Test Report and Checklist - UrbanFit BD

**Candidate Name:** Md. Golam Maula  
**Assessment:** OU-WDDF-03-L5-V1: Develop E-Commerce Website using E-Commerce Platform  
**Job 03:** Fashion E-Commerce Store – UrbanFit BD  
**Date:** September 12, 2026  

---

## 1. Executive Summary & Verification Overview
The WooCommerce store for **UrbanFit BD** has been fully installed, configured, populated with 8 products, integrated with shipping and payment gateways, and tested end-to-end against all job sheet requirements.

---

## 2. Product Upload Test Results

| Product ID | Product Name | Category | Price (BDT) | SKU | Stock | Sizes | Out-of-Stock Block |
|:---|:---|:---|:---|:---|:---|:---|:---|
| 1 | Urban Slim Fit Cotton Shirt | Men's Fashion | 1,450 | UB-M-SH-001 | 15 | S, M, L, XL | N/A (In Stock) |
| 2 | Classic Denim Jacket | Men's Fashion | 2,850 | UB-M-JK-002 | 8 | M, L, XL | N/A (In Stock) |
| 3 | Elegance Floral Summer Dress | Women's Fashion | 2,200 | UB-W-DR-003 | 12 | S, M, L | N/A (In Stock) |
| 4 | High-Waist Tailored Trousers | Women's Fashion | 1,850 | UB-W-TR-004 | 10 | S, M, L, XL | N/A (In Stock) |
| 5 | Minimalist Leather Crossbody Bag | Accessories | 1,950 | UB-A-BG-005 | 6 | One Size | N/A (In Stock) |
| 6 | Urban Polarized Sunglasses | Accessories | 1,200 | UB-A-SG-006 | 20 | One Size | N/A (In Stock) |
| 7 | Chic Linen Kurti Top | Women's Fashion | 1,600 | UB-W-KT-007 | 14 | S, M, L, XL | N/A (In Stock) |
| 8 | Limited Edition Silk Scarf | Accessories | 950 | UB-A-SC-008 | **0** | One Size | **SUCCESSFULLY BLOCKED** |

---

## 3. Cart & Payment Path Test Execution

### Path A: Mobile Banking Checkout Flow
- **Product Selected:** Urban Slim Fit Cotton Shirt (Size: M, Price: 1,450 BDT)
- **Shipping Method:** EMS Local Delivery (Flat Rate: 100 BDT)
- **Payment Method Selected:** Mobile Banking (bKash / Nagad)
- **Input Fields Provided:** Mobile Number: `01711223344`, Transaction ID: `TRX88776655`
- **Total Amount Paid:** 1,550 BDT
- **Order Status:** Processing
- **Email Receipt Status:** Dispatched automatically
- **SMS Log Result:** `SMS logged to sms_log.txt successfully`
- **Result:** **PASSED**

### Path B: Cash on Delivery (COD) Checkout Flow
- **Product Selected:** Elegance Floral Summer Dress (Size: L, Price: 2,200 BDT)
- **Shipping Method:** EMS Local Delivery (Flat Rate: 100 BDT)
- **Payment Method Selected:** Cash on Delivery (COD)
- **Total Amount Payable:** 2,300 BDT
- **Order Status:** Processing / Pending Payment
- **Email Receipt Status:** Dispatched automatically
- **SMS Log Result:** `SMS logged to sms_log.txt successfully`
- **Result:** **PASSED**

---

## 4. Security & Administration Audit
1. **HTTPS/SSL Verification:** Checkout pages enforce HTTPS protocol with SSL padlock header.
2. **Form Input Validation:** Checkout form rejects empty required fields (Name, Phone, Address).
3. **Admin Panel Security:** Admin credentials secured with strong password policy and file system permissions set.

---

## 5. Final Assessment Conclusion
All performance criteria for **Job 03: Fashion E-Commerce Store - UrbanFit BD** have been satisfied in full compliance with the specification sheet.
