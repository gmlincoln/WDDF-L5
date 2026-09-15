import React from 'react';
import { Utensils, Calendar, Award, Flame, Clock, HeartHandshake } from 'lucide-react';

export default function HomePage({ setActivePage }) {
  return (
    <div className="home-page">
      {/* Hero Section */}
      <section className="hero-section">
        <div className="hero-bg-overlay"></div>
        <div className="container hero-grid">
          <div>
            <span style={{ color: '#ff9800', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '1px', fontSize: '0.9rem' }}>
              Welcome to Spice Garden
            </span>
            <h1 className="hero-title">
              Taste the True Essence of <span>Authentic Spices</span>
            </h1>
            <p className="hero-subtitle">
              Savor fresh ingredients, aromatic blends, and hand-crafted culinary masterpieces crafted by master chefs. Browse our digital menu and reserve your table online today!
            </p>

            <div className="hero-buttons">
              <button
                className="btn btn-primary"
                onClick={() => { setActivePage('menu'); window.scrollTo({ top: 0, behavior: 'smooth' }); }}
              >
                <Utensils size={18} />
                Explore Menu
              </button>
              <button
                className="btn btn-outline"
                onClick={() => { setActivePage('booking'); window.scrollTo({ top: 0, behavior: 'smooth' }); }}
              >
                <Calendar size={18} />
                Book a Table
              </button>
            </div>
          </div>

          <div className="hero-image-wrapper">
            <img
              src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80"
              alt="Spice Garden Restaurant Dining Area"
              className="hero-image"
            />
            <div className="hero-badge">
              <div className="hero-badge-icon">⭐</div>
              <div>
                <strong style={{ fontSize: '1.1rem', display: 'block' }}>4.9 / 5.0 Rating</strong>
                <span style={{ fontSize: '0.85rem', color: '#64748b' }}>Over 1,200+ Local Reviews</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Feature Section */}
      <section className="container">
        <div className="section-header">
          <span className="section-tag">Why Choose Us</span>
          <h2 className="section-title">An Unforgettable Culinary Journey</h2>
        </div>

        <div className="features-grid">
          <div className="feature-card">
            <div className="feature-icon">
              <Flame />
            </div>
            <h3>Authentic Recipes</h3>
            <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.5rem' }}>
              Hand-ground spices and traditional recipes passed down through generations.
            </p>
          </div>

          <div className="feature-card">
            <div className="feature-icon">
              <Award />
            </div>
            <h3>Fresh & Premium</h3>
            <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.5rem' }}>
              100% organic herbs, farm-fresh vegetables, and premium quality cuts.
            </p>
          </div>

          <div className="feature-card">
            <div className="feature-icon">
              <Clock />
            </div>
            <h3>Fast Table Reservation</h3>
            <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.5rem' }}>
              Instant table booking system tailored for hassle-free dining.
            </p>
          </div>

          <div className="feature-card">
            <div className="feature-icon">
              <HeartHandshake />
            </div>
            <h3>Warm Hospitality</h3>
            <p style={{ color: '#64748b', fontSize: '0.9rem', marginTop: '0.5rem' }}>
              Attentive service in an elegant, cozy ambience suitable for family & friends.
            </p>
          </div>
        </div>
      </section>
    </div>
  );
}
