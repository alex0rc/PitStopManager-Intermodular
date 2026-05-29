import { User } from './user.model';

export interface Circuit {
  id: number;
  user_id: number;
  name: string;
  location: string;
  city: string | null;
  province: string | null;
  country: string | null;
  status?: 'pending' | 'approved' | 'rejected';
  latitude: number | null;
  longitude: number | null;
  length_meters: number | null;
  image: string | null;
  description: string | null;
  user?: User;
}
