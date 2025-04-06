@echo off
echo FlavorConnect Bluehost Deployment Tool
echo ====================================
echo.
echo This script prepares the FlavorConnect application for Bluehost deployment by:
echo  - Updating all relative paths to use absolute paths
echo  - Replacing key files with Bluehost-optimized versions
echo  - Updating configuration files for the production environment
echo A backup of all modified files will be created before making any changes.
echo.
echo Press any key to continue or CTRL+C to cancel...
pause > nul

php deploy_bluehost.php

echo.
echo Process complete! Press any key to exit...
pause > nul
