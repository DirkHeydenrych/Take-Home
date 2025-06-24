# GitHub Copilot Instructions

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Project Overview
This is a Laravel-based e-commerce Order Viewer application that provides:
- RESTful API endpoints for order management
- Responsive web interface with filtering capabilities
- Live statistics without page refresh
- Database-first approach with migrations and seeders

## Code Style Guidelines
- Follow PSR-12 coding standards for PHP
- Use Laravel's Eloquent ORM for all database operations
- Implement async/await patterns for JavaScript
- Use Laravel's built-in validation and authentication
- Write comprehensive PHPUnit tests for all features
- Use semantic HTML and ARIA attributes for accessibility

## Architecture Patterns
- Repository pattern for data access layer
- Service layer for business logic
- Resource controllers for API endpoints
- Form request classes for validation
- Event/listener pattern for order status changes
- Queue jobs for heavy operations

## Database Design
- Use migrations for all schema changes
- Include proper foreign key constraints
- Add indexes for filtered columns (status, created_at, total)
- Use soft deletes where appropriate
- Follow Laravel naming conventions

## Frontend Guidelines
- Use vanilla JavaScript or minimal framework
- Implement responsive design with CSS Grid/Flexbox
- Ensure keyboard navigation support
- Use debouncing for search/filter inputs
- Implement proper error handling and user feedback

## Testing Requirements
- Unit tests for models and services
- Feature tests for API endpoints
- Integration tests for the full workflow
- Test edge cases and error conditions
- Maintain high test coverage
