import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { MySubscriptionResponse, Subscription, SubscriptionPlan, SubscriptionQuota } from '../models/subscription.model';
import { Payment } from '../models/payment.model';
import { PaginatedResponse } from '../models/api-response.model';

export interface CheckoutSessionResponse {
  checkout_url?: string;
  session_id?: string;
}

@Injectable({ providedIn: 'root' })
export class SubscriptionService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getPlans(): Observable<SubscriptionPlan[]> {
    return this.http.get<SubscriptionPlan[]>(`${this.apiUrl}/subscription-plans`);
  }

  createPlan(data: Partial<SubscriptionPlan>): Observable<SubscriptionPlan> {
    return this.http.post<SubscriptionPlan>(`${this.apiUrl}/admin/subscription-plans`, data);
  }

  updatePlan(id: number, data: Partial<SubscriptionPlan>): Observable<SubscriptionPlan> {
    return this.http.put<SubscriptionPlan>(`${this.apiUrl}/admin/subscription-plans/${id}`, data);
  }

  deletePlan(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/admin/subscription-plans/${id}`);
  }

  subscribe(planId: number): Observable<CheckoutSessionResponse> {
    return this.http.post<CheckoutSessionResponse>(
      `${this.apiUrl}/subscriptions`,
      { plan_id: planId }
    );
  }

  // --- Checkout ---
  confirmCheckout(sessionId: string): Observable<{ message: string; role?: string }> {
    return this.http.post<{ message: string; role?: string }>(
      `${this.apiUrl}/subscriptions/confirm`,
      { session_id: sessionId }
    );
  }

  getMySubscription(): Observable<Subscription | null> {
    return this.getMySubscriptionWithQuota().pipe(map((res) => res.subscription));
  }

  getMySubscriptionWithQuota(): Observable<{ subscription: Subscription | null; quota: SubscriptionQuota }> {
    return this.http.get<MySubscriptionResponse>(`${this.apiUrl}/my/subscription`).pipe(
      map((res) => ({
        subscription: res.data ?? null,
        quota: res.quota,
      })),
    );
  }

  getMyPayments(): Observable<Payment[]> {
    return this.http
      .get<PaginatedResponse<Payment>>(`${this.apiUrl}/my/payments`)
      .pipe(map(res => res.data ?? []));
  }

  downloadPdf(paymentId: number): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/my/payments/${paymentId}/pdf`, { responseType: 'blob' });
  }

  adminGetSubscriptions(params?: Record<string, string | number>): Observable<PaginatedResponse<Subscription>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<Subscription>>(`${this.apiUrl}/admin/subscriptions`, { params: httpParams });
  }

  adminGetPayments(params?: Record<string, string | number>): Observable<PaginatedResponse<Payment>> {
    let httpParams = new HttpParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        httpParams = httpParams.set(key, value.toString());
      });
    }
    return this.http.get<PaginatedResponse<Payment>>(`${this.apiUrl}/admin/payments`, { params: httpParams });
  }
}
