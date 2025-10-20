# 420 Vallarta - Cannabis Delivery E-commerce Platform

## 🌿 Project Overview

**420 Vallarta** is a comprehensive cannabis/marijuana delivery e-commerce platform operating in Puerto Vallarta, Mexico. Built with PHP and MySQL, it provides a complete business management solution for cannabis product sales, order processing, and delivery services with emphasis on discretion, safety, and professional customer service.

## 🏗️ System Architecture

### Technology Stack
- **Backend:** PHP 7.4+ with MySQL database
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Server Environment:** XAMPP 8.2 (Apache, MySQL, PHP)
- **Dependencies:**
  - TCPDF for PDF receipt generation
  - PHPMailer for email functionality
  - Facebook Graph SDK & Twitter OAuth for social media integration

### Database Structure
- **`movies`** - Product catalog (cannabis strains/products)
- **`ordere`** - Customer orders and order management
- **`cart`** - Shopping cart items (both logged-in and guest users)
- **`user_info`** - Staff/admin user accounts
- **`cat`** - Product categories (hierarchical structure)
- **`grp`** - Product groups/strains (hierarchical structure)
- **`reg`** - Delivery regions with associated fees
- **`inventory`** - Stock tracking and inventory management

## 🎯 Core Business Features

### 1. Product Management
- **Cannabis strain catalog** with detailed descriptions and effects
- **Hierarchical categorization** (Indica, Sativa, Hybrid, Edibles, etc.)
- **Real-time inventory tracking** with stock level management
- **Product images** and comprehensive strain information
- **Dual currency pricing** (MXN primary with USD conversion)

### 2. E-commerce Functionality
- **Guest shopping cart** (no registration required for customers)
- **Advanced product search** and filtering by category, strain type, region
- **Shopping cart management** with quantity updates and validation
- **Real-time stock validation** to prevent overselling
- **Responsive design** for mobile and desktop users

### 3. Order Processing System
- **Multi-step checkout** with comprehensive customer information collection
- **Multiple payment methods:**
  - Oxxo Transfer (cash deposit at convenience stores)
  - Bank Transfer (BBVA bank account)
  - Credit/Debit Cards (via Stripe integration)
  - PayPal payments
  - Apple Pay/Google Pay (via Stripe)
- **Order validation** and automatic inventory deduction
- **WhatsApp confirmation** system for order verification

### 4. Delivery Management
- **Nationwide Mexico shipping** capabilities
- **Regional delivery fees** based on geographic location
- **Address management** and delivery tracking
- **Estimated delivery times** with real-time updates
- **Uber delivery integration** for local orders

## 💼 Admin Management System

### Order Management Dashboard
- **Comprehensive order dashboard** with advanced search and filtering
- **Order status tracking** (New, Processing, Confirming, Ready for Delivery, In Delivery, Delivered, Cancelled, Delayed)
- **Order editing** and modification capabilities
- **Automatic inventory adjustment** when orders are modified
- **Order finalization** with custom receipt settings

### Product Administration
- **Complete product CRUD operations** (cannabis strains and accessories)
- **Category and group management** with hierarchical structure
- **Inventory management** with detailed stock tracking
- **Image upload** and product media management
- **Bulk operations** for efficient product management

### Professional Receipt System
- **PDF receipt generation** with professional formatting and branding
- **Dual currency display** (MXN/USD) with real-time conversion
- **Customizable receipt settings** (delivery fees, discounts, refunds)
- **Email delivery** of receipts to customers
- **Complimentary items** tracking and inclusion
- **Payment method-specific instructions** in receipts

## 🔐 Security & Compliance Features

### Privacy & Discretion
- **Discrete ordering system** without explicit cannabis references in payment processing
- **Anonymous guest checkout** option for customer privacy
- **Secure payment processing** through established, compliant gateways
- **WhatsApp-based communication** for order confirmation and updates

### Legal Compliance
- **Mexico cannabis laws compliance** (operating within legal framework)
- **Payment platform sensitivity** (avoiding cannabis-related terms in transactions)
- **Discrete branding** and marketing approach
- **Professional business practices** and customer service

## 📱 User Experience Design

### Customer Journey
1. **Browse products** by category, strain type, or search functionality
2. **Add products to cart** (no registration required)
3. **Proceed to checkout** with contact and delivery information
4. **Select payment method** with detailed, method-specific instructions
5. **Receive WhatsApp confirmation** for order verification
6. **Get professional PDF receipt** via email
7. **Track delivery status** through communication channels

