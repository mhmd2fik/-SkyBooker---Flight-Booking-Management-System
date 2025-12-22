# ✈️ SkyBooker - Flight Booking & Management System

A modern, premium **Full-Stack Web Application** built with **PHP**, **MySQL**, and **Custom CSS**. This system enables Airline Companies to manage flights and passengers, while allowing Passengers to search, book, and communicate with airlines through an elegant dark-themed interface.

![SkyBooker](https://img.shields.io/badge/Version-2.0-blue) ![PHP](https://img.shields.io/badge/PHP-8.0+-purple) ![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange) ![License](https://img.shields.io/badge/License-MIT-green)

---

## 🌟 Features Overview

### 🏢 For Airline Companies

| Feature | Description |
|---------|-------------|
| **Flight Management** | Create, edit, and cancel flights with unique IDs, routes, fees, and schedules |
| **Passenger Management** | View pending (cash) and confirmed (paid) passengers for each flight |
| **Booking Approval** | Accept or reject cash payment bookings from passengers |
| **Financial Control** | Add balance directly to passenger accounts via email lookup |
| **Flight Cancellation** | Cancel flights with automatic refund to all confirmed passengers |
| **Company Branding** | Upload company logo, manage bio, address, and contact details |
| **Real-time Messaging** | Chat directly with passengers about bookings and inquiries |

### 👤 For Passengers

| Feature | Description |
|---------|-------------|
| **Flight Search** | Search available flights by origin and destination cities |
| **Smart Booking** | Book flights with two payment options (Account Balance or Cash) |
| **Booking Management** | View pending, confirmed, and cancelled bookings in dashboard |
| **Cancel & Refund** | Cancel bookings anytime with automatic balance refund |
| **Digital Wallet** | Manage account balance for instant flight payments |
| **Document Upload** | Upload profile photo and passport image |
| **Airline Messaging** | Direct chat with airline companies |

### 💬 Common Features

- **Secure Authentication** - Role-based login (Company/Passenger)
- **Internal Messaging** - Real-time chat between passengers and airlines
- **Modern Dark UI** - Premium glassmorphism design with animations
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Profile Pictures in Nav** - Personalized navigation with user avatars

---

## 🎨 UI Design

The application features a **premium dark theme** with:

- Deep black background (`#0a0a0f`) with blue glow accents
- Glassmorphism cards and forms with blur effects
- Gradient buttons with hover glow animations
- Animated status badges with pulse effects
- Stats cards on dashboards
- Modern chat interface with message bubbles
- Hero sections with background images
- Profile pictures or initials in navigation bar

---

## 🛠️ Installation & Setup

Follow these step-by-step instructions to run the project on your local machine.

### Step 1: Download and Install XAMPP

1. Go to [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2. Download XAMPP for your operating system (Windows/Mac/Linux)
3. Run the installer and follow the installation wizard
4. Install to the default location (e.g., `C:\xampp` on Windows)
5. Once installed, open the **XAMPP Control Panel**
6. Click **Start** for both **Apache** and **MySQL** modules
7. Both should show green "Running" status

### Step 2: Prepare the Project Folder

1. Navigate to your XAMPP installation folder:
   - **Windows:** `C:\xampp\htdocs\`
   - **Mac:** `/Applications/XAMPP/htdocs/`
   - **Linux:** `/opt/lampp/htdocs/`

2. Create a new folder named `flight-system`

3. Copy ALL project files into this folder:
   ```
   flight-system/
   ├── css/
   │   └── style.css
   ├── js/
   │   └── main.js
   ├── uploads/           ← Create this folder!
   │   └── .gitkeep
   ├── db.php
   ├── index.php
   ├── register.php
   ├── logout.php
   ├── company_home.php
   ├── company_profile.php
   ├── company_messages.php
   ├── add_flight.php
   ├── flight_details.php
   ├── passenger_home.php
   ├── passenger_profile.php
   ├── passenger_messages.php
   ├── search_flight.php
   ├── flight_info.php
   ├── process_balance.php
   └── database.sql
   ```

4. **Important:** Make sure the `uploads/` folder exists and has write permissions

### Step 3: Database Setup (phpMyAdmin)

1. Open your browser and go to: **http://localhost/phpmyadmin**

2. **Create Database:**
   - Click **"New"** in the left sidebar
   - Enter database name: `flight_booking`
   - Click **"Create"**

3. **Import Tables:**
   - Click on `flight_booking` in the left sidebar
   - Click the **"SQL"** tab at the top
   - Copy and paste the following SQL code:

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('company', 'passenger') NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    bio TEXT,
    address TEXT,
    logo_path VARCHAR(255),
    passport_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    flight_name VARCHAR(100),
    flight_unique_id VARCHAR(50) UNIQUE,
    itinerary TEXT,
    fees DECIMAL(10,2),
    max_passengers INT,
    start_time DATETIME,
    end_time DATETIME,
    status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
    FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    passenger_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    msg TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
```

4. Click **"Go"** to execute the SQL and create all tables

---

## 🚀 How to Run the Application

1. Make sure **Apache** and **MySQL** are running in XAMPP Control Panel

2. Open your browser and navigate to:
   ```
   http://localhost/flight-system/index.php
   ```
   or simply:
   ```
   http://localhost/flight-system/
   ```

---

## 📖 User Guide

### Getting Started as a Company

1. **Register:** Click "Register" and select "Company" as your role
2. **Login:** Use your email and password to access the dashboard
3. **Add Flight:** Go to "Add Flight" and create your first flight
   - Enter flight name, route (e.g., "Cairo → Dubai")
   - Set fees, max passengers, departure and arrival times
4. **Manage Bookings:** View flight details to see passenger bookings
   - Accept or reject pending (cash) bookings
5. **Add Balance:** Go to Profile to add balance to passenger accounts
6. **Messages:** Communicate with passengers through the messaging system

### Getting Started as a Passenger

1. **Register:** Click "Register" and select "Passenger" as your role
2. **Login:** Access your personalized dashboard
3. **Search Flights:** Use the search feature to find available flights
4. **Book Flight:** Click "Book Now" on any flight
   - **Pay from Account:** Instant confirmation (requires balance)
   - **Pay at Company:** Marked as pending (requires company approval)
5. **Manage Bookings:** View all your bookings in the dashboard
   - Cancel bookings anytime (refund if already paid)
6. **Messages:** Chat with airlines about your bookings

---

## 📂 File Structure Explained

| File | Description |
|------|-------------|
| `db.php` | Database connection configuration |
| `index.php` | Login page with hero section |
| `register.php` | User registration (Company/Passenger) |
| `logout.php` | Session destruction and logout |
| `company_home.php` | Company dashboard with flight list and stats |
| `company_profile.php` | Company profile editor + add balance to passengers |
| `company_messages.php` | Company messaging interface |
| `add_flight.php` | Create new flight form |
| `flight_details.php` | View flight passengers, accept/reject bookings |
| `passenger_home.php` | Passenger dashboard with booking cards |
| `passenger_profile.php` | Passenger profile and document upload |
| `passenger_messages.php` | Passenger messaging interface |
| `search_flight.php` | Flight search with hero section |
| `flight_info.php` | Flight booking page with payment options |
| `process_balance.php` | Backend for balance operations |
| `css/style.css` | Complete dark theme styling |
| `js/main.js` | jQuery animations and interactions |
| `database.sql` | SQL schema for database setup |

---

## 🔄 Application Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER REGISTRATION                         │
│                    (Company or Passenger)                        │
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              ▼                               ▼
┌─────────────────────────┐     ┌─────────────────────────┐
│     COMPANY FLOW        │     │    PASSENGER FLOW       │
├─────────────────────────┤     ├─────────────────────────┤
│ 1. Create Flights       │     │ 1. Search Flights       │
│ 2. View Bookings        │     │ 2. Book Flight          │
│ 3. Accept/Reject        │◄───►│ 3. Pay (Account/Cash)   │
│ 4. Add Balance          │     │ 4. View Bookings        │
│ 5. Cancel Flights       │     │ 5. Cancel Bookings      │
│ 6. Message Passengers   │◄───►│ 6. Message Airlines     │
└─────────────────────────┘     └─────────────────────────┘
```

---

## 💳 Payment System

### Account Payment (Instant)
- Passenger pays from their digital wallet
- Balance is deducted immediately
- Booking status: **Confirmed** ✅

### Cash Payment (Pending)
- Passenger chooses to pay at company
- Booking status: **Pending** ⏳
- Company must manually accept/reject
- Upon acceptance: Status → **Confirmed** ✅

### Refund System
- Passenger cancels confirmed booking → Balance refunded
- Company cancels flight → All passengers refunded automatically

---

## ⚠️ Troubleshooting

### Common Issues and Solutions

| Problem | Solution |
|---------|----------|
| **"Duplicate Email" Error** | Email already exists. Use a different email or delete the user from phpMyAdmin |
| **Images Not Appearing** | Ensure `uploads/` folder exists with write permissions |
| **Database Connection Error** | Check `db.php` credentials match your XAMPP settings |
| **Blank Page** | Check Apache error logs in `C:\xampp\apache\logs\error.log` |
| **Session Issues** | Clear browser cookies or restart Apache |

### Default Database Credentials (db.php)
```php
$host = "localhost";
$user = "root";
$pass = "";  // Empty by default in XAMPP
$db = "flight_booking";
```

---

## 🔧 Technical Requirements

- **PHP:** 8.0 or higher
- **MySQL:** 5.7 or higher
- **Web Server:** Apache (via XAMPP)
- **Browser:** Chrome, Firefox, Safari, Edge (modern versions)
- **No frameworks required** - Pure PHP, HTML, CSS, JavaScript

---

## 📱 Responsive Design

The application is fully responsive and works on:
- 🖥️ Desktop (1200px+)
- 💻 Laptop (992px - 1199px)
- 📱 Tablet (768px - 991px)
- 📱 Mobile (< 768px)

---

## 🎯 Future Enhancements

- [ ] Email notifications for booking updates
- [ ] Flight seat selection
- [ ] Multiple payment gateways
- [ ] Admin dashboard for system management
- [ ] Flight reviews and ratings
- [ ] Booking history export (PDF)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

## 👨‍💻 Author

Built with ❤️ using PHP, MySQL, and Modern CSS

---

**Happy Flying! ✈️**
