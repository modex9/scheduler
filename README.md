# Appointment Scheduling System

A modern appointment scheduling system built with Laravel (Backend API) and Vue.js (Frontend SPA) that allows clients to book appointments and admins to manage working hours and services.

## Features

### For Clients
- 📅 Select a date using an interactive calendar
- 🕐 View all available time slots for the selected day
- 🎯 Choose from available services
- 📧 Book appointments by entering email address
- ✅ Real-time availability checking

### For Admins
- ⚙️ Define working hour rules (when services are available)
- 🛠️ Manage services with custom durations
- 🔄 Enable/disable services and working hours

## Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3
- **Styling**: Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Testing**: PHPUnit with Laravel Testing

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.2 or higher
- Composer
- Node.js 20+ and npm
- MySQL/PostgreSQL
- Git

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd scheduler
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Setup

Copy the environment file and configure your database:

```bash
cp .env.example .env
```

Edit `.env` file and update the database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scheduler
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Seed the Database

```bash
php artisan db:seed
```

This will create:
- Sample services (Haircut, Hair Coloring, Quick Trim)
- Working hours for Monday to Friday (9 AM - 5 PM)
- Service-working hour relationships

### 8. Build Frontend Assets

```bash
npm run build
```

## Running the Application

### Development Mode

1. **Start the Laravel Development Server**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`

2. **Start the Vite Development Server (for frontend)**
   ```bash
   npm run dev
   ```
   The frontend will be available at `http://localhost:5173`

### Production Mode

1. **Build the frontend assets**
   ```bash
   npm run build
   ```

2. **Start the Laravel server**
   ```bash
   php artisan serve
   ```

## Testing

### Running All Tests

```bash
php artisan test
```

### Running Specific Test Suites

```bash
# API Tests
php artisan test --filter=Api

# Feature Tests
php artisan test --filter=Feature

# Unit Tests
php artisan test --filter=Unit
```

### Test Coverage

The application includes comprehensive tests covering:

- **API Endpoints**: All CRUD operations for appointments, services, and working hours
- **Business Logic**: Availability calculation, booking validation, conflict detection
- **Edge Cases**: Past dates, double bookings, insufficient gaps
- **Model Relationships**: Service-appointment relationships, scopes
- **Data Seeding**: Verification of sample data creation

## API Endpoints

### Availability
- `GET /api/availability/slots` - Get available time slots for a date
- `GET /api/availability/check` - Check if a specific slot is available

### Appointments
- `POST /api/appointments` - Book a new appointment
- `GET /api/appointments/{id}` - Get appointment details
- `DELETE /api/appointments/{id}/cancel` - Cancel an appointment
- `GET /api/appointments/range` - Get appointments for date range

### Services
- `GET /api/services` - Get all services
- `POST /api/services` - Create a new service
- `PUT /api/services/{id}` - Update a service
- `DELETE /api/services/{id}` - Delete a service

### Working Hours
- `GET /api/working-hours` - Get all working hours
- `POST /api/working-hours` - Create working hours
- `PUT /api/working-hours/{id}` - Update working hours
- `DELETE /api/working-hours/{id}` - Delete working hours

## Usage Examples

### Booking an Appointment

1. **Select a Date**: Use the calendar to choose a future date
2. **Choose a Service**: Select from available services (Haircut, Hair Coloring, Quick Trim)
3. **Pick a Time**: Choose from available time slots
4. **Enter Email**: Provide your email address
5. **Confirm Booking**: Submit the form to book your appointment

### Managing Services (Admin)

1. **Access Admin Panel**: Click "Admin Panel" in the navigation
2. **Add Service**: Click "Add Service" and fill in:
   - Service name
   - Duration (in minutes)
   - Active status
3. **Edit/Delete**: Use the action buttons to modify existing services

### Managing Working Hours (Admin)

1. **Access Admin Panel**: Click "Admin Panel" in the navigation
2. **Add Working Hours**: Click "Add Hours" and configure:
   - Day of the week
   - Start time
   - End time
   - Active status
3. **Edit/Delete**: Use the action buttons to modify existing working hours
