# Cart Test Checklist - UrbanFit BD

**Store URL:** `http://localhost/urbanfit/`  
**Assessment Job:** Job 03 - Fashion E-Commerce Store (UrbanFit BD)  
**Candidate Name:** Md. Golam Maula  

---

## Client Specification Checklist

| # | Requirement | Validation Method | Result | Notes |
|---|---|---|---|---|
| 1 | Platform installed and configured | WooCommerce dashboard & URL accessible | PASS | WordPress + WooCommerce running at `http://localhost/urbanfit/` |
| 2 | 8 products uploaded with categories | Product catalog view (Men's, Women's, Accessories) | PASS | All 8 products uploaded from `Sample Product Data.json` |
| 3 | Size selection available before add to cart | Single Product Page variant dropdown | PASS | Sizes (S, M, L, XL / One Size) mandatory before add-to-cart |
| 4 | Out-of-stock product blocked from cart | Product #8 (*Limited Edition Silk Scarf*) cart test | PASS | Stock = 0, Add to Cart button disabled / blocked |
| 5 | Mobile banking payment path works end to end | Path A test order placing with TrxID | PASS | Order placed successfully via Mobile Banking |
| 6 | COD payment path works end to end | Path B test order placing | PASS | Order placed successfully via Cash on Delivery |
| 7 | EMS shipping rate shown at checkout | Shipping rate added at cart & checkout | PASS | EMS Local Shipping flat rate applied to order total |
| 8 | Email notification on order | WooCommerce order confirmation email trigger | PASS | Email receipt generated on order placement |
| 9 | SMS notification on order | `sms_log.txt` payload inspection | PASS | Automated SMS details logged to `sms_log.txt` |
| 10 | HTTPS/SSL on checkout pages | Checkout URL protocol & security headers | PASS | SSL / HTTPS enforced on checkout flow |
| 11 | Admin panel secured | Strong admin credentials & directory permissions | PASS | Admin credentials set, file permissions verified |

---

## Detailed Test Scenarios

### Scenario 1: Mandatory Size Selection Test
- **Action:** Open *Urban Slim Fit Cotton Shirt*. Select size "M" and click "Add to cart".
- **Expected Result:** Item added to cart with size attribute "M".
- **Actual Result:** Item added successfully with variant "Size: M".

### Scenario 2: Out of Stock Blocking Test
- **Action:** Open *Limited Edition Silk Scarf* (Stock: 0).
- **Expected Result:** "Out of stock" badge displayed, "Add to cart" button disabled or blocked on submission.
- **Actual Result:** Store prevents adding item with message: *"You cannot add 'Limited Edition Silk Scarf' to the cart because the product is out of stock."*

### Scenario 3: Path A - Mobile Banking Checkout
- **Action:** Select product -> Proceed to checkout -> Choose "Mobile Banking" -> Enter Sender No (`01700000000`) & TrxID (`TRX99887766`) -> Place Order.
- **Expected Result:** Order placed, Order Thank You page shown, Email sent, SMS logged.
- **Actual Result:** Order confirmed. SMS logged in `sms_log.txt`.

### Scenario 4: Path B - Cash on Delivery (COD) Checkout
- **Action:** Select product -> Proceed to checkout -> Choose "Cash on Delivery (COD)" -> Place Order.
- **Expected Result:** Order placed with status "Processing" or "Pending payment", Email sent, SMS logged.
- **Actual Result:** Order completed successfully. SMS log entry appended.
