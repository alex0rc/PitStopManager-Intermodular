import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Championship } from '../models/championship.model';
import { FeaturedChampionshipPayload } from '../models/featured-championship.model';
import { RaceWithResults } from '../models/race-results.model';
import { PaginatedResponse } from '../models/api-response.model';
import { map } from 'rxjs/operators';
import { unwrapList } from '../utils/api-response';

export interface StandingEntry {
  user_id: number;
  pilot_name: string;
  total_points: number;
}

function normalizeStanding(row: Record<string, unknown>): StandingEntry {
  return {
    user_id: Number(row['user_id'] ?? row['id'] ?? 0),
    pilot_name: String(row['pilot_name'] ?? row['name'] ?? ''),
    total_points: Number(row['total_points'] ?? 0),
  };
}

@Injectable({ providedIn: 'root' })
export class ChampionshipService {
  private apiUrl = `${environment.apiUrl}/championships`;

  constructor(private http: HttpClient) {}

  getAll(params?: Record<string, string | number>): Observable<PaginatedResponse<Championship>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<Championship>>(this.apiUrl, { params: httpParams });
  }

  getMine(params?: Record<string, string | number>): Observable<PaginatedResponse<Championship>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<Championship>>(
      `${environment.apiUrl}/my/championships`,
      { params: httpParams }
    );
  }

  getById(id: number): Observable<Championship> {
    return this.http.get<Championship>(`${this.apiUrl}/${id}`);
  }

  create(data: Partial<Championship>): Observable<Championship> {
    return this.http.post<Championship>(this.apiUrl, data);
  }

  update(id: number, data: Partial<Championship>): Observable<Championship> {
    return this.http.put<Championship>(`${this.apiUrl}/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  updateStatus(id: number, status: string): Observable<Championship> {
    return this.http.patch<Championship>(`${this.apiUrl}/${id}/status`, { status });
  }

  uploadImage(id: number, file: File): Observable<Championship> {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post<Championship>(`${this.apiUrl}/${id}/image`, formData);
  }

  getFeatured(): Observable<FeaturedChampionshipPayload | null> {
    return this.http
      .get<{ data: FeaturedChampionshipPayload | null }>(`${this.apiUrl}/featured`)
      .pipe(map((res) => res.data));
  }

  getStandings(id: number): Observable<StandingEntry[]> {
    return this.http
      .get<{ data: StandingEntry[] } | StandingEntry[]>(`${this.apiUrl}/${id}/standings`)
      .pipe(
        map((res) => unwrapList(res)),
        map((rows) => rows.map((row) => normalizeStanding(row as unknown as Record<string, unknown>)))
      );
  }

  getRaceResults(id: number): Observable<RaceWithResults[]> {
    return this.http
      .get<{ data: RaceWithResults[] } | RaceWithResults[]>(`${this.apiUrl}/${id}/race-results`)
      .pipe(map((res) => unwrapList(res)));
  }
}