### Staff Workflow
1. **Monitor incoming orders** through comprehensive admin dashboard
2. **Validate and process** orders with inventory verification
3. **Update inventory levels** automatically upon order confirmation
4. **Finalize orders** with custom delivery and receipt settings
5. **Send professional receipts** to customers via email
6. **Track delivery progress** and completion status

## 🌐 Business Model & Operations

### Revenue Streams
- **Product sales** (cannabis strains, edibles, accessories)
- **Delivery fees** (varies by geographic region)
- **Premium services** for regular customers
- **Consultation services** for strain selection

### Target Market
- **Tourists** visiting Puerto Vallarta and surrounding areas
- **Local residents** throughout Mexico
- **Cannabis enthusiasts** seeking quality, tested products
- **Medical users** (though not explicitly medical-focused)

### Operating Hours
- **Monday:** 2:00 PM - 7:00 PM
- **Tuesday:** 12:00 PM - 7:00 PM
- **Wednesday:** Closed
- **Thursday:** 12:00 PM - 7:00 PM
- **Friday:** 12:00 PM - 7:00 PM
- **Saturday:** 2:00 PM - 7:00 PM
- **Sunday:** 2:00 PM - 6:00 PM

## 🔧 Technical Implementation

### Advanced Features
- **Real-time inventory management** with automatic stock deduction
- **Multi-currency support** (MXN primary, USD conversion with live rates)
- **Professional PDF receipt generation** with customizable branding
- **Social media integration** for marketing and customer engagement
- **Responsive design** optimized for mobile and desktop devices
- **Guest cart persistence** using PHP sessions

### Integration Points
- **Email marketing** via PHPMailer with HTML templates
- **Social media posting** (Facebook, Twitter, Instagram)
- **Payment gateway integration** (Stripe, PayPal, Oxxo)
- **WhatsApp business** communication system
- **Uber delivery** integration for local orders

### File Structure
```
vallarta-php/
├── admin/                 # Admin management system
├── assets/               # Frontend assets (CSS, JS, images)
├── cart/                 # Shopping cart functionality
├── LogReg/               # Login/registration system
├── settings/             # Configuration and utility functions
├── uploads/              # Product images and media
├── vendor/               # Composer dependencies
├── index.php             # Main homepage
├── movie.php             # Product catalog
├── cart.php              # Shopping cart
├── checkout.php          # Order processing
├── admin.php             # Admin dashboard
└── finalize_order.php    # Order finalization system
```

## 🚀 Installation & Setup

### Prerequisites
- XAMPP 8.2 or similar LAMP/WAMP stack
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (for dependency management)

### Installation Steps
1. **Clone the repository** to your XAMPP htdocs directory
2. **Import the database** using the provided SQL file
3. **Configure database settings** in `settings/db.php`
4. **Install dependencies** using `composer install`
5. **Set up email configuration** in PHPMailer settings
6. **Configure payment gateways** (Stripe, PayPal, etc.)
7. **Set up file permissions** for uploads directory

### Configuration
- Update database credentials in `settings/db.php`
- Configure email settings for PHPMailer
- Set up payment gateway credentials
- Configure social media API keys
- Set up WhatsApp business integration

## 📊 Business Intelligence

### Analytics & Reporting
- **Order tracking** and completion rates
- **Inventory management** with low-stock alerts
- **Customer analytics** and purchasing patterns
- **Revenue reporting** with currency conversion
- **Delivery performance** metrics

### Inventory Management
- **Real-time stock tracking** across all products
- **Automatic inventory deduction** upon order placement
- **Low-stock alerts** for inventory replenishment
- **Inventory history** tracking for audit purposes
- **Bulk inventory updates** for efficient management

## 🔒 Security Considerations

### Data Protection
- **Input validation** and sanitization
- **SQL injection prevention** using prepared statements
- **XSS protection** with proper output escaping
- **Session management** with secure practices
- **File upload security** with type validation

### Business Security
- **Discrete operations** to protect customer privacy
- **Secure payment processing** through established gateways
- **Encrypted communication** for sensitive data
- **Access control** for admin functions
- **Audit logging** for compliance and security


## 📄 License & Legal

This project operates within the legal framework of Mexico's cannabis regulations. The platform emphasizes compliance, discretion, and professional business practices while providing quality cannabis products and services to customers.

---

**Note:** This is a commercial cannabis delivery platform operating in Mexico. All operations comply with local regulations and emphasize customer privacy, product quality, and professional service delivery.
