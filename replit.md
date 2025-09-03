# Overview

This is a COP 4331 university assignment implementing a contact management system using a LAMP stack (Linux, Apache, MySQL, PHP). The project demonstrates basic web development concepts including user authentication, session management, and CRUD operations. The application allows users to log in and manage a list of colors as a demonstration of database interaction patterns.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
The frontend uses vanilla HTML, CSS, and JavaScript with a simple multi-page structure:
- **index.html**: Login page with username/password authentication
- **color.html**: Main application interface for color management
- **CSS Framework**: Custom styles with Google Fonts (Ubuntu, Vend Sans) for typography
- **JavaScript**: Pure vanilla JS without frameworks, organized in modular functions

## Authentication System
- **Client-side**: MD5 hashing library included but currently unused (password sent in plaintext)
- **Session Management**: Cookie-based session storage for user persistence
- **User State**: Maintains userId, firstName, and lastName globally

## API Integration
- **Backend URL**: Points to `http://129.212.183.29/LAMPAPI` 
- **API Pattern**: RESTful PHP endpoints with `.php` extension
- **Communication**: JSON payloads over XMLHttpRequest
- **Authentication Endpoint**: `/Login.php` for user verification

## Data Management
- **Frontend State**: Global variables for user session data
- **API Communication**: JSON-based request/response pattern
- **Operations**: Login authentication, color search, and color addition functionality

## Design Patterns
- **Separation of Concerns**: HTML structure, CSS styling, and JavaScript logic kept separate
- **Event-Driven**: DOM event listeners for user interactions
- **Callback Pattern**: XMLHttpRequest with onreadystatechange handlers

# External Dependencies

## Third-Party Libraries
- **MD5.js**: JavaScript MD5 hashing library from blueimp/JavaScript-MD5
- **Google Fonts**: Ubuntu and Vend Sans font families

## Backend Services
- **LAMP Stack Server**: Remote server at 129.212.183.29
- **PHP API**: Backend endpoints for authentication and data operations
- **MySQL Database**: Implied database backend for user and color data storage

## Browser APIs
- **XMLHttpRequest**: For API communication
- **DOM API**: For user interface manipulation
- **Cookie API**: For session persistence
- **Window Location**: For page navigation