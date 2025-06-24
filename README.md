# Order Viewer - E-commerce Order Management System

A Laravel-based web application for browsing and managing e-commerce orders with real-time filtering, live statistics, and responsive design.

## Features

### Functional Requirements ✅
- **Order List**: Display orders with ID, Customer, Status, Total, and Created Date
- **Filter Panel**: Support for date range, status, and min/max total filtering
- **Details View**: Click any order row to view line items, quantities, and prices
- **Live Statistics**: Real-time count and grand total of filtered orders without page refresh
- **Payment Management**: Mark orders as "Paid" and refresh list without full page reload

### Technical Implementation
- **Backend**: Laravel 8 with PHP 7.4+
- **Database**: SQLite with migrations and seeders (~50 demo orders)
- **Frontend**: Vanilla JavaScript with responsive CSS Grid/Flexbox
- **API**: RESTful endpoints with filtering and pagination
- **Testing**: Comprehensive PHPUnit tests (feature and unit tests)
- **Accessibility**: Keyboard navigation and ARIA attributes

## Quick Start

### Prerequisites
- PHP 7.4 or higher
- Composer
- SQLite (or MySQL/PostgreSQL if preferred)

### Installation & Setup

1. **Clone or download the project**
   ```bash
   # If using git
   git clone <repository-url>
   cd order-viewer
   
   # Or extract from zip and navigate to folder
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # Create SQLite database file (choose one based on your system):
   
   # Linux/Mac:
   touch database/database.sqlite
   
   # Windows Command Prompt:
   type nul > database\database.sqlite
   
   # Windows PowerShell:
   New-Item database\database.sqlite -ItemType File
   
   # Run migrations and seed demo data
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

6. **Access the application**
   Open your browser to: **http://127.0.0.1:8000**

**Note**: The SQLite database will persist your data between server restarts. You only need to run the database setup once.

## Usage Guide

### Main Interface
- **Filter Orders**: Use the filter panel to search by status, date range, or total amount
- **Live Statistics**: View real-time statistics that update as you apply filters
- **Order Details**: Click any order row to view detailed information including line items
- **Mark as Paid**: Use the "Mark as Paid" button in order details for unpaid orders

### API Endpoints
- `GET /api/orders` - List orders with filtering and pagination
- `GET /api/orders/{id}` - Get order details with line items
- `GET /api/orders-statistics` - Get filtered order statistics
- `PATCH /api/orders/{id}/mark-as-paid` - Mark order as paid
- `PUT /api/orders/{id}` - Update order (status, customer info)

### Filtering Options
- **Status**: Pending, Processing, Shipped, Cancelled
- **Date Range**: Start and end dates
- **Total Range**: Minimum and maximum order amounts
- **Real-time**: Filters apply automatically with debouncing

## Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Feature tests (API endpoints)
php artisan test tests/Feature/OrderApiTest.php

# Unit tests (Model logic)
php artisan test tests/Unit/OrderModelTest.php
```

### Test Coverage
- ✅ Order listing and filtering
- ✅ Order details retrieval
- ✅ Payment status updates
- ✅ Statistics calculation
- ✅ Input validation
- ✅ Error handling
- ✅ Model relationships and scopes

## Troubleshooting

### Database Issues

**Problem**: "Database (database/database.sqlite) does not exist" error

**Solutions**:
1. Ensure the SQLite file exists:
   ```bash
   # Check if file exists
   ls database/database.sqlite  # Linux/Mac
   dir database\database.sqlite  # Windows
   ```

2. Create the database file if missing:
   ```bash
   # Linux/Mac
   touch database/database.sqlite
   
   # Windows Command Prompt
   type nul > database\database.sqlite
   
   # Windows PowerShell  
   New-Item database\database.sqlite -ItemType File
   ```

3. Update database path in `.env` if needed:
   ```bash
   # Use absolute path for Windows
   DB_DATABASE="C:/full/path/to/your/project/database/database.sqlite"
   ```

4. Clear configuration cache and reseed:
   ```bash
   php artisan config:clear
   
   # For first-time setup or complete reset:
   php artisan migrate:fresh --seed
   
   # For adding data to existing database:
   php artisan db:seed
   ```

**Note**: Use `migrate:fresh --seed` only for initial setup or when you want to completely reset the database. For normal operation, data should persist between server restarts.

### API Errors

**Problem**: 500 Internal Server Error on API endpoints

**Solutions**:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Ensure database is seeded: `php artisan db:seed`
3. Verify routes: `php artisan route:list`
4. Clear all caches: `php artisan optimize:clear`

## Architecture & Design Decisions

### Database Design
- **Orders Table**: Core order information with indexes on filtered columns
- **Order Items Table**: Line items with foreign key constraints
- **SQLite**: Chosen for easy setup and portability

### Backend Architecture
- **Repository Pattern**: Clean separation of data access logic
- **Eloquent ORM**: Laravel's built-in ORM for database operations
- **API Resources**: Consistent JSON responses
- **Form Requests**: Centralized validation logic
- **Scopes**: Reusable query filters on models

### Frontend Design
- **Vanilla JavaScript**: No framework dependencies for simplicity
- **Responsive Design**: CSS Grid and Flexbox for mobile compatibility
- **Accessibility**: Semantic HTML, ARIA attributes, keyboard navigation
- **Debouncing**: Prevents excessive API calls during typing
- **Modal Interface**: Clean order details view

### Performance Considerations
- **Database Indexes**: On filtered columns (status, created_at, total)
- **Pagination**: Prevents large dataset issues
- **Debounced Filtering**: Reduces API calls
- **Lazy Loading**: Order items loaded only when needed

## File Structure

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/OrderController.php      # API endpoints
│   │   └── OrderViewController.php      # Web interface
│   └── Models/
│       ├── Order.php                    # Order model with scopes
│       └── OrderItem.php               # Order item model
├── database/
│   ├── factories/                      # Test data factories
│   ├── migrations/                     # Database schema
│   └── seeders/                        # Demo data seeder
├── resources/views/
│   ├── layouts/app.blade.php           # Base layout
│   └── orders/index.blade.php          # Main order viewer
├── routes/
│   ├── api.php                         # API routes
│   └── web.php                         # Web routes
└── tests/
    ├── Feature/OrderApiTest.php        # API integration tests
    └── Unit/OrderModelTest.php         # Model unit tests
```

## Future Enhancements

If development were to continue beyond the 6-hour limit, the following features would be prioritized:

1. **User Authentication**: Add login/registration system
2. **Export Functionality**: CSV/PDF export of filtered orders
3. **Advanced Filtering**: Customer search, product filtering
4. **Real-time Updates**: WebSocket integration for live order updates
5. **Order Management**: Create, edit, and delete orders
6. **Inventory Integration**: Link orders to product inventory
7. **Reporting Dashboard**: Charts and analytics
8. **Email Notifications**: Order status change notifications

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
