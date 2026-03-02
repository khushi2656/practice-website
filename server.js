// Load environment variables
require('dotenv').config();

// Import dependencies
const express = require('express');
const cors = require('cors');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
require('./db'); // MongoDB connection
const User = require('./User');

// Create Express app
const app = express();
const PORT = process.env.PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-this';

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Signup route
app.post('/api/signup', async (req, res) => {
  try {
    const { username, email, password } = req.body;

    // Validate input
    if (!username || !email || !password) {
      return res.json({ success: false, message: 'All fields are required' });
    }

    if (password.length < 6) {
      return res.json({ success: false, message: 'Password must be at least 6 characters' });
    }

    // Check if email exists
    const existingEmail = await User.findOne({ email });
    if (existingEmail) {
      return res.json({ success: false, message: 'Email already registered' });
    }

    // Check if username exists
    const existingUsername = await User.findOne({ username });
    if (existingUsername) {
      return res.json({ success: false, message: 'Username already taken' });
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

    res.json({
      success: true,
      message: 'Registration successful!',
      sessionToken: token,
      userId: user._id,
      username: user.username,
      email: user.email
    });
  } catch (error) {
    console.error('Signup error:', error);
    res.json({ success: false, message: 'Registration failed' });
  }
});

// Login route
app.post('/api/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    // Validate input
    if (!email || !password) {
      return res.json({ success: false, message: 'All fields are required' });
    }

    // Find user
    const user = await User.findOne({ email });
    if (!user) {
      return res.json({ success: false, message: 'Invalid email or password' });
    }

    // Verify password
    const isValidPassword = await bcrypt.compare(password, user.password);
    if (!isValidPassword) {
      return res.json({ success: false, message: 'Invalid email or password' });
    }

    // Generate token
    const token = jwt.sign({ userId: user._id }, JWT_SECRET, { expiresIn: '7d' });

    res.json({
      success: true,
      message: 'Login successful!',
      sessionToken: token,
      userId: user._id,
      username: user.username,
      email: user.email
    });
  } catch (error) {
    console.error('Login error:', error);
    res.json({ success: false, message: 'Login failed' });
  }
});

// Get profile route
app.get('/api/get_profile', async (req, res) => {
  try {
    const token = req.headers.authorization?.replace('Bearer ', '');
    if (!token) {
      return res.json({ success: false, message: 'Not authenticated' });
    }

    const decoded = jwt.verify(token, JWT_SECRET);
    const user = await User.findById(decoded.userId).select('-password');
    
    if (!user) {
      return res.json({ success: false, message: 'User not found' });
    }

    res.json({
      success: true,
      user: {
        id: user._id,
        username: user.username,
        email: user.email
      }
    });
  } catch (error) {
    console.error('Get profile error:', error);
    res.json({ success: false, message: 'Failed to get profile' });
  }
});

// Update profile route
app.post('/api/update_profile', async (req, res) => {
  try {
    const token = req.headers.authorization?.replace('Bearer ', '');
    if (!token) {
      return res.json({ success: false, message: 'Not authenticated' });
    }

    const decoded = jwt.verify(token, JWT_SECRET);
    const { username, email } = req.body;

    if (!username || !email) {
      return res.json({ success: false, message: 'All fields are required' });
    }

    // Check if new username is taken by another user
    const existingUsername = await User.findOne({ 
      username, 
      _id: { $ne: decoded.userId } 
    });
    if (existingUsername) {
      return res.json({ success: false, message: 'Username already taken' });
    }

    // Check if new email is taken by another user
    const existingEmail = await User.findOne({ 
      email, 
      _id: { $ne: decoded.userId } 
    });
    if (existingEmail) {
      return res.json({ success: false, message: 'Email already registered' });
    }

    const user = await User.findByIdAndUpdate(
      decoded.userId,
      { username, email },
      { new: true }
    ).select('-password');

    res.json({
      success: true,
      message: 'Profile updated successfully!',
      user: {
        id: user._id,
        username: user.username,
        email: user.email
      }
    });
  } catch (error) {
    console.error('Update profile error:', error);
    res.json({ success: false, message: 'Failed to update profile' });
  }
});

// Logout route
app.post('/api/logout', (req, res) => {
  res.json({ success: true, message: 'Logged out successfully' });
});

// Start server
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});

module.exports = app;
