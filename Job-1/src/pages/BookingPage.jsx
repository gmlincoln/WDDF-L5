import React, { useState } from 'react';
import { Calendar, Users, Phone, User, CheckCircle2 } from 'lucide-react';

export default function BookingPage() {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    date: '',
    time: '19:00',
    guests: '2',
    notes: ''
  });

  const [errors, setErrors] = useState({});
  const [submitted, setSubmitted] = useState(false);

  const validate = () => {
    const newErrors = {};

    // 1. Name validation (required)
    if (!formData.name.trim()) {
      newErrors.name = 'Full Name is required';
    }

    // 2. Phone validation (11 digits required)
    const phoneDigitsOnly = formData.phone.trim();
    if (!phoneDigitsOnly) {
      newErrors.phone = 'Phone number is required';
    } else if (!/^\d{11}$/.test(phoneDigitsOnly)) {
      newErrors.phone = 'Phone number must be exactly 11 digits (e.g. 01712345678)';
    }

    // 3. Date validation (future date only)
    if (!formData.date) {
      newErrors.date = 'Reservation date is required';
    } else {
      const selectedDate = new Date(formData.date);
      const today = new Date();
      // Reset hours for accurate date-only comparison
      today.setHours(0, 0, 0, 0);
      selectedDate.setHours(0, 0, 0, 0);

      if (selectedDate <= today) {
        newErrors.date = 'Date must be a future date (starting from tomorrow)';
      }
    }

    // 4. Guests validation (1-12)
    const guestsNum = parseInt(formData.guests, 10);
    if (!formData.guests || isNaN(guestsNum)) {
      newErrors.guests = 'Number of guests is required';
    } else if (guestsNum < 1 || guestsNum > 12) {
      newErrors.guests = 'Guests must be between 1 and 12';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    // Clear error on change
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: '' }));
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      setSubmitted(true);
    }
  };

  // Get tomorrow's date formatted as YYYY-MM-DD for min attribute
  const tomorrowStr = new Date(Date.now() + 86400000).toISOString().split('T')[0];

  return (
    <div className="container" style={{ paddingTop: '2.5rem' }}>
      <div className="form-container">
        <div style={{ textAlign: 'center', marginBottom: '1.5rem' }}>
          <div
            style={{
              width: '50px',
              height: '50px',
              borderRadius: '50%',
              background: 'var(--primary-light)',
              color: 'var(--primary)',
              display: 'inline-flex',
              alignItems: 'center',
              justifyContent: 'center',
              marginBottom: '0.75rem'
            }}
          >
            <Calendar size={26} />
          </div>
          <h1 className="form-title">Reserve a Table</h1>
          <p className="form-subtitle">
            Book your dining spot at Spice Garden. Please fill in all required details.
          </p>
        </div>

        {submitted ? (
          <div className="alert-success">
            <CheckCircle2 size={48} style={{ color: '#10b981', margin: '0 auto 1rem' }} />
            <h3 style={{ fontSize: '1.4rem', color: '#065f46', marginBottom: '0.5rem' }}>
              Reservation Confirmed!
            </h3>
            <p style={{ fontSize: '0.95rem', color: '#047857', marginBottom: '1.5rem' }}>
              Thank you, <strong>{formData.name}</strong>! Your table for <strong>{formData.guests} guests</strong> on{' '}
              <strong>{formData.date}</strong> at <strong>{formData.time}</strong> has been successfully booked.
            </p>
            <p style={{ fontSize: '0.85rem', color: '#059669' }}>
              We have noted your phone number ({formData.phone}). See you soon!
            </p>
            <button
              className="btn btn-primary"
              style={{ marginTop: '1.5rem' }}
              onClick={() => {
                setSubmitted(false);
                setFormData({
                  name: '',
                  phone: '',
                  date: '',
                  time: '19:00',
                  guests: '2',
                  notes: ''
                });
              }}
            >
              Book Another Table
            </button>
          </div>
        ) : (
          <form onSubmit={handleSubmit} noValidate>
            <div className="form-grid">
              {/* Full Name */}
              <div className="form-group full-width">
                <label className="form-label" htmlFor="name">
                  Full Name <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <div style={{ position: 'relative' }}>
                  <input
                    type="text"
                    id="name"
                    name="name"
                    className={`form-input ${errors.name ? 'error' : ''}`}
                    placeholder="Enter your full name"
                    value={formData.name}
                    onChange={handleChange}
                  />
                </div>
                {errors.name && <span className="error-msg">{errors.name}</span>}
              </div>

              {/* Phone (11 digits required) */}
              <div className="form-group">
                <label className="form-label" htmlFor="phone">
                  Phone Number (11 digits) <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  className={`form-input ${errors.phone ? 'error' : ''}`}
                  placeholder="e.g. 01712345678"
                  value={formData.phone}
                  onChange={handleChange}
                />
                {errors.phone && <span className="error-msg">{errors.phone}</span>}
              </div>

              {/* Number of Guests (1-12) */}
              <div className="form-group">
                <label className="form-label" htmlFor="guests">
                  Number of Guests (1-12) <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <select
                  id="guests"
                  name="guests"
                  className={`form-select ${errors.guests ? 'error' : ''}`}
                  value={formData.guests}
                  onChange={handleChange}
                >
                  {[...Array(12)].map((_, i) => (
                    <option key={i + 1} value={i + 1}>
                      {i + 1} {i === 0 ? 'Guest' : 'Guests'}
                    </option>
                  ))}
                </select>
                {errors.guests && <span className="error-msg">{errors.guests}</span>}
              </div>

              {/* Date (Future date only) */}
              <div className="form-group">
                <label className="form-label" htmlFor="date">
                  Reservation Date (Future Date Only) <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <input
                  type="date"
                  id="date"
                  name="date"
                  min={tomorrowStr}
                  className={`form-input ${errors.date ? 'error' : ''}`}
                  value={formData.date}
                  onChange={handleChange}
                />
                {errors.date && <span className="error-msg">{errors.date}</span>}
              </div>

              {/* Time */}
              <div className="form-group">
                <label className="form-label" htmlFor="time">
                  Time Slot
                </label>
                <select
                  id="time"
                  name="time"
                  className="form-select"
                  value={formData.time}
                  onChange={handleChange}
                >
                  <option value="12:00">12:00 PM (Lunch)</option>
                  <option value="13:30">01:30 PM (Lunch)</option>
                  <option value="18:30">06:30 PM (Dinner)</option>
                  <option value="19:00">07:00 PM (Dinner)</option>
                  <option value="20:30">08:30 PM (Dinner)</option>
                </select>
              </div>

              {/* Special Requests */}
              <div className="form-group full-width">
                <label className="form-label" htmlFor="notes">
                  Special Requests / Dietary Notes (Optional)
                </label>
                <textarea
                  id="notes"
                  name="notes"
                  rows="3"
                  className="form-textarea"
                  placeholder="Any preferences e.g. high chair, window seat, birthday setup..."
                  value={formData.notes}
                  onChange={handleChange}
                ></textarea>
              </div>

              {/* Submit Button */}
              <div className="form-group full-width" style={{ marginTop: '1rem' }}>
                <button type="submit" className="btn btn-primary" style={{ width: '100%', justifyContent: 'center' }}>
                  <Calendar size={18} />
                  Confirm Table Booking
                </button>
              </div>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}
