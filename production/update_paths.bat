@echo off
echo FlavorConnect Path Update Tool
echo ============================
echo.
echo This script will update all relative paths to initialize.php with absolute paths for Bluehost deployment.
echo A backup of all modified files will be created before making any changes.
echo.
echo Press any key to continue or CTRL+C to cancel...
pause > nul

php update_paths.php

echo.
echo Process complete! Press any key to exit...
pause > nul
