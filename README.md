# FlavorConnect

A recipe sharing and management platform where users can discover, share, and organize their favorite recipes. A Capstone Project for WEB-289 at Asheville-Buncombe Technical Community College.

## Features

- Recipe sharing and discovery
- User accounts with favorites functionality
- Recipe search and filtering
- Responsive design for all devices
- Automatic environment detection (Docker, XAMPP, Bluehost)
- Image processing with ImageMagick and GD library

## Prerequisites

### For Docker Development
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running
- Git (optional, for cloning the repository)

### For XAMPP Development
- [XAMPP](https://www.apachefriends.org/) installed and running
- PHP 8.1 or higher
- MySQL 8.0 or higher

## Quick Start

### Using Docker

1. Extract the zip file or clone the repository
2. Open a terminal/command prompt in the project directory
3. Run the following command to start the application:
   ```
   docker-compose up -d
   ```
4. Wait for all containers to start (this may take a few minutes on first run)
5. Access the application at:
   - Website: http://localhost:8080
   - API: http://localhost:3000
   - Database: localhost:3306

### Using XAMPP

1. Extract the zip file or clone the repository to your XAMPP htdocs folder
2. Import the database schema from `/private/database/vaughn-henry-dump-local.sql`
3. Access the application at:
   - Website: http://localhost/FlavorConnect
   - API: http://localhost/FlavorConnect/api

## Environment Detection

FlavorConnect now automatically detects and configures itself for different environments:

- **Docker**: Detected by the presence of Docker-specific files
- **XAMPP/Local**: Default environment when not in Docker or production
- **Bluehost/Production**: Detected by the domain or server configuration

No manual configuration changes are needed when switching between environments.

## Database Configuration

### Docker Environment
- Host: db
- Port: 3306
- Username: user
- Password: @connect4Establish
- Database: flavorconnect

### XAMPP Environment
- Host: localhost
- Port: 3306
- Username: user
- Password: @connect4Establish
- Database: flavorconnect

### Bluehost Production Environment
- Host: localhost
- Port: 3306
- Username: swbhdnmy_user
- Password: @Connect4establish
- Database: swbhdnmy_db_flavorconnect

## Stopping the Application

### Docker
To stop the application, run:
```
docker-compose down
```

### XAMPP
Stop the Apache and MySQL services from the XAMPP control panel.

## Project Structure

- `/public` - Web accessible files
- `/private` - Application core files
  - `/private/config` - Configuration files
  - `/private/core` - Core application classes
  - `/private/database` - Database scripts and schemas
- `/api` - API service
- `/docker` - Docker configuration files

## Additional Documentation

- [Image Processing Setup](README-IMAGE-PROCESSING.md) - Instructions for setting up ImageMagick for recipe image processing
- [API Documentation](api/README.md) - Details about the API endpoints and usage

## Troubleshooting

If you encounter any issues:

### Docker Issues
1. Make sure Docker Desktop is running
2. Try rebuilding the containers:
   ```
   docker-compose down
   docker-compose up -d --build
   ```
3. Check the logs:
   ```
   docker-compose logs
   ```

### Database Issues
1. Verify database connection settings
2. Check that the database user has proper permissions
3. For XAMPP, ensure MySQL service is running

### API Issues
1. Check that the API server is running
2. Verify API endpoints in the browser console
3. Check API logs for errors

## Technologies Used

- PHP 8.1
- Node.js 18
- MySQL 8.0
- Apache Web Server
- Docker
- XAMPP (alternative development environment)
