@echo off
echo FlavorConnect Docker Image Import Script
echo ======================================
echo.

echo Step 1: Extracting compressed files...
echo This may take a few minutes depending on your system.
powershell -Command "Expand-Archive -Path flavorconnect-web-image.zip -DestinationPath . -Force"
powershell -Command "Expand-Archive -Path flavorconnect-api-image.zip -DestinationPath . -Force"
echo Extraction complete.
echo.

echo Step 2: Importing Docker images...
echo This may take several minutes. Please be patient.
docker load -i flavorconnect-web-image.tar
docker load -i flavorconnect-api-image.tar
echo Import complete.
echo.

echo Step 3: Verifying imported images...
docker images | findstr flavorconnect
echo.

echo All done! Your FlavorConnect Docker images are now ready to use.
echo Please refer to docker-export-readme.md for more information.
echo.

pause
