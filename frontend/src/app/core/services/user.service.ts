import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';
import { PaginatedResponse } from '../models/api-response.model';
import { unwrapData } from '../utils/api-response';

@Injectable({ providedIn: 'root' })
export class UserService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getAll(params?: Record<string, string | number>): Observable<PaginatedResponse<User>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<User>>(`${this.apiUrl}/admin/users`, { params: httpParams });
  }

  getById(id: number): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/admin/users/${id}`);
  }

  update(id: number, data: Partial<User>): Observable<User> {
    return this.http.put<User>(`${this.apiUrl}/admin/users/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/admin/users/${id}`);
  }

  toggleActive(id: number): Observable<User> {
    return this.http.patch<User>(`${this.apiUrl}/admin/users/${id}/toggle-active`, {});
  }

  getProfile(): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/profile`);
  }

  updateProfile(data: Partial<User>): Observable<User> {
    return this.http.put<User>(`${this.apiUrl}/profile`, data);
  }

  uploadAvatar(file: File): Observable<User> {
    const formData = new FormData();
    formData.append('avatar', file);
    return this.http.post<User | { data: User }>(`${this.apiUrl}/profile/avatar`, formData).pipe(
      map((res) => unwrapData(res) as User),
    );
  }

  getPilotProfile(): Observable<any> {
    return this.http.get(`${this.apiUrl}/profile/pilot`);
  }

  updatePilotProfile(data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/profile/pilot`, data);
  }
}
