# 💬 Chatting API - Social Networking Platform

A comprehensive **real-time social networking and chatting application** built with Laravel (Backend) and Vue.js (Frontend). Connect with friends, share posts, and chat in real-time with a modern, responsive interface.

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Vue.js-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
</p>

## 📖 About

This project is a full-stack social networking platform that enables users to:
- **Chat in real-time** with friends and community members
- **Build connections** through friend requests and following systems
- **Share content** via posts and social feed
- **Manage profiles** with customizable avatars and bios
- **Admin dashboard** for platform management

Perfect for communities, teams, or any group that needs seamless communication and social interaction.

---

## ✨ Features

### 🔐 Authentication & Security
- User registration and login
- Email verification system
- Password reset functionality
- Secure token-based authentication (Laravel Sanctum)
- Role-based access control (Admin, User roles)

### 💬 Real-Time Chat & Messaging
- **One-on-one conversations** with friends
- **Group conversations** with multiple participants
- **Real-time messaging** using Laravel Broadcasting (WebSockets)
- **Typing indicators** - see when someone is typing
- **Message status** - read receipts and delivery status
- **Message management** - edit and delete messages
- **Community chat** - public chat rooms

### 👥 Friends & Social Network
- **Friend requests** - send, accept, or reject friend requests
- **Friends list** - manage your network of friends
- **Follow system** - follow users to see their posts
- **Friendship status** - check connection status with any user
- **Remove friends** - unfriend or cancel pending requests

### 📱 Social Feed & Posts
- **Create posts** - share text, images, and updates
- **Social feed** - see posts from people you follow
- **Explore page** - discover new content and users
- **User posts** - view posts from specific users
- **Post interactions** - like, comment, and engage

### 👤 User Profiles
- **Customizable profiles** - username, bio, and avatar
- **Profile management** - update information anytime
- **Avatar upload** - set and change profile pictures
- **Password management** - change password securely
- **Account deletion** - delete account when needed

### 🛡️ Admin Dashboard
- **User management** - view, ban, unban, and delete users
- **Role assignment** - assign admin roles to users
- **Dashboard statistics** - view platform metrics
- **Community management** - moderate content and users
- **User details** - detailed view of user information

### 🎨 Frontend Features
- **Modern Vue.js 3** interface with Composition API
- **Responsive design** - works on desktop, tablet, and mobile
- **Dark/Light theme** - toggle between themes
- **Multi-language support** - i18n with English, Arabic, and Kurdish
- **Real-time updates** - instant UI updates via WebSockets
- **Toast notifications** - user-friendly feedback
- **Form validation** - comprehensive input validation

---

## 🎯 User Stories - Why Use This Project?

### For Individual Users

**As a user, I want to:**
- ✅ **Connect with friends** - Send friend requests and build my social network
- ✅ **Chat instantly** - Have real-time conversations without page refreshes
- ✅ **Share my thoughts** - Post updates and share content with my network
- ✅ **Discover people** - Find and connect with new people in the community
- ✅ **Manage my profile** - Customize my profile with photos and bio
- ✅ **Stay connected** - See when friends are online and available to chat
- ✅ **Privacy control** - Control who can see my posts and contact me

### For Communities & Teams

**As a community manager, I want to:**
- ✅ **Moderate content** - Admin dashboard to manage users and posts
- ✅ **Monitor activity** - View statistics and platform health
- ✅ **Manage members** - Ban, unban, or remove problematic users
- ✅ **Assign roles** - Give trusted members admin privileges
- ✅ **Track growth** - See user registration and engagement metrics

### For Developers

**As a developer, I want to:**
- ✅ **Learn full-stack development** - See Laravel + Vue.js integration
- ✅ **Understand real-time features** - Learn WebSocket implementation
- ✅ **Study API design** - RESTful API with proper authentication
- ✅ **See best practices** - Clean code, proper structure, and documentation
- ✅ **Extend functionality** - Easy to add new features

---

## 🛠️ Technology Stack

