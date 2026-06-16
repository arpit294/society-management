# Society Management System (SMP)

A comprehensive Laravel-based application for managing residential society operations, including flat management, resident tracking, expense monitoring, and automated maintenance bill generation.

## 🌟 Key Features

* **Resident & Flat Management:** Track owners, tenants, and their assigned flats across different blocks.
* **Dynamic Maintenance Billing:** Generate monthly bills based on flat types (Owner vs. Rental rates).
* **Smart Penalty & Discount Engine:** 
  * Automatically calculates late penalties on a **month-by-month** basis.
  * Tiers include Monthly, Quarterly, Half-Yearly, and Yearly penalties.
  * Older overdue months scale up to higher penalty tiers automatically.
  * Prepayment discounts apply similarly for advance payments.
* **Expense Tracking & Complaints:** Log society expenses and manage resident complaints efficiently.
* **Role-Based Access Control:** Distinct roles for Admins, Secretaries, and standard Users/Residents.
* **Automated Scheduled Tasks:** Cron jobs ensure bills are updated daily with the latest accurate penalty/discount amounts.

## 🛠️ Technology Stack

* **Framework:** Laravel 11 (PHP 8.3)
* **Frontend:** Blade Templates, Bootstrap/Custom CSS, jQuery, and DataTables (Yajra)
* **Database:** MySQL / MariaDB (via Eloquent ORM)
* **PDF Generation:** Barryvdh/DomPDF for generating invoice receipts.

## ⚙️ Core Business Logic Notes

### Penalty Calculation (`MaintenanceBillController.php`)
The system avoids flat-rate penalties on large arrears. Instead, it iterates through each past-due month individually. 
For example, if a resident pays 5 months late:
1. The 5th month (oldest) might hit the "Quarterly" 15% tier.
2. The 1st month (newest) might only hit the "Monthly" 10% tier.
This rewards residents for paying sooner and prevents unfair compounding. *See `calculatePenaltyAndDiscount()` in the controller for the exact loop implementation.*

## 🚀 Getting Started

### Prerequisites
* PHP 8.3+
* Composer
* MySQL or equivalent database
* Node.js & NPM (for frontend assets)

### Installation

1. **Clone the repository and install dependencies:**
   ```bash
   composer install
   npm install && npm run build
   ```

2. **Environment Setup:**
   Copy the `.env.example` file to `.env` and configure your database credentials.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Migration & Seeding:**
   Run the migrations to build the schema and seed initial data (like Flat Types and Admin users).
   ```bash
   php artisan migrate --seed
   ```

4. **Start the Development Server:**
   ```bash
   php artisan serve
   ```

## 🧹 Code Quality
This project enforces the Laravel coding standard. Before committing new PHP code, run Laravel Pint to automatically fix formatting issues:
```bash
vendor/bin/pint
```
