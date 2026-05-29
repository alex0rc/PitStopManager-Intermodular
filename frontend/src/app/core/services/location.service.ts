import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

export interface GeoCoordinates {
  latitude: number;
  longitude: number;
}

@Injectable({ providedIn: 'root' })
export class LocationService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getCountries(): Observable<string[]> {
    return this.http
      .get<{ data: string[] }>(`${this.apiUrl}/locations/countries`)
      .pipe(map((r) => r.data ?? []));
  }

  getProvinces(country: string): Observable<string[]> {
    const params = new HttpParams().set('country', country);
    return this.http
      .get<{ data: string[] }>(`${this.apiUrl}/locations/provinces`, { params })
      .pipe(map((r) => r.data ?? []));
  }

  getCities(province: string): Observable<string[]> {
    const params = new HttpParams().set('province', province);
    return this.http
      .get<{ data: string[] }>(`${this.apiUrl}/locations/cities`, { params })
      .pipe(map((r) => r.data ?? []));
  }

  geocode(city: string, province: string, country: string): Observable<GeoCoordinates> {
    return this.http
      .post<{ data: GeoCoordinates }>(`${this.apiUrl}/locations/geocode`, {
        city,
        province,
        country,
      })
      .pipe(map((r) => r.data));
  }
}
