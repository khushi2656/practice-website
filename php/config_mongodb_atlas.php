<?php
// MongoDB Atlas Configuration Example
// Use this if you're using MongoDB Atlas (Cloud)

// MongoDB Atlas Connection String
define('MONGO_CONNECTION_STRING', 'mongodb+srv://YOUR_USERNAME:YOUR_PASSWORD@YOUR_CLUSTER.mongodb.net/user_profiles_db?retryWrites=true&w=majority');
define('MONGO_DB', 'user_profiles_db');

// Function to get MongoDB connection (for Atlas)
function getMongoDBConnection() {
    try {
        $mongoClient = new MongoDB\Driver\Manager(MONGO_CONNECTION_STRING);
        return $mongoClient;
    } catch (Exception $e) {
        error_log("MongoDB Connection failed: " . $e->getMessage());
        return null;
    }
}

// OR for Local MongoDB (current setup):
// define('MONGO_HOST', 'localhost');
// define('MONGO_PORT', 27017);
// define('MONGO_DB', 'user_profiles_db');
// 
// function getMongoDBConnection() {
//     try {
//         $mongoClient = new MongoDB\Driver\Manager("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
//         return $mongoClient;
//     } catch (Exception $e) {
//         error_log("MongoDB Connection failed: " . $e->getMessage());
//         return null;
//     }
// }
?>
