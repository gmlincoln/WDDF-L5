# CODING PLAN - Spice Garden Restaurant Frontend

**Candidate Name:** Md. Golam Maula (ID: 1040)  
**Job Sheet:** Job 01 - Restaurant Website Frontend - Spice Garden Restaurant  
**Framework:** React (via Vite)  

---

## 1. Purpose & Target Audience
- **Purpose:** Promote Spice Garden Restaurant and provide an interactive online table booking & menu browsing experience.
- **Target Audience:** Local customers looking to explore authentic dining options in Dhaka (Parjatan Bhaban, Agargaon) and reserve tables conveniently. All prices are listed in Bangladeshi Taka (৳).

## 2. Page Structure (4 Pages)
1. **Home (`/` or Home view):** Restaurant introduction, hero section, cuisine highlights, quick navigation CTA buttons to Menu and Booking pages.
2. **Menu (`/menu` or Menu view):** Displays 8 menu items loaded from `Sample Menu Data.json` with real-time interactive search, category filter, and Veg/Non-Veg toggle working together. Displays "Unavailable" badge on `available: false` items.
3. **Booking (`/booking` or Booking view):** Table reservation form with robust client-side validation for Name (required), Phone (11 digits), Date (future dates only), and Guests (1–12).
4. **Contact (`/contact` or Contact view):** Restaurant location, phone, email, opening hours, and an interactive message form.

---

## 3. Component Architecture & Design Pattern
- **Component Pattern:** Modular, component-driven React architecture.
- **Components:**
  - `Navbar`: Responsive top navigation bar with page links and mobile hamburger menu.
  - `Footer`: Footnote section with operating hours, social links, and contact info.
  - `MenuCard`: Card component presenting dish image, price, veg/non-veg status, and "Unavailable" badge.
  - `FilterBar`: Category pills, search box input, and Veg/Non-Veg toggle buttons.
  - `BookingForm`: Reservation form with dynamic error state management and validation.
  - `ContactForm`: Customer inquiry form with feedback states.

---

## 4. Project Folder Layout
```text
Job-1/
├── Sample Menu Data.json
├── CODING_PLAN.md
├── TESTING_CHECKLIST.md
├── package.json
├── vite.config.js
├── index.html
├── public/
│   └── Sample Menu Data.json
└── src/
    ├── main.jsx
    ├── App.jsx
    ├── index.css
    ├── data/
    │   └── Sample Menu Data.json
    ├── components/
    │   ├── Navbar.jsx
    │   ├── Footer.jsx
    │   ├── MenuCard.jsx
    │   └── FilterBar.jsx
    └── pages/
        ├── HomePage.jsx
        ├── MenuPage.jsx
        ├── BookingPage.jsx
        └── ContactPage.jsx
```

---

## 5. Development & Execution Instructions
1. Run `npm install` to install React and Vite dependencies.
2. Run `npm run dev` to launch the local development server (`http://localhost:3000`).
3. Run `npm run build` to verify production bundle generation without console/build errors.
