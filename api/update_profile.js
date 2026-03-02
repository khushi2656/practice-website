const jwt = require('jsonwebtoken');
const connectDB = require('./db');
const User = require('./User');

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-this';

module.exports = async (req, res) => {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: 'Method not allowed' });
  }

  try {
    // Connect to MongoDB
    await connectDB();

    const token = req.headers.authorization?.replace('Bearer ', '');
    if (!token) {
      return res.status(200).json({ success: false, message: 'Not authenticated' });
    }

    const decoded = jwt.verify(token, JWT_SECRET);
    const { username, email } = req.body;

    if (!username || !email) {
      return res.status(200).json({ success: false, message: 'All fields are required' });
    }

    // Check if new username is taken by another user
    const existingUsername = await User.findOne({ 
      username, 
      _id: { $ne: decoded.userId } 
    });
    if (existingUsername) {
      return res.status(200).json({ success: false, message: 'Username already taken' });
    }

    // Check if new email is taken by another user
    const existingEmail = await User.findOne({ 
      email, 
      _id: { $ne: decoded.userId } 
    });
    if (existingEmail) {
      return res.status(200).json({ success: false, message: 'Email already registered' });
    }

    const user = await User.findByIdAndUpdate(
      decoded.userId,
      { username, email },
      { new: true }
    ).select('-password');

    res.status(200).json({
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
    res.status(200).json({ success: false, message: 'Failed to update profile' });
  }
};
