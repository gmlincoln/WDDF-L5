import React, { useState } from 'react';
import { MapPin, Phone, Mail, Clock, Send, CheckCircle } from 'lucide-react';

export default function ContactPage() {
  const [formData, setFormData] = useState({ name: '', email: '', message: '' });
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState({});

  const validate = () => {
    const errs = {};
    if (!formData.name.trim()) errs.name = 'Name is required';
    if (!formData.email.trim()) {
      errs.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      errs.email = 'Invalid email address';
    }
    if (!formData.message.trim()) errs.message = 'Message is required';
    setErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      setSubmitted(true);
    }
  };

  return (
    <div className="container" style={{ paddingTop: '2.5rem' }}>
      <div className="section-header">
        <span className="section-tag">Get in Touch</span>
        <h1 className="section-title">Contact Spice Garden</h1>
        <p style={{ color: '#64748b', marginTop: '0.5rem' }}>
          Have questions or want to leave feedback? We would love to hear from you.
        </p>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))', gap: '2.5rem', marginTop: '2rem' }}>
        {/* Contact Information */}
        <div style={{ background: 'var(--surface)', padding: '2.5rem', borderRadius: '24px', boxShadow: 'var(--shadow)', border: '1px solid var(--border)' }}>
          <h2 style={{ fontSize: '1.5rem', marginBottom: '1.5rem' }}>Visit & Reach Us</h2>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
            <div style={{ display: 'flex', gap: '1rem', alignItems: 'flex-start' }}>
              <div style={{ background: 'var(--primary-light)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '12px' }}>
                <MapPin size={22} />
              </div>
              <div>
                <strong>Restaurant Location</strong>
                <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.2rem' }}>
                  Parjatan Bhaban, Agargaon, Dhaka - 1207
                </p>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '1rem', alignItems: 'flex-start' }}>
              <div style={{ background: 'var(--primary-light)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '12px' }}>
                <Phone size={22} />
              </div>
              <div>
                <strong>Phone Reservations</strong>
                <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.2rem' }}>
                  01712345678 / +880 1712 345678
                </p>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '1rem', alignItems: 'flex-start' }}>
              <div style={{ background: 'var(--primary-light)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '12px' }}>
                <Mail size={22} />
              </div>
              <div>
                <strong>Email Address</strong>
                <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.2rem' }}>
                  info@spicegarden.com / contact@spicegarden.com
                </p>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '1rem', alignItems: 'flex-start' }}>
              <div style={{ background: 'var(--primary-light)', color: 'var(--primary)', padding: '0.75rem', borderRadius: '12px' }}>
                <Clock size={22} />
              </div>
              <div>
                <strong>Opening Hours</strong>
                <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.2rem' }}>
                  Monday – Friday: 11:00 AM – 10:30 PM<br />
                  Saturday – Sunday: 10:00 AM – 11:00 PM
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Contact Form */}
        <div style={{ background: 'var(--surface)', padding: '2.5rem', borderRadius: '24px', boxShadow: 'var(--shadow)', border: '1px solid var(--border)' }}>
          <h2 style={{ fontSize: '1.5rem', marginBottom: '1.5rem' }}>Send Us a Message</h2>

          {submitted ? (
            <div className="alert-success">
              <CheckCircle size={40} style={{ color: '#10b981', margin: '0 auto 0.75rem' }} />
              <h3 style={{ color: '#065f46' }}>Message Sent!</h3>
              <p style={{ color: '#047857', fontSize: '0.9rem', marginTop: '0.5rem' }}>
                Thank you, <strong>{formData.name}</strong>. We have received your inquiry and will reply to <strong>{formData.email}</strong> shortly.
              </p>
              <button
                className="btn btn-primary"
                style={{ marginTop: '1.25rem' }}
                onClick={() => {
                  setSubmitted(false);
                  setFormData({ name: '', email: '', message: '' });
                }}
              >
                Send Another Message
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} noValidate>
              <div className="form-group" style={{ marginBottom: '1.25rem' }}>
                <label className="form-label">
                  Your Name <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <input
                  type="text"
                  className={`form-input ${errors.name ? 'error' : ''}`}
                  placeholder="Enter your name"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                />
                {errors.name && <span className="error-msg">{errors.name}</span>}
              </div>

              <div className="form-group" style={{ marginBottom: '1.25rem' }}>
                <label className="form-label">
                  Your Email <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <input
                  type="email"
                  className={`form-input ${errors.email ? 'error' : ''}`}
                  placeholder="name@example.com"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                />
                {errors.email && <span className="error-msg">{errors.email}</span>}
              </div>

              <div className="form-group" style={{ marginBottom: '1.5rem' }}>
                <label className="form-label">
                  Message <span style={{ color: '#ef4444' }}>*</span>
                </label>
                <textarea
                  rows="4"
                  className={`form-textarea ${errors.message ? 'error' : ''}`}
                  placeholder="How can we help you?"
                  value={formData.message}
                  onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                ></textarea>
                {errors.message && <span className="error-msg">{errors.message}</span>}
              </div>

              <button type="submit" className="btn btn-primary" style={{ width: '100%', justifyContent: 'center' }}>
                <Send size={18} />
                Send Message
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
