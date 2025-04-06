#!/bin/bash

echo "FlavorConnect Bluehost Deployment Tool"
echo "===================================="
echo ""
echo "This script prepares the FlavorConnect application for Bluehost deployment by:"
echo " - Updating all relative paths to use absolute paths"
echo " - Replacing key files with Bluehost-optimized versions"
echo " - Updating configuration files for the production environment"
echo "A backup of all modified files will be created before making any changes."
echo ""
echo "Press Enter to continue or CTRL+C to cancel..."
read

php deploy_bluehost.php

echo ""
echo "Process complete!"
