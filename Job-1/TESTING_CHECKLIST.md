# Client Specification Checklist & Testing Report

**Candidate Name:** Md. Golam Maula (ID: 1040)  
**Project:** Spice Garden Restaurant Website Frontend  

---

## Client Specification Checklist

| Item | Requirement | Status | Verification Notes |
|:---:|:---|:---:|:---|
| 1 | **4 pages present and linked via navigation** | [x] PASSED | Home, Menu, Booking, and Contact pages fully accessible via navbar. |
| 2 | **8 menu items display with category and price** | [x] PASSED | Items correctly loaded from `Sample Menu Data.json` showing category & price tags. |
| 3 | **Category filter works** | [x] PASSED | Filters menu items by Appetizer, Main Course, Dessert, Drinks, and Combo. |
| 4 | **Search filter works** | [x] PASSED | Real-time text search filters dishes by name (case-insensitive). |
| 5 | **Veg/non-veg toggle works** | [x] PASSED | Toggle correctly isolates Vegetarian (`is_veg: true`) vs Non-Vegetarian items. |
| 6 | **Filters work together (combined test)** | [x] PASSED | Combining category + search string + veg toggle produces accurate combined results. |
| 7 | **Grilled Chicken shows "Unavailable" badge** | [x] PASSED | Grilled Chicken has `available: false` and explicitly displays "Unavailable" badge. |
| 8 | **Booking form rejects empty fields** | [x] PASSED | Submitting empty form highlights required fields with error messages. |
| 9 | **Booking form rejects invalid phone (not 11 digits)** | [x] PASSED | Phone inputs not equal to 11 digits (e.g. 10 or 12 digits) are rejected with error. |
| 10 | **Booking form rejects past date** | [x] PASSED | Dates on or before today's date are rejected; only future dates are accepted. |
| 11 | **Site responsive on mobile view** | [x] PASSED | Flex/Grid CSS layout scales down seamlessly for mobile and desktop screens. |
| 12 | **Site runs without console errors** | [x] PASSED | React app renders cleanly with zero console warnings or errors. |

---

## Testing Execution Summary

1. **Rendering Test:** Verified on Chrome, Edge, and mobile viewports. Layout adjusts responsively.
2. **Form Validation Test:** Tested empty fields, 10-digit phone (`0171234567`), past date (`2020-01-01`), and valid input (`John Doe`, `01712345678`, future date, 4 guests). All validation logic behaves accurately.
3. **Business Logic Test:** Tested combined filters (Category: "Main Course" + Veg: "Veg Only" + Search: "Paneer"). Result correctly isolates Paneer Butter Masala.
4. **Integration Test:** Booking submission triggers a success feedback modal with details.
