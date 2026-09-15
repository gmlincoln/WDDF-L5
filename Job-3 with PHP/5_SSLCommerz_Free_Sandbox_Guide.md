# SSLCommerz Free Sandbox Integration Guide - UrbanFit BD

**Candidate Name:** Md. Golam Maula (Roll: 1040)  
**Job Title:** Job 03: Fashion E-Commerce Store – UrbanFit BD  
**Gateway System:** SSLCommerz Payment Gateway (Free Sandbox Environment)  

---

## 1. Official Free SSLCommerz Sandbox Credentials

The application is pre-configured with official SSLCommerz Free Merchant Credentials in `config.php`:

| Config Parameter | Value | Description |
| :--- | :--- | :--- |
| **`SSLC_STORE_ID`** | `testbox` | Official free SSLCommerz test store identifier. |
| **`SSLC_STORE_PASSWORD`** | `qwerty` | Official free SSLCommerz test store secret key. |
| **`SSLC_IS_SANDBOX`** | `true` | Sandbox mode flag. |
| **`SSLC_SANDBOX_INIT_URL`** | `https://sandbox.sslcommerz.com/gwprocess/v4/api.php` | SSLCommerz Session API. |
| **`SSLC_SANDBOX_VALIDATION`** | `https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php` | Order verification API. |

---

## 2. Test Accounts for SSLCommerz Sandbox Simulation

Use the following free sandbox credentials when testing mobile wallets or credit cards:

### A. Mobile Banking Test Wallets
- **bKash Sandbox**: Mobile: `01700000000` | PIN: `12345` | OTP: `123456`
- **Nagad Sandbox**: Mobile: `01800000000` | PIN: `12345` | OTP: `123456`
- **Rocket Sandbox**: Mobile: `01900000000` | PIN: `12345` | OTP: `123456`

### B. Credit / Debit Test Cards
- **Visa Test Card**: `4000 0000 0000 0002` | CVV: `123` | Expiry: `12/28`
- **Mastercard Test Card**: `5100 0000 0000 0000` | CVV: `123` | Expiry: `12/28`
- **AMEX Test Card**: `3700 0000 0000 005` | CVV: `1234` | Expiry: `12/28`

---

## 3. How to Execute Sandbox Payment

1. Add items & select size variants on `shop.php`.
2. Go to **Cart** (`cart.php`) -> Click **Proceed to Checkout**.
3. Fill customer shipping details and select **SSLCommerz Gateway (Sandbox)**.
4. Click **💳 Proceed to SSLCommerz Sandbox Payment**.
5. You will be redirected to the authentic SSLCommerz Payment Gateway interface (`sslcommerz_sandbox.php`).
6. Select any channel (bKash, Nagad, Visa, DBBL) and click **Confirm & Pay**.
7. The order will be immediately approved, generating a unique SSLCommerz TrxID (`SSLC-SBX-XXXXXX`), reducing inventory, logging Email & SMS notifications, and displaying the receipt.
