// Load environment variables from .env file
require('dotenv').config();

// Import mongoose
const mongoose = require('mongoose');

// Get MongoDB URI from environment variables
const MONGO_URI = process.env.MONGO_URI;

// Connect to MongoDB
mongoose.connect(MONGO_URI)
  .then(() => {
    console.log('MongoDB connected successfully!');
  })
  .catch((error) => {
    console.error('MongoDB connection failed:', error.message);
  });

// Export mongoose connection for use in other files
module.exports = mongoose;
