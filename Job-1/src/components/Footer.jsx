import React from 'react';
import { Utensils, MapPin, Phone, Mail, Clock } from 'lucide-react';

export default function Footer({ setActivePage }) {
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-grid">
          <div>
            <div className="footer-brand">
              <Utensils size={24} color="#ff9800" />
              <span>Spice Garden</span>
            </div>
            <p style={{ fontSize: '0.9rem', lineHeight: '1.7', color: '#94a3b8' }}>
              Experience authentic flavors, hand-picked spices, and exceptional dining in a vibrant atmosphere.
            </p>
          </div>

          <div>
            <h4 style={{ color: 'white', marginBottom: '1rem' }}>Quick Links</h4>
            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              <li>
                <a
                  href="#home"
                  onClick={(e) => { e.preventDefault(); setActivePage('home'); window.scrollTo({ top: 0, behavior: 'smooth' }); }}
                  style={{ color: '#94a3b8', textDecoration: 'none' }}
                >
                  Home
                </a>
              </li>
              <li>
                <a
                  href="#menu"
                  onClick={(e) => { e.preventDefault(); setActivePage('menu'); window.scrollTo({ top: 0, behavior: 'smooth' }); }}
                  style={{ color: '#94a3b8', textDecoration: 'none' }}
                >
                  Menu
                </a>
              </li>
              <li>
                <a
                  href="#booking"
                  onClick={(e) => { e.preventDefault(); setActivePage('booking'); window.scrollTo({ top: 0, behavior: 'smooth' }); }}
                  style={{ color: '#94a3b8', textDecoration: 'none' }}
                >
                  Book Table
                </a>
              </li>
              <li>
                <a
                  href="#contact"
                  onClick={(e) => { e.preventDefault(); setActivePage('contact'); window.scrollTo({ top: 0, behavior: 'smooth' }); }}
                  style={{ color: '#94a3b8', textDecoration: 'none' }}
                >
                  Contact Us
                </a>
              </li>
            </ul>
          </div>

          <div>
            <h4 style={{ color: 'white', marginBottom: '1rem' }}>Opening Hours</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem', fontSize: '0.9rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <Clock size={16} color="#ff9800" />
                <span>Mon - Fri: 11:00 AM - 10:30 PM</span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <Clock size={16} color="#ff9800" />
                <span>Sat - Sun: 10:00 AM - 11:00 PM</span>
              </div>
            </div>
          </div>

          <div>
            <h4 style={{ color: 'white', marginBottom: '1rem' }}>Contact Info</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.6rem', fontSize: '0.9rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <MapPin size={16} color="#ff9800" />
                <span>Parjatan Bhaban, Agargaon, Dhaka - 1207</span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <Phone size={16} color="#ff9800" />
                <span>01712345678</span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <Mail size={16} color="#ff9800" />
                <span>info@spicegarden.com</span>
              </div>
            </div>
          </div>
        </div>

        <div className="footer-bottom">
          <p>&copy; {new Date().getFullYear()} Spice Garden Restaurant. All rights reserved. Assessment Level-5 Project.</p>
        </div>
      </div>
    </footer>
  );
}
