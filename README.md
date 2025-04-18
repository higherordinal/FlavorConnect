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
- Enhanced error handling and 404 page system
- Context-aware navigation with recipe state preservation
- Bluehost deployment support
- Admin dashboard for content management

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
5. Install ImageMagick using the provided script:
   ```
   docker exec -it flavorconnect-web bash /var/www/html/install-imagemagick-docker.sh
   ```
6. Access the application at:
   - Website: http://localhost:8080
   - API: http://localhost:3000
   - Database: localhost:3306
   - phpMyAdmin: http://localhost:8081

### Using XAMPP

1. Extract the zip file or clone the repository to your XAMPP htdocs folder
2. Import the database schema from `/private/database/vaughn-henry-dump.sql`
3. If using Linux/macOS and you need to install ImageMagick, use the provided script:
   ```
   sudo bash install-imagemagick-xampp.sh
   ```
4. Access the application at:
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

### Root Directory
- `.htaccess` - Apache configuration for URL handling and security
- `docker-compose.yml` - Docker Compose configuration for multi-container setup
- `install-imagemagick-docker.sh` - Script for installing ImageMagick in Docker environment
- `install-imagemagick-xampp.sh` - Script for installing ImageMagick in XAMPP environment
- `README.md` - Project documentation (this file)
- `.gitignore` - Git configuration for ignored files

### Public Directory Structure

- `/public` — Web-accessible files and main entry points
  - `.htaccess` — Main Apache config for URL routing and error handling
  - `index.php` — Main entry point for the application
  - `404.php`— Custom 404 error page
  - `about.php` — About page
  - `env_test.php` — Environment detection/config test utility
  - `robots.txt` — Robots exclusion protocol

  - `/assets` — Static assets for the frontend
    - `/css` — Main stylesheets
      - `/pages` — Page-specific CSS (e.g., `user-profile.css`, `admin.css`, `home.css`, `recipe-crud.css`, etc.)
      - `/components` — Reusable component styles (e.g., `forms.css`, `header.css`, `footer.css`, `recipe-card.css`, etc.)
      - `main.css` — Global site styles
    - `/js` — JavaScript files
      - `/pages` — Page-specific scripts (`admin.js`, `auth.js`, `recipe-gallery.js`, `recipe-show.js`, `user-favorites.js`)
      - `/components` — Reusable JS components (`pagination.js`, `member-header.js`, `recipe-form.js`, `recipe-favorite.js`)
      - `/utils` — Utility scripts (`form-validation.js`, `common.js`, `back-link.js`, `recipe-scale.js`)
      - `.htaccess` — JS directory-specific config
    - `/images` — Static images, icons, and graphics (`hero-img.webp`, `about-hero-img.webp`, `flavorconnect_favicon.ico`, etc.)
    - `/uploads` — User-uploaded content
      - `/recipes` — Recipe images (originals, thumbnails, medium sizes)

  - `/recipes` — Recipe-related pages
    - `index.php` — Recipe listing/gallery
    - `new.php` — Recipe creation form
    - `edit.php` — Recipe editing form
    - `show.php` — Recipe detail view
    - `form_fields.php` — Reusable form components for recipe forms
    - `delete.php` — Recipe deletion confirmation

    - `recipe-card.php` — Partial for rendering recipe cards

  - `/auth` — Authentication pages
    - `login.php` — Login form
    - `logout.php` — Logout handler
    - `register.php` — Registration form

  - `/admin` — Admin panel
    - `index.php` — Admin dashboard
    - `/categories` — Category management (with subfolders for diet, measurement, style, type)
    - `/users` — User management (`index.php`, `edit.php`, `delete.php`, `new.php`, `form_fields.php`)

  - `/users` — User profile and favorites
    - `profile.php` — User profile page
    - `favorites.php` — User’s favorite recipes

  - `/api` - API endpoints for AJAX functionality
    - `.htaccess` - API-specific Apache configuration with JSON error handling
    - `index.php` - API root endpoint
    - `error.php` - JSON error responses for API 404 errors
    - `toggle_favorite.php` - Endpoint for toggling recipe favorites

