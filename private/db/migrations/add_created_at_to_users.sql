-- Add created_at column to user_account table
ALTER TABLE user_account
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update existing users to have a created_at timestamp
UPDATE user_account
SET created_at = CURRENT_TIMESTAMP
WHERE created_at IS NULL;
