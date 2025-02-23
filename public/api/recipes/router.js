const express = require('express');
const router = express.Router();
const mysql = require('mysql2/promise');

// Create database pool
const pool = mysql.createPool({
    host: '127.0.0.1',
    port: 3307,
    user: 'hcvaughn',
    password: '@connect4Establish',
    database: 'flavorconnect',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Handle all recipe-related API requests
router.post('/', async (req, res) => {
    try {
        const { action, recipe_id } = req.body;
        
        switch (action) {
            case 'toggle_favorite':
                if (!recipe_id) {
                    throw new Error('Missing recipe_id');
                }
                
                // Check if already favorited
                const [existing] = await pool.execute(
                    'SELECT COUNT(*) as count FROM user_favorite WHERE user_id = ? AND recipe_id = ?',
                    [req.session.user_id, recipe_id]
                );
                
                const isFavorited = existing[0].count > 0;
                
                if (isFavorited) {
                    // Remove favorite
                    await pool.execute(
                        'DELETE FROM user_favorite WHERE user_id = ? AND recipe_id = ?',
                        [req.session.user_id, recipe_id]
                    );
                } else {
                    // Add favorite
                    await pool.execute(
                        'INSERT INTO user_favorite (user_id, recipe_id) VALUES (?, ?)',
                        [req.session.user_id, recipe_id]
                    );
                }
                
                res.json({
                    success: true,
                    is_favorited: !isFavorited,
                    message: !isFavorited ? 'Added to favorites' : 'Removed from favorites'
                });
                break;
                
            default:
                throw new Error('Invalid action');
        }
    } catch (error) {
        console.error('API Error:', error);
        res.status(500).json({
            success: false,
            message: 'Error processing request',
            error: error.message
        });
    }
});

module.exports = router;
