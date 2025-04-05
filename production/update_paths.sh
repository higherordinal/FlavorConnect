#!/bin/bash

echo "FlavorConnect Path Update Tool"
echo "============================"
echo ""
echo "This script will update all relative paths to initialize.php with absolute paths for Bluehost deployment."
echo "A backup of all modified files will be created before making any changes."
echo ""
echo "Press Enter to continue or CTRL+C to cancel..."
read

php update_paths.php

echo ""
echo "Process complete!"
