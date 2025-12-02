# Chatting API Frontend

A modern social networking and chatting application built with Vue 3, Vite, Pinia, and Tailwind CSS.

## 🚀 Features

- **Real-time Chat**: Conversations and messaging with friends and community
- **Social Feed**: Share posts, photos, and updates with the community
- **Friends Management**: Send, accept, and manage friend requests
- **Follow System**: Follow and unfollow users
- **User Profiles**: View and manage user profiles
- **Community**: Discover and connect with users
- **Authentication**: Secure login and registration with form validation
- **Responsive Design**: Mobile-first, fully responsive UI
- **Accessibility**: ARIA labels, keyboard navigation, and focus management
- **State Management**: Pinia stores with persistence
- **Form Validation**: Yup schema validation
- **Toast Notifications**: User-friendly notifications
- **Theme Support**: Light/dark mode toggle
- **Code Splitting**: Route-level code splitting for optimal performance

## 📋 Prerequisites

- Node.js 18+ and npm
- Backend API running on `http://localhost:8000` (or configure via `.env`)

## 🛠️ Installation

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and set your API base URL if different from default.

3. **Start development server:**
   ```bash
   npm run dev
   ```

   The app will be available at `http://localhost:3000`

## 📜 Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint
- `npm run format` - Format code with Prettier
- `npm run test:unit` - Run unit tests with Vitest
- `npm run test:unit:ui` - Run unit tests with UI
- `npm run test:unit:coverage` - Run unit tests with coverage
- `npm run test:e2e` - Run Cypress e2e tests
- `npm run test:e2e:open` - Open Cypress test runner
- `npm run test` - Run all tests

## 🏗️ Project Structure

```
frontend/
├── src/
│   ├── assets/          # Static assets (CSS, images)
│   ├── components/       # Reusable components
│   │   ├── layout/      # Layout components (Header, Footer)
│   │   ├── posts/       # Post-related components
│   │   └── ui/          # UI kit components (Button, Input, etc.)
│   ├── composables/     # Vue composables
│   ├── router/          # Vue Router configuration
│   ├── services/        # API service layer
│   ├── stores/          # Pinia stores
│   └── views/           # Page components
│       ├── auth/        # Authentication pages
│       ├── chat/        # Chat/Conversation pages
│       ├── posts/       # Post/Feed pages
│       ├── friends/     # Friends management
│       ├── community/   # Community/Users
│       └── users/       # User profiles
├── cypress/             # E2E tests
├── tests/               # Unit tests
└── public/              # Public assets
```

## 🧪 Testing

### Unit Tests

Unit tests are written with Vitest and Vue Test Utils:

```bash
npm run test:unit
```

### E2E Tests

E2E tests are written with Cypress:

```bash
npm run test:e2e:open  # Interactive mode
npm run test:e2e       # Headless mode
```

## 🎨 UI Components

The project includes a complete UI kit:

- **Button**: Multiple variants (primary, secondary, danger, outline, ghost)
- **Input**: Text inputs with validation and error states
- **Select**: Dropdown selects with options
- **Modal**: Accessible modal dialogs
- **Card**: Content cards with header/footer slots
- **Badge**: Status badges with variants
- **Toast**: Notification toasts

All components are:
- Fully accessible (ARIA labels, keyboard navigation)
- Responsive
- Themeable
- Well-documented

## 🔐 Authentication

The app uses token-based authentication:
- Login/Register forms with validation
- Automatic token storage and retrieval
- Protected routes with navigation guards
- Automatic logout on 401 responses

## 📡 API Integration

API services are organized by domain:
- `auth.js` - Authentication endpoints
- `conversations.js` - Chat conversations
- `messages.js` - Messages
- `posts.js` - Social posts
- `friends.js` - Friends management
- `follows.js` - Follow/unfollow
- `users.js` - User profiles

All API calls use Axios with:
- Request interceptors for auth tokens
- Response interceptors for error handling
- Automatic token refresh handling

## 🎯 State Management

Pinia stores with persistence:
- **auth**: User authentication state
- **conversations**: Conversations list and current conversation
- **messages**: Messages by conversation
- **posts**: Posts and feed
- **friends**: Friends and friend requests
- **users**: Users and profiles
- **preferences**: User preferences (theme, view mode)

## 🚀 Deployment

### Build for Production

```bash
npm run build
```

The `dist/` folder contains the production build.

### Environment Variables

Set these in your production environment:
- `VITE_API_BASE_URL` - Your backend API URL

### CI/CD

GitHub Actions workflow runs on push/PR:
- Linting (ESLint)
- Code formatting (Prettier)
- Unit tests
- Coverage reports

## 📚 Architecture

### Component Architecture

- **Layout Components**: BaseLayout, AppHeader, AppFooter
- **Page Components**: Views organized by feature
- **UI Components**: Reusable, accessible components
- **Form Components**: Feature-specific forms

### Routing

- Route-level code splitting
- Navigation guards for authentication
- Lazy-loaded routes

### Performance Optimizations

- Route-level code splitting
- Lazy-loaded components
- Efficient state management

## 🔧 Configuration

### ESLint

ESLint is configured with Vue 3 and Prettier integration.

### Prettier

Code formatting is handled by Prettier with sensible defaults.

### Tailwind CSS

Utility-first CSS framework for rapid UI development.

## 📝 License

This project is part of a larger application.

## 🤝 Contributing

1. Follow the existing code style
2. Write tests for new features
3. Ensure accessibility standards
4. Update documentation as needed
