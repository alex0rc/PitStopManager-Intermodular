import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Result } from '../models/result.model';
import { unwrapList } from '../utils/api-response';

@Injectable({ providedIn: 'root' })
export class ResultService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getByRace(raceId: number): Observable<Result[]> {
    return this.http
      .get<Result[] | { data: Result[] }>(`${this.apiUrl}/races/${raceId}/results`)
      .pipe(map(unwrapList));
  }

  create(raceId: number, data: Partial<Result>): Observable<Result> {
    return this.http.post<Result>(`${this.apiUrl}/races/${raceId}/results`, data);
  }

  update(id: number, data: Partial<Result>): Observable<Result> {
    return this.http.put<Result>(`${this.apiUrl}/results/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/results/${id}`);
  }

  getMyResults(): Observable<Result[]> {
    return this.http
      .get<Result[] | { data: Result[] }>(`${this.apiUrl}/my/results`)
      .pipe(map(unwrapList));
  }
}
