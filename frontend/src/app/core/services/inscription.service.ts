import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { Inscription } from '../models/inscription.model';
import { unwrapList } from '../utils/api-response';

@Injectable({ providedIn: 'root' })
export class InscriptionService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  private normalizeInscription(raw: Inscription & { races?: Inscription['races'] | { data?: Inscription['races'] } }): Inscription {
    const item = { ...raw } as Inscription;
    if (item.races && !Array.isArray(item.races) && 'data' in item.races) {
      item.races = (item.races as { data?: Inscription['races'] }).data ?? [];
    }
    return item;
  }

  private normalizeList(list: Inscription[]): Inscription[] {
    return unwrapList(list).map((i) => this.normalizeInscription(i));
  }

  getByChampionship(championshipId: number): Observable<Inscription[]> {
    return this.http
      .get<Inscription[] | { data: Inscription[] }>(`${this.apiUrl}/championships/${championshipId}/inscriptions`)
      .pipe(map((res) => this.normalizeList(res as Inscription[])));
  }

  create(
    championshipId: number,
    data: { car_number?: number | null; kart_info?: string | null; race_ids?: number[] },
  ): Observable<Inscription> {
    return this.http.post<Inscription>(`${this.apiUrl}/championships/${championshipId}/inscriptions`, data);
  }

  updateRaces(
    inscriptionId: number,
    data: { race_ids: number[]; car_number?: number | null; kart_info?: string | null },
  ): Observable<Inscription> {
    return this.http.put<Inscription>(`${this.apiUrl}/inscriptions/${inscriptionId}/races`, data);
  }

  updateStatus(id: number, status: string): Observable<Inscription> {
    return this.http.patch<Inscription>(`${this.apiUrl}/inscriptions/${id}/status`, { status });
  }

  remove(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/inscriptions/${id}`);
  }

  detachRace(inscriptionId: number, raceId: number): Observable<Inscription> {
    return this.http.delete<Inscription>(
      `${this.apiUrl}/inscriptions/${inscriptionId}/races/${raceId}`,
    );
  }

  withdraw(id: number): Observable<Inscription> {
    return this.http.delete<Inscription>(`${this.apiUrl}/inscriptions/${id}`);
  }

  delete(id: number): Observable<void> {
    return this.remove(id);
  }

  getMyInscriptions(): Observable<Inscription[]> {
    return this.http
      .get<Inscription[] | { data: Inscription[] }>(`${this.apiUrl}/my/inscriptions`)
      .pipe(map((res) => this.normalizeList(unwrapList(res))));
  }
}
