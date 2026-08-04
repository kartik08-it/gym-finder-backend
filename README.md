# 🏋️ GymFinder Backend

> A production-ready Laravel 12 REST API powering **GymFinder** — a platform to discover, compare, and join gyms based on location, pricing, amenities, and verified reviews.

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.3-blue)
![MySQL](https://img.shields.io/badge/MySQL-8-orange)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📖 Overview

GymFinder is a modern gym discovery platform inspired by applications like Goibibo and Booking.com.

Users can:

- 🔍 Search nearby gyms
- 📍 Filter by distance
- 💰 Compare membership pricing
- ⭐ Read verified reviews
- ❤️ Save favorite gyms
- 💳 Purchase memberships online
- 📱 Receive booking notifications

The backend is built with **Laravel 12** following enterprise architecture, SOLID principles, and REST API best practices.

---

## ✨ Features

### Authentication

- Email & Password Login
- Google OAuth
- Phone OTP Login
- Email Verification
- Password Reset
- Sanctum Authentication

### Customer

- Search Nearby Gyms
- Compare Gyms
- Advanced Filters
- Membership Booking
- Wishlist
- Ratings & Reviews
- Payment History

### Gym Owner

- Register Gym
- Manage Membership Plans
- Upload Images & Videos
- Manage Trainers
- Analytics Dashboard
- Review Management

### Admin

- User Management
- Gym Approval
- Coupon Management
- Review Moderation
- Revenue Dashboard
- Reports & Analytics

---

## 🛠 Tech Stack

### Backend

- Laravel 12
- PHP 8.3
- MySQL
- Redis
- Laravel Sanctum
- Laravel Horizon
- Meilisearch

### Services

- Razorpay
- Cloudinary
- Firebase Cloud Messaging
- OpenStreetMap
- Nominatim API

---

## 📂 Project Structure

```
app
├── Actions
├── Console
├── Events
├── Exceptions
├── Helpers
├── Http
│   ├── Controllers
│   ├── Middleware
│   ├── Requests
│   └── Resources
├── Jobs
├── Listeners
├── Mail
├── Models
├── Notifications
├── Policies
├── Repositories
├── Services
└── Traits

database
routes
storage
tests
```

---

## 🏗 Architecture

```
Client
     │
     ▼
REST API
     │
     ▼
Controllers
     │
     ▼
Services
     │
     ▼
Repositories
     │
     ▼
Models
     │
     ▼
MySQL
```

---

## 🚀 Getting Started

### Requirements

- PHP 8.3+
- Composer
- MySQL 8+
- Redis
- Node.js 20+

---

### Installation

Clone the repository

```bash
git clone https://github.com/yourusername/gymfinder-backend.git
```

Go inside the project

```bash
cd gymfinder-backend
```

Install dependencies

```bash
composer install
```

Copy environment file

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

Configure your database inside `.env`

Run migrations

```bash
php artisan migrate
```

(Optional)

```bash
php artisan db:seed
```

Start development server

```bash
php artisan serve
```

---

## ⚙ Environment Variables

Create a `.env` file.

Example:

```env
APP_NAME=GymFinder
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gymfinder
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis

CLOUDINARY_URL=

RAZORPAY_KEY=
RAZORPAY_SECRET=

FCM_SERVER_KEY=
```

---

## 📡 API Version

```
/api/v1
```

Example endpoints

| Method | Endpoint | Description |
|----------|----------|-------------|
| POST | `/auth/register` | Register User |
| POST | `/auth/login` | Login |
| GET | `/gyms` | List Gyms |
| GET | `/gyms/{id}` | Gym Details |
| POST | `/reviews` | Add Review |
| POST | `/bookings` | Book Membership |

---

## 🧪 Testing

Run the test suite

```bash
php artisan test
```

---

## 📦 Deployment

The application is deployment-ready with:

- Docker
- Nginx
- Supervisor
- Redis
- Queue Workers

---

## 📋 Roadmap

- [x] Authentication
- [x] Gym Management
- [x] Membership Plans
- [x] Reviews
- [x] Favorites
- [x] Payments
- [x] Notifications
- [ ] AI Recommendations
- [ ] AI Review Summary
- [ ] Mobile API
- [ ] QR Check-in

---

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository
2. Create a feature branch

```bash
git checkout -b feature/new-feature
```

3. Commit your changes

```bash
git commit -m "Add new feature"
```

4. Push

```bash
git push origin feature/new-feature
```

5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Author

**Kartik Upadhayay**

Full Stack Developer

Laravel • React • Next.js • TypeScript • MySQL

---

⭐ If you found this project useful, please consider giving it a star.
