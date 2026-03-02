const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const connectDB = require('./db');
const User = require('./User');

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-this';

module.exports = async (req, res) => {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: 'Method not allowed' });
  }

  try {
    // Connect to MongoDB
    await connectDB();

    const { username, email, password } = req.body;

    // Validate input
    if (!username || !email || !password) {
      return res.status(200).json({ success: false, message: 'All fields are required' });
    }

    if (password.length < 6) {
      return res.status(200).json({ success: false, message: 'Password must be at least 6 characters' });
    }

    // Check if email exists
    const existingEmail = await User.findOne({ email });
    if (existingEmail) {
      return res.status(200).json({ success: false, message: 'Email already registered' });
    }

    // Check if username exists
    const existingUsername = await User.findOne({ username });
    if (existingUsername) {
      return res.status(200).json({ success: false, message: 'Username already taken' });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    // Create user
    const user = new User({
      username,
      email,
      password: hashedPassword
    });

    await user.save();

    // Generate token
    const token = jwt.sign({ userId: user._id }, JWT_SECRET, { expiresIn: '7d' });

    res.status(200).json({
      success: true,
      message: 'Registration successful!',
      sessionToken: token,
      userId: user._id,
      username: user.username,
      email: user.email
    });
  } catch (error) {
    console.error('Signup error:', error);
    res.status(200).json({ success: false, message: 'Registration failed' });
  }
};