### Backend
- **Laravel 11** - PHP framework
- **Laravel Sanctum** - API authentication
- **Laravel Broadcasting** - Real-time events (WebSockets)
- **MySQL** - Database
- **Laravel Reverb** - WebSocket server
- **Spatie Laravel Permission** - Role and permission management

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Vite** - Build tool and dev server
- **Pinia** - State management
- **Vue Router** - Client-side routing
- **Axios** - HTTP client
- **Tailwind CSS** - Utility-first CSS framework
- **Echo.js** - Laravel Echo for WebSocket connections

### Development Tools
- **Cypress** - End-to-end testing
- **Vitest** - Unit testing
- **ESLint** - Code linting
- **Prettier** - Code formatting

---

## 📋 Prerequisites

Before you begin, ensure you have:
- **PHP 8.2+** installed
- **Composer** - PHP dependency manager
- **Node.js 18+** and **npm**
- **MySQL** or compatible database
- **Git** for version control

---

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/ibrahimsoftware1/webApplication.git
cd webApplication
```

### 2. Backend Setup (Laravel)

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start Laravel development server
php artisan serve
```

### 3. Frontend Setup (Vue.js)

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Copy environment file (if exists)
cp .env.example .env

# Start development server
npm run dev
```

### 4. WebSocket Server (Laravel Reverb)

```bash
# Start Reverb server (in a separate terminal)
php artisan reverb:start
```

### 5. Email Configuration (Optional)

For email verification, configure your `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

Or use Mailpit for local development (see `MAILPIT_SETUP.md`).

---

## 📡 API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout
- `POST /api/forgot-password` - Request password reset
- `POST /api/reset-password` - Reset password
- `GET /api/email/verify/{id}/{hash}` - Verify email
- `POST /api/email/resend` - Resend verification email

### Profile
- `GET /api/profile` - Get user profile
- `PUT /api/profile/update-profile` - Update profile
- `POST /api/profile/avatar` - Upload avatar
- `DELETE /api/profile/avatar` - Remove avatar
- `POST /api/profile/change-password` - Change password
- `GET /api/profile/conversations` - Get user conversations
- `DELETE /api/profile/delete-account` - Delete account

### Friends
- `GET /api/friends` - Get all friends
- `GET /api/friends/requests` - Get friend requests
- `POST /api/friends/request` - Send friend request
- `POST /api/friends/accept/{id}` - Accept friend request
- `POST /api/friends/reject/{id}` - Reject friend request
- `DELETE /api/friends/{id}` - Remove friend
- `GET /api/friends/status/{userId}` - Check friendship status

### Conversations
- `GET /api/conversations` - Get all conversations
- `POST /api/conversations` - Create conversation
- `GET /api/conversations/{id}` - Get conversation details
- `PUT /api/conversations/{id}` - Update conversation
- `DELETE /api/conversations/{id}` - Delete conversation
- `GET /api/conversations/{id}/messages` - Get messages

### Messages
- `POST /api/messages/conversations/{id}` - Send message
- `PUT /api/messages/{id}` - Update message
- `DELETE /api/messages/{id}` - Delete message
- `POST /api/messages/{id}/read` - Mark as read
- `POST /api/messages/conversations/{id}/typing` - Typing indicator

### Admin
- `GET /api/admin/dashboard` - Admin dashboard stats
- `GET /api/admin/users` - List all users
- `GET /api/admin/users/{id}` - User details
- `POST /api/admin/users/{id}/ban` - Ban user
- `POST /api/admin/users/{id}/unban` - Unban user
- `POST /api/admin/users/{id}/assign-role` - Assign role
- `DELETE /api/admin/users/{id}` - Delete user

---

## 🧪 Testing

### Backend Tests
```bash
php artisan test
```

### Frontend Tests
```bash
cd frontend

# Unit tests
npm run test:unit

# E2E tests
npm run test:e2e
```

---

## 👥 Team

This project was developed collaboratively by:

- **Ibrahim** - Authentication & Email System
- **Daban** - Friends System & Chat & Messaging
- **Rasa** - User Profiles & Admin Dashboard
- **Abdullah** - [Feature assignment]
- **Ahmad** - [Feature assignment]

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📞 Support

For support, email support@example.com or open an issue in the repository.

---

## 🎉 Acknowledgments

- Laravel community for the amazing framework
- Vue.js team for the progressive framework
- All contributors who helped build this project

---

**Built with ❤️ by the development team**
