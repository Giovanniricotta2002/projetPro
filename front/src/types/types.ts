// Types
interface User {
  id: string
  username: string
  email: string
  roles?: string[]
  createdAt?: string
}

interface LoginData {
  login: string
  password: string
  csrfToken: string
}

interface RegisterData {
  username: string
  email: string
  password: string
  csrfToken: string
}

interface ApiResponse<T = any> {
  success: boolean
  data?: T
  message?: string
  errors?: Record<string, string[]>
}

export type { User, LoginData, RegisterData, ApiResponse }