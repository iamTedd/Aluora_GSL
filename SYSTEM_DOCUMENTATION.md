# Aluora General Suppliers Limited (aluoragsl.com)
## Complete System Documentation

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Database Schema](#database-schema)
4. [User Authentication System](#user-authentication-system)
5. [Dashboard Features](#dashboard-features)
6. [Order Management](#order-management)
7. [Tender Request System](#tender-request-system)
8. [AI Chatbot System](#ai-chatbot-system)
9. [Human Support Referral](#human-support-referral)
10. [Admin Panel](#admin-panel)
11. [API Endpoints](#api-endpoints)
12. [Installation Guide](#installation-guide)
13. [Usage Guide](#usage-guide)

---

## System Overview

**Aluora GSL** is a comprehensive e-commerce and business management platform for a general supplies company. The system provides:

- **Public Website**: Product catalog, company information, contact
- **User Portal**: Registration, login, dashboard, orders, tenders, support
- **Admin Panel**: Full administrative control over the system

### Key Features

| Feature | Description |
|---------|-------------|
| Product Catalog | Browse products by categories with search |
| User Authentication | Secure login/registration with session management |
| Order Management | Place orders, track status, view history |
| Tender Requests | Submit formal requests for bulk quotes |
| AI Chatbot | 24/7 intelligent assistance |
| Human Support | Escalate to live agent when needed |
| Admin Panel | Complete system management |

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| Styling | Custom CSS (no frameworks) |
| Icons | Font Awesome 6 |
| Fonts | Google Fonts (Poppins, Open Sans) |
| JavaScript | Vanilla JS (no jQuery) |

---

## Database Schema

### Core Tables

#### `users`
Stores all registered users (customers, admins, staff).

```sql
- id: INT PRIMARY KEY
- email: VARCHAR(255) UNIQUE
- password: VARCHAR(255) (hashed)
- first_name, last_name: VARCHAR(100)
- phone, company, address: VARCHAR
- role: ENUM('customer', 'admin', 'staff')
- status: ENUM('active', 'inactive', 'suspended')
- created_at, updated_at: TIMESTAMP
- last_login: DATETIME
```

#### `categories`
Product categories for the catalog system.

```sql
- id: INT PRIMARY KEY
- name, slug: VARCHAR(255)
- description: TEXT
- parent_id: INT (self-referencing)
- sort_order: INT
- status: ENUM('active', 'inactive')
```

#### `products`
Main product catalog.

```sql
- id: INT PRIMARY KEY
- sku: VARCHAR(100) UNIQUE
- name, slug: VARCHAR(255)
- description, short_description: TEXT
- category_id: INT FOREIGN KEY
- price, sale_price: DECIMAL
- stock_quantity: INT
- featured: TINYINT (boolean)
- status: ENUM('active', 'inactive', 'out_of_stock')
```

#### `orders`
Customer orders.

```sql
- id: INT PRIMARY KEY
- order_number: VARCHAR(50) UNIQUE
- user_id: INT FOREIGN KEY
- subtotal, tax, shipping, total: DECIMAL
- status: ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled')
- payment_status: ENUM('pending', 'paid', 'failed', 'refunded')
- created_at, updated_at: TIMESTAMP
```

#### `order_items`
Individual items within orders.

```sql
- id: INT PRIMARY KEY
- order_id, product_id: INT FOREIGN KEY
- product_name, sku: VARCHAR
- quantity, unit_price, total: DECIMAL
```

#### `tenders`
Customer tender/bulk request submissions.

```sql
- id: INT PRIMARY KEY
- tender_number: VARCHAR(50) UNIQUE
- user_id: INT FOREIGN KEY
- title, description: TEXT
- category, quantity, budget_range: VARCHAR
- deadline: DATE
- status: ENUM('pending', 'reviewed', 'quoted', 'accepted', 'rejected', 'closed')
- admin_notes, quote_amount: TEXT/DECIMAL
```

#### `support_tickets`
Customer support tickets.

```sql
- id: INT PRIMARY KEY
- ticket_number: VARCHAR(50) UNIQUE
- user_id: INT FOREIGN KEY
- subject: VARCHAR(255)
- category: ENUM('general', 'order', 'product', 'payment', 'technical', 'other')
- priority: ENUM('low', 'medium', 'high', 'urgent')
- status: ENUM('open', 'in_progress', 'waiting_customer', 'resolved', 'closed')
- assigned_to: INT FOREIGN KEY (admin/staff)
```

#### `chat_messages`
AI chatbot conversation history.

```sql
- id: INT PRIMARY KEY
- session_id, user_id: VARCHAR/INT
- message, response: TEXT
- is_from_ai: TINYINT
- rating: TINYINT (1-5)
- human_requested: TINYINT
```

#### `human_chat_requests`
Human support escalation requests.

```sql
- id: INT PRIMARY KEY
- user_id, chat_message_id: INT
- status: ENUM('pending', 'in_progress', 'completed', 'cancelled')
- assigned_to: INT FOREIGN KEY
- started_at, ended_at: DATETIME
```

#### `activity_log`
User activity tracking.

```sql
- id: INT PRIMARY KEY
- user_id: INT FOREIGN KEY
- action, description: VARCHAR/TEXT
- ip_address, user_agent: VARCHAR
- created_at: TIMESTAMP
```

#### `notifications`
User notifications.

```sql
- id: INT PRIMARY KEY
- user_id: INT FOREIGN KEY
- title, message: VARCHAR/TEXT
- type: ENUM('info', 'success', 'warning', 'error', 'order', 'tender', 'ticket')
- link: VARCHAR
- is_read: TINYINT
```

---

## User Authentication System

### Registration

1. User fills registration form (name, email, phone, company, password)
2. Password is hashed using `password_hash()` with `PASSWORD_DEFAULT`
3. User record created with role 'customer' and status 'active'
4. Welcome notification sent
5. Activity logged

### Login

1. User submits email and password
2. System validates credentials against database
3. Session variables set (`user_id`, `email`, `first_name`, `last_name`, `role`, `logged_in`)
4. localStorage updated for JavaScript persistence
5. Last login timestamp updated
6. Activity logged
7. Redirect to dashboard

### Logout

1. User clicks logout
2. Session destroyed
3. localStorage cleared
4. Activity logged
5. Redirect to home page

### Security Features

- Passwords hashed with PHP's `password_hash()`
- Session-based authentication
- Input sanitization on all forms
- SQL injection prevention via PDO prepared statements
- Activity logging for security monitoring

---

## Dashboard Features

### User Dashboard (`dashboard.php`)

After login, users access their personalized dashboard with:

1. **Welcome Section**: Personalized greeting with user name
2. **Quick Actions**: Shortcuts to key features
3. **Recent Orders**: Latest 5 orders with status
4. **Active Tenders**: Current tender requests
5. **Support Tickets**: Open support tickets
6. **Notifications**: Unread notifications count

### Dashboard Sections

| Section | Description |
|---------|-------------|
| Orders | View order history, track status |
| Tenders | Submit and track tender requests |
| Tickets | Create and manage support tickets |
| Profile | Update personal information |

---

## Order Management

### Placing Orders

1. Browse products on main site
2. Add products to cart (or use Quick Order)
3. Cart stored in session/database
4. Checkout process
5. Order created with 'pending' status
6. Confirmation shown with order number

### Order Status Flow

```
pending → processing → shipped → delivered
    ↓
cancelled
```

### Order Tracking

- Users can view all their orders
- Status updates visible in real-time
- Order history preserved indefinitely

---

## Tender Request System

### Submitting Tenders

Tenders are formal requests for bulk quotes, typically for large orders.

1. User must be logged in
2. Click "Submit Tender" (login check enforced)
3. Fill tender form:
   - Title (required)
   - Description (required)
   - Category
   - Quantity needed
   - Budget range
   - Deadline
4. Submit generates unique tender number (e.g., TND-2024-0001)
5. Status defaults to 'pending'
6. Admin notified

### Tender Status Flow

```
pending → reviewed → quoted → accepted → closed
    ↓              ↓
  rejected       closed
```

### Tender Management (Admin)

- View all tenders
- Update status
- Add admin notes
- Set quote amount
- Contact user

---

## AI Chatbot System

### How It Works

The AI chatbot provides 24/7 automated assistance using keyword-based responses.

### Features

1. **Floating Widget**: Always visible on page
2. **Quick Responses**: Pre-defined answers to common questions
3. **Product Search**: Can find products in catalog
4. **Order Status**: Can check order information
5. **Human Escalation**: Option to talk to a real agent

### Response Categories

| Category | Keywords | Example |
|----------|----------|---------|
| Greetings | hello, hi, hey | Welcome! How can I help? |
| Products | product, buy, price | Browse our products... |
| Orders | order, track, status | I can help track your order |
| Tenders | tender, quote, bulk | Submit a tender request... |
| Contact | contact, phone, email | Contact us at... |
| Support | help, support, issue | Let me connect you to support |
| Company | about, aluora, company | Aluora GSL is... |

### Rating System

- Users can rate AI responses (1-5 stars)
- Feedback stored for improvement

---

## Human Support Referral

### When to Use

- AI couldn't resolve the issue
- Complex technical problem
- User prefers human interaction

### Process

1. User clicks "Talk to Human" in chat
2. System creates human chat request
3. Admin notified in admin panel
4. Staff member accepts/assigns chat
5. Real-time communication via ticket system
6. Chat marked complete when resolved

### Admin View

- View all pending human requests
- Assign to staff
- View chat history
- Mark as completed

---

## Admin Panel

### Access

URL: `admin/index.php`
- Admin role required
- Separate login from main site

### Features

#### Dashboard
- Total users, orders, tenders, tickets
- Revenue statistics
- Recent activity
- Charts and analytics

#### User Management
- View all users
- Edit user details
- Change user status (active/suspended)
- View user activity

#### Product Management
- Add/Edit/Delete products
- Manage categories
- Set featured products
- Update stock

#### Order Management
- View all orders
- Update order status
- Process payments
- View order details

#### Tender Management
- View all tenders
- Update tender status
- Add quotes
- Contact applicants

#### Ticket Management
- View all tickets
- Assign to staff
- Reply to tickets
- Resolve issues

#### Settings
- Site configuration
- Contact information

---

## API Endpoints

### Products API (`api/products.php`)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `api/products.php` | GET | List products (supports ?category=&search=) |
| `api/products.php?id=` | GET | Get single product |

### Authentication API (`auth/`)

| File | Method | Description |
|------|--------|-------------|
| `auth/login.php` | POST | User login |
| `auth/register.php` | POST | User registration |

### AJAX Actions (in `index.php`)

| Action | Method | Description |
|--------|--------|-------------|
| `submit_tender` | POST | Submit tender request |
| `quick_order` | POST | Place quick order |
| `newsletter` | POST | Subscribe to newsletter |
| `chat_message` | POST | Send chat message |
| `rate_chat` | POST | Rate chat response |

---

## Installation Guide

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB
- Apache/Nginx web server

### Steps

1. **Upload Files**
   - Upload all files to web server
   - Document root should be the project folder

2. **Create Database**
   - Create new MySQL database
   - Import `database/schema.sql`

3. **Configure Database**
   - Edit `config.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set Permissions**
   - Ensure `uploads/` folder is writable (for future use)

5. **Test**
   - Access the website
   - Try logging in with admin credentials:
     - Email: admin@aluoragsl.com
     - Password: admin123

---

## Usage Guide

### For Customers

1. **Browse Products**
   - Visit the homepage
   - Use category navigation
   - Search for specific items

2. **Register/Login**
   - Click "Login" or "Register"
   - Fill in your details
   - Verify email (if enabled)

3. **Place Orders**
   - Find product
   - Use Quick Order or add to cart
   - Checkout

4. **Submit Tenders**
   - Login required
   - Click "Submit Tender"
   - Fill in details
   - Wait for admin response

5. **Get Support**
   - Use AI chatbot
   - Or submit support ticket
   - Or request human agent

### For Admins

1. **Access Admin Panel**
   - Go to admin/index.php
   - Login with admin credentials

2. **Manage Products**
   - Add new products
   - Update inventory
   - Set featured items

3. **Process Orders**
   - View pending orders
   - Update status
   - Process payments

4. **Handle Tenders**
   - Review submissions
   - Provide quotes
   - Update status

5. **Support Users**
   - Check tickets
   - Respond to inquiries
   - Handle human chat requests

---

## File Structure

```
Aluora_GSL/
├── index.php              # Main homepage
├── dashboard.php          # User dashboard
├── products.php           # Product catalog
├── about.php              # About page
├── contact.php            # Contact page
├── logout.php             # Logout handler
├── config.php             # Database & functions
├── css/
│   ├── style.css         # Main styles
│   ├── enhanced.css      # Advanced animations
│   ├── dashboard.css     # Dashboard styles
│   └── admin.css         # Admin panel styles
├── js/
│   ├── main.js           # Core functionality
│   ├── enhanced.js       # Advanced features
│   ├── dashboard.js      # Dashboard JS
│   └── admin.js          # Admin JS
├── auth/
│   ├── login.php         # Login handler
│   └── register.php      # Registration handler
├── api/
│   └── products.php      # Products API
├── admin/
│   └── index.php         # Admin panel
├── database/
│   └── schema.sql        # Database schema
└── SYSTEM_DOCUMENTATION.md
```

---

## Support & Contact

- **Email**: aluoragsl@gmail.com
- **Phone**: +254-715-173-207
- **Website**: www.aluoragsl.com

---

*Document Version: 1.0*
*Last Updated: 2024*
*For: Aluora General Suppliers Limited*
