/**
 * XSS Prevention Middleware
 * Sanitizes input strings in req.body, req.query, and req.params against Cross-Site Scripting (XSS)
 */
const xss = require('xss');

const sanitizeObject = (data) => {
  if (!data || typeof data !== 'object') {
    if (typeof data === 'string') {
      return xss(data);
    }
    return data;
  }

  if (Array.isArray(data)) {
    return data.map(item => sanitizeObject(item));
  }

  const sanitized = {};
  for (const key in data) {
    if (Object.prototype.hasOwnProperty.call(data, key)) {
      sanitized[key] = sanitizeObject(data[key]);
    }
  }
  return sanitized;
};

const xssSanitizer = (req, res, next) => {
  if (req.body) req.body = sanitizeObject(req.body);
  if (req.query) req.query = sanitizeObject(req.query);
  if (req.params) req.params = sanitizeObject(req.params);
  next();
};

module.exports = xssSanitizer;
