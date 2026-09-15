/**
 * Factory Pattern Implementation for Database Connection
 * Encapsulates the instantiation logic for database connections based on environment type.
 */
const dbSingleton = require('./DatabaseSingleton');

class DatabaseFactory {
  static createConnection(type = 'mysql', customConfig = {}) {
    switch (type.toLowerCase()) {
      case 'mysql':
        return dbSingleton.init(customConfig);
      default:
        throw new Error(`[Factory Error] Unsupported database type: ${type}`);
    }
  }

  static getConnection() {
    return dbSingleton.getPool();
  }
}

module.exports = DatabaseFactory;
