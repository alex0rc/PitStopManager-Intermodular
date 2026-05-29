import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { BehaviorSubject, Observable, tap, map, filter } from 'rxjs';
import { environment } from '../../../environments/environment';
import { User, LoginRequest, RegisterRequest, AuthResponse } from '../models/user.model';
import { unwrapData } from '../utils/api-response';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = environment.apiUrl;
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {
    this.loadUser();
    this.validateSession();
  }

  private loadUser(): void {
    const user = localStorage.getItem('user');
    const token = localStorage.getItem('token');
    if (user && token) {
      this.currentUserSubject.next(JSON.parse(user));
    }
  }

  private validateSession(): void {
    if (!this.token) {
      return;
    }
    this.getUser().subscribe({
      error: (err: HttpErrorResponse) => {
        if (err.status === 401 || err.status === 403) {
          this.clearSession();
        }
      },
    });
  }

  get currentUser(): User | null {
    return this.currentUserSubject.value;
  }

  get token(): string | null {
    return localStorage.getItem('token');
  }

  get isLoggedIn(): boolean {
    return !!this.token;
  }

  login(data: LoginRequest): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/login`, data).pipe(
      tap(res => this.setSession(res))
    );
  }

  register(data: RegisterRequest): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/register`, data).pipe(
      tap(res => this.setSession(res))
    );
  }

  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => this.clearSession())
    );
  }

  getUser(): Observable<User> {
    return this.http.get<User | { data: User }>(`${this.apiUrl}/user`).pipe(
      map((res) => unwrapData(res)),
      filter((user): user is User => user != null),
      tap((user) => {
        this.currentUserSubject.next(user);
        localStorage.setItem('user', JSON.stringify(user));
      }),
    );
  }

  setTokenFromBridge(token: string): Observable<User> {
    localStorage.setItem('token', token);
    return this.getUser();
  }

  hasRole(role: string): boolean {
    return this.currentUser?.role === role;
  }

  private setSession(res: AuthResponse): void {
    const user = unwrapData(res.user as User | { data: User }) ?? res.user;
    localStorage.setItem('token', res.token);
    localStorage.setItem('user', JSON.stringify(user));
    this.currentUserSubject.next(user);
  }

  syncUser(user: User): void {
    this.currentUserSubject.next(user);
    localStorage.setItem('user', JSON.stringify(user));
  }

  clearSession(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUserSubject.next(null);
  }
}
