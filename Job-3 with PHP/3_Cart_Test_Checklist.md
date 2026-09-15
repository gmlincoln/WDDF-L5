# UrbanFit BD - Cart & Checkout Test Checklist

**Candidate Name:** Md. Golam Maula (Roll: 1040)  
**Job Title:** Job 03: Fashion E-Commerce Store – UrbanFit BD  
**Test Environment:** XAMPP Apache 2.4, PHP 8.2, MySQL 8.0, SSL Enabled  

---

## 1. Path A – Mobile Banking Payment Execution

| Step | Action Description | Expected Result | Status | Evidence / Log Output |
| :---: | :--- | :--- | :---: | :--- |
| **A1** | Select Product `Urban Slim Fit Cotton Shirt` and Size `L` | Size `L` selected and highlighted; price ৳1,450 shown. | **PASSED** | Size `L` active in state. |
| **A2** | Click **Add to Cart** | Product added to session cart; cart count updated to 1. | **PASSED** | Item in cart with Size `L`. |
| **A3** | Navigate to **Cart** and click **Proceed to Checkout** | SSL padlock shown, cart total ৳1,450 + EMS shipping ৳120 = ৳1,570. | **PASSED** | Total amount = ৳1,570. |
| **A4** | Fill details & select **Mobile Banking (Sandbox Mode)** | Select bKash/Nagad Sandbox option. Auto-fill TrxID `SBX-BK-892311`. | **PASSED** | Sandbox form validated successfully. |
| **A5** | Click **Place Order** | Order submitted via Mobile Banking Sandbox, stock reduced (15 -> 14). | **PASSED** | Order ID `#UB-1001` generated. |
| **A6** | Verify Email Notification Log | Confirmation email logged with recipient address, items, and total amount. | **PASSED** | Log Entry: `[EMAIL SENT] Order #UB-1001 confirmed for customer@domain.com`. |
| **A7** | Verify SMS Notification Log | SMS confirmation dispatch logged with mobile number, Order ID, and TrxID. | **PASSED** | Log Entry: `[SMS SENT] Order #UB-1001 confirmed (Sandbox). Sent to +8801711223344`. |

---

## 2. Path B – Cash on Delivery (COD) Payment Execution

| Step | Action Description | Expected Result | Status | Evidence / Log Output |
| :---: | :--- | :--- | :---: | :--- |
| **B1** | Select Product `Elegance Floral Summer Dress` and Size `M` | Size `M` selected; price ৳2,200 shown. | **PASSED** | Size `M` selected. |
| **B2** | Click **Add to Cart** | Added to cart; item count updated. | **PASSED** | Cart contains Dress (Size M). |
| **B3** | Proceed to **Checkout** | Cart total ৳2,200 + EMS shipping ৳120 = ৳2,320. | **PASSED** | Total amount = ৳2,320. |
| **B4** | Fill customer details & select **Cash on Delivery (COD)** | Select COD payment method. Form passes validation. | **PASSED** | Payment method set to COD. |
| **B5** | Click **Place Order** | Order processed, inventory updated (12 -> 11), redirected to order receipt. | **PASSED** | Order ID `#UB-1002` created. |
| **B6** | Verify Email Notification Log | Email confirmation generated with COD payment status "Pending Collection". | **PASSED** | Log Entry: `[EMAIL SENT] COD Order #UB-1002 logged for customer@domain.com`. |
| **B7** | Verify SMS Notification Log | SMS log records notification sent to customer phone with order reference. | **PASSED** | Log Entry: `[SMS SENT] Order #UB-1002 confirmed (COD). Sent to +8801899887766`. |

---

## 3. Out-of-Stock & Size Selection Validation Tests

| Test Case | Procedure | Expected Behavior | Outcome |
| :--- | :--- | :--- | :---: |
| **Size Selection Requirement** | Click **Add to Cart** on product without choosing size | System displays alert: "Please select a size before adding item to cart!" and prevents addition. | **PASSED** |
| **Out-of-Stock Blocking** | Navigate to `Limited Edition Silk Scarf` (Stock = 0) | **Add to Cart** button is disabled, styled gray, displaying label **Out of Stock**. | **PASSED** |
| **Empty Checkout Validation** | Click **Place Order** on checkout form with empty fields | Browser/Form validation triggers and rejects submission until required fields are filled. | **PASSED** |
| **SSL/HTTPS Verification** | Check Cart & Checkout headers / visual indicator | SSL Security Padlock active, HTTPS simulation banner displayed. | **PASSED** |
