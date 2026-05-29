export interface SubscriptionPlan {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  price: number;
  duration_days: number;
  max_championships: number;
  is_active: boolean;
}

export interface Subscription {
  id: number;
  user_id: number;
  plan_id: number;
  status: 'active' | 'expired' | 'cancelled' | 'pending';
  starts_at: string;
  ends_at: string;
  plan?: SubscriptionPlan;
  created_at?: string;
}

export interface SubscriptionQuota {
  has_active_subscription: boolean;
  plan_name: string | null;
  max_championships: number;
  current_championships: number;
  remaining_championships: number;
  can_create_championship: boolean;
  duration_days: number | null;
  starts_at: string | null;
  ends_at: string | null;
  days_remaining: number | null;
  deny_reason: string | null;
}

export interface MySubscriptionResponse {
  data: Subscription | null;
  quota: SubscriptionQuota;
}
