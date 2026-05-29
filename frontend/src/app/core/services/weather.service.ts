import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { WeatherData } from '../models/weather.model';

@Injectable({ providedIn: 'root' })
export class WeatherService {
  private apiUrl = `${environment.apiUrl}/weather`;

  constructor(private http: HttpClient) {}

  getWeather(lat: number, lng: number): Observable<WeatherData> {
    const params = new HttpParams()
      .set('lat', lat.toString())
      .set('lng', lng.toString());

    return this.http.get<{ data: WeatherData }>(this.apiUrl, { params }).pipe(
      map((res) => res.data)
    );
  }
}
