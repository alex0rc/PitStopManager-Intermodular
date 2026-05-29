export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'organizer' | 'pilot';
  avatar: string | null;
  is_active: boolean;
  created_at: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: 'organizer' | 'pilot';
}

export interface AuthResponse {
  user: User;
  token: string;
}
