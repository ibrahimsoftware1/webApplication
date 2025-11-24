# Architecture Diagram

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         Frontend (Vue 3)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Router     │  │    Pinia     │  │   Services   │      │
│  │  (Vue Router)│  │   (State)    │  │   (Axios)    │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                 │               │
│         └──────────────────┼─────────────────┘               │
│                            │                                 │
│  ┌─────────────────────────┴─────────────────────────┐      │
│  │              Components Layer                      │      │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐        │      │
│  │  │  Layout  │  │    UI    │  │  Pages   │        │      │
│  │  │ Components│  │ Components│  │ (Views)  │        │      │
│  │  └──────────┘  └──────────┘  └──────────┘        │      │
│  └───────────────────────────────────────────────────┘      │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            │ HTTP/HTTPS
                            │
┌───────────────────────────▼─────────────────────────────────┐
│                    Backend API (Laravel)                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ Controllers  │  │   Models     │  │  Database    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└────────────────────────────────────────────────────────────┘
```

## Component Hierarchy

```
App.vue
└── RouterView
    ├── BaseLayout
    │   ├── AppHeader
    │   │   ├── Navigation
    │   │   └── UserMenu
    │   ├── Main Content (RouterView)
    │   │   ├── HomeView
    │   │   ├── LoginView
    │   │   ├── RegisterView
    │   │   ├── ProjectsView
    │   │   │   ├── ProjectCard
    │   │   │   └── ProjectForm
    │   │   ├── ProjectDetailView
    │   │   ├── TasksView
    │   │   │   ├── KanbanBoard
    │   │   │   └── TaskForm
    │   │   └── TaskDetailView
    │   └── AppFooter
    └── Toast (Global)
```

## Data Flow

```
User Action
    │
    ├─→ Component
    │       │
    │       ├─→ Store Action (Pinia)
    │       │       │
    │       │       └─→ API Service (Axios)
    │       │               │
    │       │               └─→ Backend API
    │       │                       │
    │       │                       └─→ Response
    │       │                               │
    │       └─→ Store Mutation              │
    │               │                        │
    │               └─→ State Update         │
    │                       │                 │
    │                       └─→ Component     │
    │                               │         │
    │                               └─→ UI Update
    │
    └─→ Optimistic Update (for better UX)
```

## State Management (Pinia)

```
┌─────────────────────────────────────────┐
│           Pinia Stores                   │
├─────────────────────────────────────────┤
│  auth                                    │
│  ├── state: user, token, isAuthenticated│
│  ├── getters: userName, userEmail        │
│  └── actions: login, logout, fetchProfile│
├─────────────────────────────────────────┤
│  projects                                │
│  ├── state: projects, currentProject     │
│  ├── getters: hasProjects, projectById  │
│  └── actions: fetch, create, update, del│
├─────────────────────────────────────────┤
│  tasks                                   │
│  ├── state: tasks, boardColumns         │
│  ├── getters: hasTasks, taskById        │
│  └── actions: fetch, create, update, move│
└─────────────────────────────────────────┘
```

## Routing Structure

```
/ (HomeView)
├── /login (LoginView)
├── /register (RegisterView)
├── /projects (ProjectsView)
│   ├── /projects/create (ProjectFormView)
│   ├── /projects/:id (ProjectDetailView)
│   └── /projects/:id/edit (ProjectFormView)
├── /projects/:projectId/tasks (TasksView)
├── /tasks/:id (TaskDetailView)
└── /profile (ProfileView)
```

## Testing Strategy

```
┌─────────────────────────────────────────┐
│         Testing Pyramid                 │
├─────────────────────────────────────────┤
│                                         │
│              E2E Tests                  │
│         (Cypress - 3+ journeys)         │
│                                         │
│         ┌───────────────┐               │
│         │  Unit Tests   │               │
│         │ (Vitest - 10+)│               │
│         └───────────────┘               │
│                                         │
└─────────────────────────────────────────┘
```

## Build & Deployment

```
Development
    │
    ├─→ npm run dev
    │       └─→ Vite Dev Server (HMR)
    │
Production
    │
    ├─→ npm run build
    │       └─→ Vite Build
    │           └─→ dist/ (Static Files)
    │
    └─→ Deploy to:
            ├─→ Static Hosting (Netlify, Vercel)
            ├─→ CDN
            └─→ Server (Nginx)
```

## Security Considerations

- Token-based authentication
- XSS protection via Vue's template system
- CSRF protection via SameSite cookies
- Input validation on client and server
- Secure HTTP headers (configured on backend)

## Performance Optimizations

1. **Code Splitting**: Route-level lazy loading
2. **Tree Shaking**: Unused code elimination
3. **Asset Optimization**: Image optimization, minification
4. **Caching**: Browser caching for static assets
5. **Optimistic UI**: Instant feedback, rollback on error

