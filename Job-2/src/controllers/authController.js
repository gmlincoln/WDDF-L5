/**
 * Auth Controller - User Registration & Login
 */
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const DatabaseFactory = require('../patterns/DatabaseFactory');
const { JWT_SECRET } = require('../middleware/authMiddleware');

const register = async (req, res) => {
  try {
    const { username, password, role } = req.body;

    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: 'Username and password are required.'
      });
    }

    const userRole = role && ['admin', 'staff'].includes(role.toLowerCase()) ? role.toLowerCase() : 'staff';
    const pool = DatabaseFactory.getConnection();

    // Check if username already exists (SQL Injection Prevention: Parameterized Query)
    const [existing] = await pool.execute('SELECT id FROM users WHERE username = ?', [username]);
    if (existing.length > 0) {
      return res.status(400).json({
        success: false,
        message: 'Username is already taken.'
      });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insert user (SQL Injection Prevention: Parameterized Query)
    const [result] = await pool.execute(
      'INSERT INTO users (username, password, role) VALUES (?, ?, ?)',
      [username, hashedPassword, userRole]
    );

    return res.status(201).json({
      success: true,
      message: 'User registered successfully.',
      data: {
        id: result.insertId,
        username,
        role: userRole
      }
    });
  } catch (error) {
    console.error('Registration Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error during registration.',
      error: error.message
    });
  }
};

const login = async (req, res) => {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: 'Username and password are required.'
      });
    }

    const pool = DatabaseFactory.getConnection();

    // Fetch user by username (SQL Injection Prevention: Parameterized Query)
    const [users] = await pool.execute('SELECT * FROM users WHERE username = ?', [username]);
    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Invalid username or password.'
      });
    }

    const user = users[0];

    // Verify password
    const isPasswordValid = await bcrypt.compare(password, user.password);
    if (!isPasswordValid) {
      return res.status(401).json({
        success: false,
        message: 'Invalid username or password.'
      });
    }

    // Generate JWT Token
    const tokenPayload = {
      id: user.id,
      username: user.username,
      role: user.role
    };

    const token = jwt.sign(tokenPayload, JWT_SECRET, { expiresIn: '8h' });

    return res.status(200).json({
      success: true,
      message: 'Login successful.',
      token,
      user: {
        id: user.id,
        username: user.username,
        role: user.role
      }
    });
  } catch (error) {
    console.error('Login Error:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error during login.',
      error: error.message
    });
  }
};

module.exports = {
  register,
  login
};
