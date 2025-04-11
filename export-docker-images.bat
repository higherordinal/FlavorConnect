@echo off
echo FlavorConnect Docker Image Export Script
echo ======================================
echo.

echo Step 1: Building Docker images...
echo This may take several minutes. Please be patient.
docker-compose build
echo Build complete.
echo.

echo Step 2: Exporting Docker images as tar files...
echo This may take several minutes. Please be patient.
docker save flavorconnect-web -o flavorconnect-web-image.tar
docker save flavorconnect-api -o flavorconnect-api-image.tar
echo Export of tar files complete.
echo.

echo Step 3: Compressing tar files to ZIP format...
echo Using PowerShell for compression...
powershell -Command "Compress-Archive -Path flavorconnect-web-image.tar -DestinationPath flavorconnect-web-image.zip -Force"
powershell -Command "Compress-Archive -Path flavorconnect-api-image.tar -DestinationPath flavorconnect-api-image.zip -Force"
echo Compression complete.
echo.

echo Step 4: Verifying exported files...
dir *.zip
echo.

echo All done! Your FlavorConnect Docker images have been exported as ZIP files.
echo You can distribute these files and import them using the import-docker-images.bat script.
echo.

pause
