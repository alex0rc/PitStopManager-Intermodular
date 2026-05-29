import { Championship } from './championship.model';
import { Race } from './race.model';
import { WeatherData } from './weather.model';

export interface FeaturedStanding {
  user_id: number;
  pilot_name: string;
  total_points: number;
}

export interface FeaturedChampionshipPayload {
  championship: Championship;
  standings: FeaturedStanding[];
  next_race: Race | null;
  weather: WeatherData | null;
}
