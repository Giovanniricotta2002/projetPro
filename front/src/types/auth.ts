// types/auth.ts
export interface User {
  id: string
  username: string
  email: string
  roles?: string[]
  createdAt?: string
  updatedAt?: string
}

export interface LoginData {
  login: string
  password: string
  csrfToken: string
}

export interface RegisterData {
  username: string
  email: string
  password: string
  csrfToken: string
}

export interface ChangePasswordData {
  currentPassword: string
  newPassword: string
  csrfToken: string
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  errors?: Record<string, string[]>
}

export interface AuthState {
  user: User | null
  isLoading: boolean
  error: string
  isInitialized: boolean
}

// Types pour les réponses d'authentification
export interface AuthResponse {
  user: User
  message?: string
}

export interface RefreshResponse {
  user: User
  expiresIn?: number
}

// Types pour les erreurs d'API
export interface ValidationError {
  field: string
  message: string
}

export interface ApiError {
  code: string
  message: string
  details?: ValidationError[]
}
