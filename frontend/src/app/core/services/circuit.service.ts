import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Circuit } from '../models/circuit.model';
import { PaginatedResponse } from '../models/api-response.model';

@Injectable({ providedIn: 'root' })
export class CircuitService {
  private apiUrl = `${environment.apiUrl}/circuits`;

  constructor(private http: HttpClient) {}

  getProvinces(): Observable<string[]> {
    return this.http.get<{ data: string[] }>(`${this.apiUrl}/provinces/list`).pipe(
      map(res => res.data ?? [])
    );
  }

  getAll(params?: Record<string, string | number>): Observable<PaginatedResponse<Circuit>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<Circuit>>(this.apiUrl, { params: httpParams });
  }

  getMine(params?: Record<string, string | number>): Observable<PaginatedResponse<Circuit>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<Circuit>>(
      `${environment.apiUrl}/my/circuits`,
      { params: httpParams }
    );
  }

  getById(id: number): Observable<Circuit> {
    return this.http.get<Circuit>(`${this.apiUrl}/${id}`);
  }

  create(data: Partial<Circuit>): Observable<Circuit> {
    return this.http.post<Circuit>(this.apiUrl, data);
  }

  update(id: number, data: Partial<Circuit>): Observable<Circuit> {
    return this.http.put<Circuit>(`${this.apiUrl}/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  uploadImage(id: number, file: File): Observable<Circuit> {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post<Circuit>(`${this.apiUrl}/${id}/image`, formData);
  }
}
