export interface WeatherData {
  temperature: number | null;
  feels_like: number | null;
  humidity: number | null;
  description: string | null;
  icon: string | null;
  wind_speed: number | null;
  clouds: number | null;
  city: string | null;
  error?: string;
}
