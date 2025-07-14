# School Management System

A modern, full-featured school management system for efficiently handling student, teacher, and school administration tasks. Built with Laravel and Vue.js, it provides a seamless experience for school staff, students, and administrators.

## Features
- Student registration, profiles, and subject management
- Teacher management and assignments
- School structure and curriculum setup
- Fee invoicing, payments, and receipt generation
- Mpesa integration for school payments
- Admin dashboards for school and system administrators
- Role-based access control (admin, teacher, student)
- Holiday and calendar management
- Dynamic theming and dark mode support

## Getting Started

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js & npm
- A database (MySQL, PostgreSQL, or SQLite)

### Installation
1. Clone the repository:
   ```sh
   git clone https://github.com/mushi23/school-management-system.git
   cd school-management-system
   ```
2. Install PHP dependencies:
   ```sh
   composer install
   ```
3. Install JavaScript dependencies:
   ```sh
   npm install
   ```
4. Copy the example environment file and configure your settings:
   ```sh
   cp .env.example .env
   # Edit .env as needed
   ```
5. Generate an application key:
   ```sh
   php artisan key:generate
   ```
6. Run migrations and seeders:
   ```sh
   php artisan migrate --seed
   ```
7. Build frontend assets:
   ```sh
   npm run build
   ```
8. Start the development server:
   ```sh
   php artisan serve
   ```

## Documentation
- [docs/](docs/) directory contains additional guides (e.g., Mpesa setup, payment flow, receipts).

## Contributing
Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License
This project is open-sourced under the MIT license.
