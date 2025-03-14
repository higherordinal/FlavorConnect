require('dotenv').config();
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(cors({
    origin: ['http://localhost:8080', 'http://localhost', 'http://localhost:8090'],
    methods: ['GET', 'POST', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type']
}));
app.use(express.json());

// Request logging middleware
app.use((req, res, next) => {
    console.log(`${req.method} ${req.path}`, {
        body: req.body,
        query: req.query,
        params: req.params
    });
    next();
});

// Function to create database connection with retry
const connectWithRetry = async () => {
    console.log('Attempting to connect to MySQL...');
    
    try {
        // Database connection
        const pool = mysql.createPool({
            host: process.env.DB_HOST || 'db',
            port: parseInt(process.env.DB_PORT) || 3306,
            user: process.env.DB_USER || 'flavorconnect',
            password: process.env.DB_PASSWORD || 'flavorconnect',
            database: process.env.DB_NAME || 'flavorconnect',
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0
        });

        // Test database connection
        const connection = await pool.getConnection();
        console.log('Successfully connected to database');
        connection.release();
        
        // Only set up routes after successful database connection
        setupRoutes(pool);
    } catch (err) {
        console.error('Error connecting to database:', err);
        console.log('Will retry connection in 5 seconds...');
        setTimeout(connectWithRetry, 5000);
    }
};

// Set up routes after database connection is established
const setupRoutes = (pool) => {
    // Make pool available to routes
    app.set('dbPool', pool);
    
    // Routes
    const favoritesRouter = require('./routes/favorites');
    app.use('/api/favorites', favoritesRouter);
    
    // Start the server
    app.listen(port, () => {
        console.log(`Server is running on port ${port}`);
    });
};

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({ 
        success: false,
        message: 'Something went wrong!',
        error: process.env.NODE_ENV === 'development' ? err.message : undefined
    });
});

// Start connection process
connectWithRetry();
