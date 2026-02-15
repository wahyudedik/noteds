# Marketplace & Plugin System Documentation

## Overview
The Marketplace System allows the platform to sell digital products (Web, Desktop, Mobile applications/plugins) directly to users. It supports manual bank transfer payments, order verification by admins, and automated email notifications.

## Features

### 1. Multi-Platform Application Support
The system supports selling various types of applications:
- **Web Applications**
- **Desktop Applications**
- **Mobile Applications**

Each product listing includes:
- Pricing (Free/Paid)
- Category & Type
- Screenshots Gallery
- System Requirements
- Demo URL
- Version History

### 2. Payment System (Manual Transfer)
- **Multi-Bank Support**: Admins can configure multiple bank accounts (BCA, Mandiri, BRI, etc.) via the Admin Panel.
- **Checkout Process**:
  1. User clicks "Buy Now" on a paid plugin.
  2. User selects a destination bank.
  3. User uploads proof of transfer (Image/PDF).
  4. Order status becomes `Pending`.
- **Verification**:
  - Admins review proofs in the Order Management dashboard.
  - Upon approval (`Paid` status), the user receives a confirmation email with a download link.

### 3. Order Management
- **Dashboard**: View all orders with status (Pending, Paid, Rejected).
- **Export**: Export sales data to Excel (`.xlsx`) for reporting.
- **Notifications**:
  - `OrderSubmitted`: Sent to user when they upload proof.
  - `PaymentVerified`: Sent to user when admin approves the order (includes download link).

### 4. Plugin Versioning & Updates
- **Auto-Detection**: Uploading a new `.zip` file automatically detects the version from `manifest.json`.
- **Update System**:
  - Replaces the current version while keeping a history of previous versions.
  - Supports **Rollback** to previous versions if needed.
- **Update Notifications**:
  - Automatically sends an email to all existing buyers when a new version is released.
- **Large File Support**: Configured to handle file uploads up to **512MB**.

## Admin User Guide

### Managing Bank Accounts
1. Go to **Admin > Marketplace Settings** (or Bank Accounts menu).
2. Click **Add Bank Account**.
3. Enter Bank Name, Account Number, and Account Holder.
4. Toggle "Active" to show/hide it during checkout.

### Adding/Editing a Product
1. Go to **Admin > Plugins**.
2. Upload a plugin `.zip` file or edit an existing one.
3. In the **Marketplace Settings** section:
   - Check **Paid Plugin?** and set the **Price**.
   - Add **Demo URL**, **Thumbnail**, and **Description**.
   - Set **Category** (Web/Desktop/Mobile).
4. Save changes.

### Verifying Orders
1. Go to **Admin > Orders**.
2. Click **Details** on a pending order to view the proof of transfer.
3. If valid, change status to **Paid** and save.
   - *The user will automatically receive the download link via email.*
4. If invalid, change status to **Rejected** and add an Admin Note.

### Updating a Plugin Version
1. Go to the plugin's detail page in Admin.
2. Scroll to **Update Plugin Version**.
3. Upload the new `.zip` file.
4. Click **Upload & Update**.
   - *All previous buyers will receive an "Update Available" email.*

## Technical Implementation

### Database Schema
- **`plugins`**: Stores product details, price, category, etc.
- **`plugin_orders`**: Stores transaction data, proof file path, buyer info.
- **`bank_accounts`**: Stores admin bank details.
- **`plugin_versions`**: Stores history of file versions for rollback.

### Key Classes
- **Controllers**:
  - `Admin\MarketplaceController`: Handles orders, bank accounts, and settings.
  - `Admin\PluginController`: Handles plugin upload, updates, and versioning.
  - `MarketplaceController`: Handles user checkout and buy logic.
- **Models**: `Plugin`, `PluginOrder`, `BankAccount`, `PluginVersion`.
- **Mailables**: `OrderSubmitted`, `PaymentVerified`, `PluginUpdated`.

### Configuration
To support large file uploads (e.g., >100MB), the following PHP settings are enforced via `.user.ini` and `.htaccess`:
```ini
upload_max_filesize = 512M
post_max_size = 512M
memory_limit = 512M
```
*Note: Nginx users must also configure `client_max_body_size 512M;` in their server config.*
