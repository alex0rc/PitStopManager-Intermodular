import { User } from './user.model';
import { Championship } from './championship.model';
import { Race } from './race.model';

export interface Inscription {
  id: number;
  user_id: number;
  championship_id: number;
  status: 'pending' | 'confirmed' | 'rejected' | 'withdrawn';
  car_number: number | null;
  kart_info?: string | null;
  user?: User;
  championship?: Championship;
  races?: Race[];
  created_at?: string;
}
