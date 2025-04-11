@echo off
echo FlavorConnect Docker Image Import Script
echo ======================================
echo.

echo Step 1: Extracting compressed files...
echo This may take a few minutes depending on your system.

if exist flavorconnect-web-image.zip (
    echo Found .zip files, using PowerShell for extraction...
    powershell -Command "Expand-Archive -Path flavorconnect-web-image.zip -DestinationPath . -Force"
    powershell -Command "Expand-Archive -Path flavorconnect-api-image.zip -DestinationPath . -Force"
) else (
    echo No compressed Docker image files found.
    echo Please make sure flavorconnect-web-image.zip and flavorconnect-api-image.zip exist.
    exit /b 1
)
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
