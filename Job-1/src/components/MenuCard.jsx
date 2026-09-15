import React from 'react';

export default function MenuCard({ item }) {
  const { name, category, price, is_veg, available, description, image } = item;

  return (
    <div className={`menu-card ${!available ? 'unavailable-card' : ''}`}>
      <div className="card-img-wrapper">
        <img src={image} alt={name} className="card-img" />
        <div className="badge-overlay">
          <span className={`badge ${is_veg ? 'badge-veg' : 'badge-non-veg'}`}>
            {is_veg ? '🌱 Veg' : '🍗 Non-Veg'}
          </span>
          <span className="badge badge-category">{category}</span>
        </div>

        {available === false && (
          <div className="badge-unavailable">
            Unavailable
          </div>
        )}
      </div>

      <div className="card-body">
        <div className="card-title-row">
          <h3 className="card-title">{name}</h3>
          <span className="card-price">৳{price.toFixed(2)}</span>
        </div>
        <p className="card-desc">{description}</p>
      </div>
    </div>
  );
}
