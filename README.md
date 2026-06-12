cat <<'EOF' > README.md
# Multi-Tenant SaaS Invoice & Inventory System

A high-performance, multi-tenant SaaS application built with Laravel 13. This system utilizes physical database isolation for each tenant via stancl/tenancy and PostgreSQL, ensuring data security and high scalability.

## 🚀 Architecture Overview
The system employs a "Central-Tenant" separation architecture:
* Central DB: Manages tenant registration, domain mapping, and global routing.
* Tenant DB: Each tenant operates in a dedicated PostgreSQL database, providing total physical data isolation.



## 🛠 Tech Stack
* Framework: PHP 8.3+, Laravel 13
* Database: PostgreSQL (Multi-database isolation)
* Frontend: Inertia.js + Vue.js (SPA Architecture)
* Auth: Laravel Sanctum (API) + Session (Web)
* Concurrency: Pessimistic locking (lockForUpdate) for inventory integrity

## 📋 Setup Guide

### 1. Requirements
Ensure you have PHP 8.3+, Composer, Node.js, and PostgreSQL installed.

### 2. Installation
git clone https://github.com/liqi-nvx/assignments.git
cd assignments
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate

### 3. Database Migration
php artisan migrate --force
php artisan db:seed

## 🧪 Testing
The system includes robust unit and feature tests covering multi-tenant context switching and database operations.

php artisan test

Testing Highlights:
* Connection Management: Includes pg_terminate_backend logic to force-release PostgreSQL locks, ensuring DROP DATABASE succeeds during test teardown.
* Queue Testing: Uses PHP Reflection to verify multi-tenant ID integrity in asynchronous jobs.

## 💡 Architecture Notes
* Concurrency Safety: Uses lockForUpdate() in invoice processing to prevent race conditions and over-selling of inventory.
* SMTP Isolation: Custom service dynamic reconfiguration for per-tenant SMTP settings, with Mail::forgetMailers() to ensure container instance integrity.
* Financial Precision: All monetary values use decimal(12, 2) to eliminate floating-point errors.
* Automation: Uses scheduled tasks with optimized indices for efficient overdue invoice processing.

## 📊 Database Schema (ERD)

EOF