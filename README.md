# FlavorConnect

A recipe sharing and management platform where users can discover, share, and organize their favorite recipes. A Capstone Project for WEB-289 at Asheville-Buncombe Technical Community College.

## Features

- Recipe sharing and discovery
- User accounts with favorites functionality
- Recipe search and filtering by cuisine, diet, and meal type
- Responsive design for all devices
- Image processing with ImageMagick and GD library
- Measurement pluralization based on quantity
- Dynamic form validation
- Intuitive recipe creation interface

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

## Deployment

### Bluehost Deployment

For Bluehost deployment, a simplified approach is used:

No manual configuration changes are needed when switching between environments.

Example path changes:

```php
// Change this (in local development):
require_once('../private/core/initialize.php');

// To this (in Bluehost deployment):
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/core/initialize.php');
```

**Important Notes:**
- On Bluehost, the application is deployed as an added domain with all files (including private folder) in the same root directory at `/home2/swbhdnmy/public_html/website_7135c1f5/`
- The private folder is located at `/home2/swbhdnmy/public_html/website_7135c1f5/private/`
- All path handling should use the document root's private directory:
  - For config files: `$_SERVER['DOCUMENT_ROOT'] . '/private/config/'`
  - For core files: `$_SERVER['DOCUMENT_ROOT'] . '/private/core/'`
  - For class files: `$_SERVER['DOCUMENT_ROOT'] . '/private/classes/'`

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
  - `/private/config` - Configuration files
    - Database connection settings
    - Environment-specific configurations
    - Feature flags and application settings
  - `/private/core` - Core application classes
    - Initialization and bootstrapping
    - Routing and request handling
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
- Docker
- XAMPP (alternative development environment)
- ImageMagick for image processing