### Private Directory
- `/private` - Application core files (not directly web accessible)
  - `/private/classes` - PHP classes for application objects
    - `DatabaseObject.class.php` - Base class for database-backed objects
    - `Recipe.class.php` - Recipe model with CRUD operations
    - `User.class.php` - User model with authentication and profile management
    - `RecipeIngredient.class.php` - Recipe ingredient model
    - `RecipeStep.class.php` - Recipe step/instruction model
    - `RecipeAttribute.class.php` - Recipe attributes (cuisine, diet, etc.)
    - `RecipeFavorite.class.php` - User favorite recipes functionality
    - `RecipeFilter.class.php` - Recipe filtering and search
    - `RecipeReview.class.php` - Recipe reviews and ratings
    - `RecipeImageProcessor.class.php` - Image processing for recipe photos
    - `Measurement.class.php` - Measurement handling with pluralization
    - `Pagination.class.php` - Pagination functionality
    - `Session.class.php` - Session management
    - `TimeUtility.class.php` - Date and time utilities
  - `/private/config` - Configuration files
    - `config.php` - Environment-specific configurations
    - `api_config.php` - API-specific configuration
  - `/private/core` - Core application functionality
    - `initialize.php` - Application bootstrapping
    - `api_functions.php` - API helper functions
    - `auth_functions.php` - Authentication and authorization functions
    - `core_utilities.php` - Core utility functions
    - `database_functions.php` - Database connection and utility functions
    - `error_functions.php` - Error handling and logging
    - `validation_functions.php` - Form validation helpers
  - `/private/database` - Database scripts and schemas
    - `vaughn-henry-dump.sql` - Main database schema and seed data
  - `/private/shared` - Shared UI components
    - `public_header.php` - Header for public-facing pages
    - `member_header.php` - Header for logged-in user pages
    - `footer.php` - Common footer for all pages
    - `seo_meta.php` - SEO metadata for pages

### API Directory
- `/api` - Node.js API service
  - `/api/routes` - API route definitions
  - `Dockerfile` - Docker configuration for API service
  - `package.json` - Node.js dependencies

### Docker Configuration
- `/docker` - Docker configuration files
  - `/docker/apache` - Apache configuration for web container
    - `000-default.conf` - Apache virtual host configuration
  - `/docker/php` - PHP configuration for web container
    - `custom.ini` - PHP settings

### Production Configuration
- `/production` - Production deployment files and documentation
  - `.bluehost-main-htaccess` - Bluehost-specific Apache configuration
  - `.bluehost-api-htaccess` - Bluehost-specific API Apache configuration
  - `bluehost_config.php` - Production environment configuration
  - `bluehost_api_config.php` - Production API configuration
  - `Recipe.live.class.php` - Production-optimized Recipe class
  - `RecipeImageProcessor.live.class.php` - Production-optimized image processor
  - `deploy_bluehost.php` - PHP deployment script for Bluehost
  - `deploy_bluehost.bat` - Windows batch script for deployment
  - `deploy_bluehost.sh` - Linux/macOS shell script for deployment
  - `DEPLOYMENT_GUIDE.md` - Comprehensive but not yet completed deployment instructions
  
**Note:** The production environment uses a simplified configuration approach where the public folder contents are placed directly in the document root.

## Additional Documentation

- [Image Processing Setup](README-IMAGE-PROCESSING.md) - Instructions for setting up ImageMagick for recipe image processing
  - `install-imagemagick-xampp.sh` - Script for installing ImageMagick in XAMPP environment (Linux/macOS)
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
4. Install ImageMagick if needed:
   ```
   docker exec -it flavorconnect-web bash /var/www/html/install-imagemagick-docker.sh
   ```

### Environment and Configuration Issues
1. Use the environment test utility to verify your setup:
   ```
   http://localhost:8080/env_test.php     (Docker)
   http://localhost/FlavorConnect/env_test.php     (XAMPP)
   ```
2. The env_test.php utility will show:
   - Which environment was detected (Docker or XAMPP)
   - Database connection status for both web and API
   - Server information and configuration details
   - Environment variables and detection methods
   - Image processing capabilities and configuration

### Database Issues
1. Verify database connection settings
2. Check that the database user has proper permissions
3. For XAMPP, ensure MySQL service is running
4. Use env_test.php to test database connections

### API Issues
1. Check that the API server is running
2. Verify API endpoints in the browser console
3. Check API logs for errors
4. Use env_test.php to verify API database connection

## Technologies Used

- PHP 8.1
- MySQL 8.0
- Apache Web Server
- Node.js for API services
- JavaScript (ES6+)
- CSS3 with custom variables for theming and consistent form styling
- Docker (primary development environment)
- XAMPP (alternative development environment)
- ImageMagick for image processing (with GD library fallback)
- Modular CSS architecture with component-based styling
- Responsive design principles for all screen sizes
