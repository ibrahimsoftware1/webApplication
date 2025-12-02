# Quick Start Guide

## Prerequisites

- Node.js 18+ installed
- Backend API running on `http://localhost:8000`

## Setup Steps

1. **Navigate to frontend directory:**
   ```bash
   cd frontend
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Create environment file:**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` if your backend runs on a different port:
   ```
   VITE_API_BASE_URL=http://localhost:8000/api
   ```

4. **Start development server:**
   ```bash
   npm run dev
   ```

5. **Open browser:**
   Navigate to `http://localhost:3000`

## First Steps

1. **Register a new account** at `/register`
2. **Login** at `/login`
3. **View your feed** at `/feed`
4. **Start chatting** at `/chat`
5. **Add friends** at `/friends`
6. **Explore community** at `/community`

## Testing

Run unit tests:
```bash
npm run test:unit
```

Run e2e tests (requires dev server running):
```bash
npm run test:e2e:open
```

## Building for Production

```bash
npm run build
```

The production build will be in the `dist/` folder.

## Troubleshooting

### CORS Issues
If you encounter CORS errors, ensure your Laravel backend has CORS configured in `config/cors.php` to allow requests from `http://localhost:3000`.

### API Connection Issues
- Verify your backend is running on the correct port
- Check the `VITE_API_BASE_URL` in your `.env` file
- Ensure your backend API endpoints match the expected structure

### Build Errors
- Clear `node_modules` and reinstall: `rm -rf node_modules && npm install`
- Clear Vite cache: `rm -rf node_modules/.vite`
