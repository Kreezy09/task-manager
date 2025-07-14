#!/bin/bash

# Task Management System Setup Script
# This script will set up the Laravel + Vue.js task management system

echo "🚀 Setting up Task Management System..."

# Check if required tools are installed
echo "📋 Checking prerequisites..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1+ first."
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 16+ first."
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi

echo "✅ All prerequisites are installed!"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

echo ""
echo "🔧 Configuration Required:"
echo "Please update your .env file with the following settings:"
echo ""
echo "Database Configuration:"
echo "DB_CONNECTION=pgsql"
echo "DB_HOST=127.0.0.1"
echo "DB_PORT=5432"
echo "DB_DATABASE=task_manager"
echo "DB_USERNAME=postgres"
echo "DB_PASSWORD=your_password"
echo ""
echo "Mail Configuration:"
echo "MAIL_MAILER=smtp"
echo "MAIL_HOST=your_smtp_host"
echo "MAIL_PORT=587"
echo "MAIL_USERNAME=your_username"
echo "MAIL_PASSWORD=your_password"
echo "MAIL_ENCRYPTION=tls"
echo "MAIL_FROM_ADDRESS=noreply@example.com"
echo "MAIL_FROM_NAME=\"Task Management System\""
echo ""

read -p "Have you configured your .env file? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Please configure your .env file and run this script again."
    exit 1
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate

# Run seeders
echo "🌱 Running database seeders..."
php artisan db:seed --class=AdminUserSeeder

# Build frontend assets
echo "🏗️ Building frontend assets..."
npm run build

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Start the Laravel development server:"
echo "   php artisan serve"
echo ""
echo "2. (Optional) Start the Vite development server:"
echo "   npm run dev"
echo ""
echo "3. Access the application at: http://localhost:8000"
echo ""
echo "4. Login with the default admin account:"
echo "   Email: admin@example.com"
echo "   Password: password"
echo ""
echo "5. For production, set up a queue worker for email notifications:"
echo "   php artisan queue:work"
echo ""
echo "📚 For more information, see the README.md file." 