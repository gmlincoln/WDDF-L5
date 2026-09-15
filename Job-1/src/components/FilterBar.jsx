import React from 'react';
import { Search } from 'lucide-react';

export default function FilterBar({
  categories,
  selectedCategory,
  setSelectedCategory,
  searchQuery,
  setSearchQuery,
  vegFilter,
  setVegFilter
}) {
  return (
    <div className="filter-bar">
      <div className="filter-row">
        {/* Search Input */}
        <div className="search-box">
          <Search className="search-icon" size={18} />
          <input
            type="text"
            className="search-input"
            placeholder="Search dish by name..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        {/* Veg/Non-Veg Toggle */}
        <div className="veg-toggle-group">
          <button
            type="button"
            className={`toggle-option ${vegFilter === 'All' ? 'active' : ''}`}
            onClick={() => setVegFilter('All')}
          >
            All
          </button>
          <button
            type="button"
            className={`toggle-option ${vegFilter === 'Veg' ? 'active veg' : ''}`}
            onClick={() => setVegFilter('Veg')}
          >
            🌱 Veg
          </button>
          <button
            type="button"
            className={`toggle-option ${vegFilter === 'Non-Veg' ? 'active non-veg' : ''}`}
            onClick={() => setVegFilter('Non-Veg')}
          >
            🍗 Non-Veg
          </button>
        </div>
      </div>

      {/* Category Pills */}
      <div className="category-pills">
        {categories.map((cat) => (
          <button
            key={cat}
            type="button"
            className={`pill-btn ${selectedCategory === cat ? 'active' : ''}`}
            onClick={() => setSelectedCategory(cat)}
          >
            {cat}
          </button>
        ))}
      </div>
    </div>
  );
}
