import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Race } from '../models/race.model';
import { unwrapList } from '../utils/api-response';

@Injectable({ providedIn: 'root' })
export class RaceService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getByChampionship(championshipId: number): Observable<Race[]> {
    return this.http
      .get<Race[] | { data: Race[] }>(`${this.apiUrl}/championships/${championshipId}/races`)
      .pipe(map(unwrapList));
  }

  getById(id: number): Observable<Race> {
    return this.http.get<Race>(`${this.apiUrl}/races/${id}`);
  }

  create(championshipId: number, data: Partial<Race>): Observable<Race> {
    return this.http.post<Race>(`${this.apiUrl}/championships/${championshipId}/races`, data);
  }

  update(id: number, data: Partial<Race>): Observable<Race> {
    return this.http.put<Race>(`${this.apiUrl}/races/${id}`, data);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/races/${id}`);
  }
}
