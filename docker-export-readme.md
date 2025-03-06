# FlavorConnect Docker Images

This package contains exported Docker images for the FlavorConnect project.

## Contents

- `flavorconnect-web-image.zip`: Compressed Docker image for the web application (746MB uncompressed)
- `flavorconnect-api-image.zip`: Compressed Docker image for the API service (1.59GB uncompressed)

## How to Import and Use These Images

1. Extract the zip files to get the .tar files
2. Import the Docker images:

```bash
# Import the web image
docker load -i flavorconnect-web-image.tar

# Import the API image
docker load -i flavorconnect-api-image.tar
```

3. Verify the images were imported:

```bash
docker images
```

4. Run the containers:

```bash
# Run the web container
docker run -d -p 80:80 flavorconnect-web:latest

# Run the API container
docker run -d -p 8080:8080 flavorconnect-api:latest
```

Note: You may need to adjust port mappings and environment variables based on your specific setup.

## Additional Requirements

- Docker must be installed on the recipient's machine
- The recipient may need to set up a Docker network if the containers need to communicate with each other
- Database configuration may need to be adjusted for the new environment

## Support

For any issues or questions, please contact the project maintainer.
