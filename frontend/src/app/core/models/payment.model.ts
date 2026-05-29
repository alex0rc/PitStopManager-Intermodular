import { Subscription } from './subscription.model';

export interface Payment {
  id: number;
  subscription_id: number;
  user_id: number;
  amount: number;
  currency: string;
  status: 'succeeded' | 'pending' | 'failed' | 'refunded';
  paid_at: string | null;
  subscription?: Subscription;
  created_at?: string;
}
