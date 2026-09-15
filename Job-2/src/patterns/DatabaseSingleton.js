/**
 * Singleton Pattern Implementation for MySQL Database Connection Pool
 * Guarantees a single connection pool instance exists throughout the app lifecycle.
 */
const mysql = require('mysql2/promise');

class DatabaseSingleton {
  constructor() {
    if (DatabaseSingleton.instance) {
      return DatabaseSingleton.instance;
    }

    this.pool = null;
    DatabaseSingleton.instance = this;
  }

  init(config = {}) {
    if (!this.pool) {
      this.pool = mysql.createPool({
        host: config.host || process.env.DB_HOST || 'localhost',
        port: config.port || process.env.DB_PORT || 3306,
        user: config.user || process.env.DB_USER || 'root',
        password: config.password !== undefined ? config.password : (process.env.DB_PASSWORD || ''),
        database: config.database || process.env.DB_NAME || 'student_mgmt_db',
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0
      });
      console.log('[Singleton DB] Database connection pool initialized.');
    }
    return this.pool;
  }

  getPool() {
    if (!this.pool) {
      return this.init({});
    }
    return this.pool;
  }
}

const instance = new DatabaseSingleton();

module.exports = instance;

