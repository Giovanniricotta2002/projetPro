// types/auth.ts
import type { Utilisateur } from './Utilisateur'

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

// Types pour les réponses d'authentification
export interface AuthResponse {
  user: Utilisateur
  message?: string
}

export interface RefreshResponse {
  user: Utilisateur
  expiresIn?: number
}

export interface AuthState {
  user: Utilisateur | null;
  isLoading: boolean;
  error: string;
  isInitialized: boolean;
}

export interface ApiError {
  code: string;
  message: string;
  details?: { field: string; message: string }[];
}
