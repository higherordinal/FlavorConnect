const express = require('express');
const router = express.Router();
const mysql = require('mysql2/promise');

// Create database pool
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'db',
    port: parseInt(process.env.DB_PORT) || 3306,
    user: process.env.DB_USER || 'hcvaughn',
    password: process.env.DB_PASSWORD || '@connect4Establish',
    database: process.env.DB_NAME || 'flavorconnect',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Check if a recipe is favorited by a user
router.get('/:userId/:recipeId', async (req, res) => {
    try {
        const [rows] = await pool.execute(
            'SELECT COUNT(*) as count FROM user_favorite WHERE user_id = ? AND recipe_id = ?',
            [req.params.userId, req.params.recipeId]
        );
        res.json({ isFavorited: rows[0].count > 0 });
    } catch (error) {
        console.error('Error checking favorite status:', error);
        res.status(500).json({ message: 'Error checking favorite status', error: error.message });
    }
});

// Get user favorites
router.get('/:userId', async (req, res) => {
    try {
        const [rows] = await pool.execute(
            'SELECT r.* FROM recipe r JOIN user_favorite f ON r.recipe_id = f.recipe_id WHERE f.user_id = ?',
            [req.params.userId]
        );
        res.json(rows);
    } catch (error) {
        console.error('Error fetching favorites:', error);
        res.status(500).json({ message: 'Error fetching favorites', error: error.message });
    }
});

// Toggle favorite status
router.post('/toggle', async (req, res) => {
    const { userId, recipeId } = req.body;
    try {
        if (!userId || !recipeId) {
            throw new Error('Missing required fields: userId and recipeId');
        }

        // Check if already favorited
        const [existing] = await pool.execute(
            'SELECT COUNT(*) as count FROM user_favorite WHERE user_id = ? AND recipe_id = ?',
            [userId, recipeId]
        );
        
        const isFavorited = existing[0].count > 0;
        
        if (isFavorited) {
            // Remove favorite
            await pool.execute(
                'DELETE FROM user_favorite WHERE user_id = ? AND recipe_id = ?',
                [userId, recipeId]
            );
        } else {
            // Add favorite
            await pool.execute(
                'INSERT INTO user_favorite (user_id, recipe_id) VALUES (?, ?)',
                [userId, recipeId]
            );
        }

        res.json({
            success: true,
            isFavorited: !isFavorited,
            message: !isFavorited ? 'Added to favorites' : 'Removed from favorites'
        });
    } catch (error) {
        console.error('Error toggling favorite:', error);
        res.status(500).json({ 
            success: false,
            message: 'Error toggling favorite status', 
            error: error.message 
        });
    }
});

module.exports = router;
