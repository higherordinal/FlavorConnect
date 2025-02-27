# FlavorConnect

A recipe sharing and management platform where users can discover, share, and organize their favorite recipes. A Capstone Project for WEB-289 at Asheville-Buncombe Technical Community College.

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running
- Git (optional, for cloning the repository)

## Quick Start

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

## Default Database Credentials

- Username: hcvaughn
- Password: @connect4Establish
- Database: flavorconnect

## Stopping the Application

To stop the application, run:
```
docker-compose down
```

## Project Structure

- `/public` - Web accessible files
- `/private` - Application core files
- `/api` - Node.js API service
- `/docker` - Docker configuration files

## Troubleshooting

If you encounter any issues:

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

## Technologies Used

- PHP 8.1
- Node.js 18
- MySQL 8.0
- Apache Web Server
- Docker
