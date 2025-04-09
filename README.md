# FlavorConnect

A recipe sharing and management platform where users can discover, share, and organize their favorite recipes. A Capstone Project for WEB-289 at Asheville-Buncombe Technical Community College.

## Features

- Recipe sharing and discovery
- User accounts with save favorites functionality
- Recipe search and filtering by cuisine, diet, meal type, title, and description
- Responsive design for all devices
- Image processing with ImageMagick and GD library
- Measurement pluralization based on quantity
- Dynamic form validation
- Intuitive recipe creation interface
- Flexible routing system with middleware support
- Context-aware navigation with recipe state preservation
- Bluehost deployment support

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
   - phpMyAdmin: http://localhost:8081

### Using XAMPP

1. Extract the zip file or clone the repository to your XAMPP htdocs folder
2. Import the database schema from `/private/database/vaughn-henry-dump.sql`
3. Access the application at:
   - Website: http://localhost/FlavorConnect
   - API: http://localhost/FlavorConnect/api

## Development and Deployment

### Environment Separation

FlavorConnect is designed to work in two development environments:

1. **Docker Environment**:
   - Uses container-based development setup
   - Includes pre-configured web server, database, and API services
   - Optimized for containerized development workflow

2. **XAMPP Environment**:
   - Traditional local development setup
   - Uses relative paths for includes
   - Development mode enabled with detailed error reporting

**Note:** The codebase has been optimized specifically for Docker and XAMPP environments. For production deployment, a separate version is available in the `production` folder that's specifically optimized for production environments.

For deployment details, see the [DEPLOYMENT_GUIDE.md](production/DEPLOYMENT_GUIDE.md) file in the production folder.

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

## Stopping the Application

### Docker
To stop the application, run:
```
docker-compose down
```

### XAMPP
Stop the Apache and MySQL services from the XAMPP control panel.

## Project Structure

### Public Directory
- `/public` - Web accessible files
  - `/public/assets` - Static assets
    - `/public/assets/css` - Stylesheets
      - `/public/assets/css/pages` - Page-specific styles (recipe-crud.css, etc.)
      - `/public/assets/css/components` - Component styles
    - `/public/assets/js` - JavaScript files
      - `/public/assets/js/pages` - Page-specific scripts (recipe-form.js, etc.)
      - `/public/assets/js/components` - Reusable component scripts
    - `/public/assets/images` - Static images and icons
    - `/public/assets/uploads` - User-uploaded content
      - `/public/assets/uploads/recipes` - Recipe images (original, thumb, medium)
  - `/public/recipes` - Recipe-related pages
    - Recipe listing, creation, editing, and viewing
    - Includes form_fields.php for reusable form components
  - `/public/admin` - Admin interface
    - User management, category management
  - `/public/auth` - Authentication pages (login, register, password reset)
  - `/public/account` - User account management pages
  - `/public/api` - API endpoints for AJAX functionality

### Private Directory
- `/private` - Application core files (not directly web accessible)
  - `/private/classes` - PHP classes for application objects
    - Data models (Recipe, User, Measurement, etc.)
    - Service classes for business logic
    - Utility classes for common operations
    - Router.class.php - Flexible routing system with middleware support
  - `/private/config` - Configuration files
    - Database connection settings
    - Environment-specific configurations
    - Feature flags and application settings
    - routes.php - Application route definitions
  - `/private/core` - Core application classes
    - Initialization and bootstrapping
    - Error handling and logging
  - `/private/database` - Database scripts and schemas
    - SQL dump files for different environments
    - Migration scripts
    - Seed data for development
  - `/private/shared` - Shared components like headers and footers
    - Page templates and layouts
    - Common UI components
    - Notification elements

### Docker Configuration
- `/docker` - Docker configuration files
  - Docker Compose configuration
  - Container definitions and environment settings
  - Development environment setup

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
- MySQL 8.0
- Apache Web Server
- Node.js for API services
- JavaScript (ES6+)
- CSS3 with custom variables for theming
- Docker (primary development environment)
- XAMPP (alternative development environment)
- ImageMagick for image processing (with GD library fallback)
