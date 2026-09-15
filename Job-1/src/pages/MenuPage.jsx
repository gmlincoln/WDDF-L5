import React, { useState, useEffect } from 'react';
import FilterBar from '../components/FilterBar';
import MenuCard from '../components/MenuCard';
import sampleMenuData from '../data/Sample Menu Data.json';

export default function MenuPage() {
  const [menuItems, setMenuItems] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [searchQuery, setSearchQuery] = useState('');
  const [vegFilter, setVegFilter] = useState('All');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Load from Sample Menu Data.json
    setMenuItems(sampleMenuData);
    setLoading(false);
  }, []);

  const categories = ['All', 'Appetizer', 'Main Course', 'Dessert', 'Drinks', 'Combo'];

  // Combined filtering logic: category + search + veg/non-veg
  const filteredItems = menuItems.filter((item) => {
    // 1. Category Filter
    const matchesCategory = selectedCategory === 'All' || item.category === selectedCategory;

    // 2. Search Filter (by dish name)
    const matchesSearch = item.name.toLowerCase().includes(searchQuery.toLowerCase().trim());

    // 3. Veg / Non-Veg Toggle Filter
    let matchesVeg = true;
    if (vegFilter === 'Veg') {
      matchesVeg = item.is_veg === true;
    } else if (vegFilter === 'Non-Veg') {
      matchesVeg = item.is_veg === false;
    }

    return matchesCategory && matchesSearch && matchesVeg;
  });

  return (
    <div className="container" style={{ paddingTop: '2rem' }}>
      <div className="section-header">
        <span className="section-tag">Our Delicious Offerings</span>
        <h1 className="section-title">Spice Garden Menu</h1>
        <p style={{ color: '#64748b', marginTop: '0.5rem' }}>
          Explore our wide variety of appetizers, main courses, desserts, drinks, and special combos.
        </p>
      </div>

      {/* Interactive Combined Filter Bar */}
      <FilterBar
        categories={categories}
        selectedCategory={selectedCategory}
        setSelectedCategory={setSelectedCategory}
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        vegFilter={vegFilter}
        setVegFilter={setVegFilter}
      />

      {/* Menu Cards Display */}
      {loading ? (
        <div style={{ textAlignment: 'center', padding: '3rem' }}>Loading menu items...</div>
      ) : filteredItems.length > 0 ? (
        <div className="menu-grid">
          {filteredItems.map((item) => (
            <MenuCard key={item.id} item={item} />
          ))}
        </div>
      ) : (
        <div
          style={{
            textAlign: 'center',
            padding: '4rem 1rem',
            background: 'white',
            borderRadius: '16px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.05)'
          }}
        >
          <h3 style={{ fontSize: '1.25rem', color: '#334155' }}>No dishes found matching your filters</h3>
          <p style={{ color: '#94a3b8', marginTop: '0.5rem' }}>
            Try clearing your search query or selecting a different category/veg option.
          </p>
          <button
            className="btn btn-primary"
            style={{ marginTop: '1.25rem' }}
            onClick={() => {
              setSelectedCategory('All');
              setSearchQuery('');
              setVegFilter('All');
            }}
          >
            Reset All Filters
          </button>
        </div>
      )}
    </div>
  );
}
