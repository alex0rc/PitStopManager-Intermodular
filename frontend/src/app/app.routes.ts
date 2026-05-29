import { Routes } from '@angular/router';

import { authGuard } from './core/guards/auth.guard';

import { roleGuard } from './core/guards/role.guard';

import { MainLayoutComponent } from './layouts/main-layout/main-layout.component';



export const routes: Routes = [

  {

    path: '',

    component: MainLayoutComponent,

    children: [

      { path: '', loadComponent: () => import('./features/public/landing/landing.component').then(m => m.LandingComponent) },

      { path: 'login', loadComponent: () => import('./features/auth/login/login.component').then(m => m.LoginComponent) },

      { path: 'auth/callback', loadComponent: () => import('./features/auth/auth-callback/auth-callback.component').then(m => m.AuthCallbackComponent) },

      { path: 'register', loadComponent: () => import('./features/auth/register/register.component').then(m => m.RegisterComponent) },

      { path: 'championships', loadComponent: () => import('./features/public/championship-list/championship-list.component').then(m => m.ChampionshipListComponent) },

      { path: 'championships/:id', loadComponent: () => import('./features/public/championship-detail/championship-detail.component').then(m => m.ChampionshipDetailComponent) },

      { path: 'circuits', loadComponent: () => import('./features/public/circuit-list/public-circuit-list.component').then(m => m.PublicCircuitListComponent) },

      { path: 'circuits/:id', loadComponent: () => import('./features/public/circuit-detail/circuit-detail.component').then(m => m.CircuitDetailComponent) },

      {
        path: 'profile',
        canActivate: [authGuard],
        loadComponent: () => import('./features/pilot/profile/profile.component').then(m => m.ProfileComponent),
      },

      {

        path: 'error/:code',

        loadComponent: () => import('./features/public/error-page/error-page.component').then(m => m.ErrorPageComponent),

      },



      {

        path: 'subscription/success',

        canActivate: [authGuard],

        loadComponent: () => import('./features/subscription-result/subscription-result.component').then(m => m.SubscriptionResultComponent),

      },

      {

        path: 'subscription/cancel',

        canActivate: [authGuard],

        loadComponent: () => import('./features/subscription-result/subscription-result.component').then(m => m.SubscriptionResultComponent),

      },



      {

        path: 'pilot',

        canActivate: [authGuard, roleGuard],

        data: { roles: ['pilot'] },

        children: [

          { path: '', loadComponent: () => import('./features/pilot/dashboard/pilot-dashboard.component').then(m => m.PilotDashboardComponent) },

          { path: 'upgrade', loadComponent: () => import('./features/pilot/upgrade/pilot-upgrade.component').then(m => m.PilotUpgradeComponent) },

          { path: 'profile', loadComponent: () => import('./features/pilot/profile/profile.component').then(m => m.ProfileComponent) },

          { path: 'championships', redirectTo: '/championships', pathMatch: 'full' },

          { path: 'inscriptions', loadComponent: () => import('./features/pilot/inscriptions/pilot-inscription-list/pilot-inscription-list.component').then(m => m.PilotInscriptionListComponent) },

          { path: 'results', loadComponent: () => import('./features/pilot/results/pilot-result-list/pilot-result-list.component').then(m => m.PilotResultListComponent) },

          { path: 'payments', loadComponent: () => import('./features/pilot/payments/pilot-payments.component').then(m => m.PilotPaymentsComponent) },

        ],

      },



      {

        path: 'organizer',

        canActivate: [authGuard, roleGuard],

        data: { roles: ['organizer'] },

        children: [

          { path: '', redirectTo: 'championships', pathMatch: 'full' },

          { path: 'championships', loadComponent: () => import('./features/organizer/championships/championship-list/championship-list.component').then(m => m.ChampionshipListComponent) },

          { path: 'championships/new', loadComponent: () => import('./features/organizer/championships/championship-form/championship-form.component').then(m => m.ChampionshipFormComponent) },

          { path: 'championships/:id/edit', loadComponent: () => import('./features/organizer/championships/championship-form/championship-form.component').then(m => m.ChampionshipFormComponent) },

          { path: 'championships/:championshipId/races', loadComponent: () => import('./features/organizer/championships/championship-races/championship-races.component').then(m => m.ChampionshipRacesComponent) },

          { path: 'championships/:championshipId/races/new', loadComponent: () => import('./features/organizer/races/race-form/race-form.component').then(m => m.RaceFormComponent) },

          { path: 'championships/:championshipId/races/:id/edit', loadComponent: () => import('./features/organizer/races/race-form/race-form.component').then(m => m.RaceFormComponent) },

          { path: 'championships/:championshipId/inscriptions', loadComponent: () => import('./features/organizer/inscriptions/inscription-list/inscription-list.component').then(m => m.InscriptionListComponent) },

          { path: 'races/:raceId/results', loadComponent: () => import('./features/organizer/results/result-list/result-list.component').then(m => m.ResultListComponent) },

          { path: 'races/:raceId/results/new', loadComponent: () => import('./features/organizer/results/result-form/result-form.component').then(m => m.ResultFormComponent) },

          { path: 'races/:raceId/results/:id/edit', loadComponent: () => import('./features/organizer/results/result-form/result-form.component').then(m => m.ResultFormComponent) },

          { path: 'circuits', loadComponent: () => import('./features/organizer/circuits/circuit-list/circuit-list.component').then(m => m.CircuitListComponent) },

          { path: 'circuits/new', loadComponent: () => import('./features/organizer/circuits/circuit-form/circuit-form.component').then(m => m.CircuitFormComponent) },

          { path: 'circuits/:id/edit', loadComponent: () => import('./features/organizer/circuits/circuit-form/circuit-form.component').then(m => m.CircuitFormComponent) },

          { path: 'subscription', loadComponent: () => import('./features/organizer/subscription/subscription-page/subscription-page.component').then(m => m.SubscriptionPageComponent) },

          { path: 'inscriptions', loadComponent: () => import('./features/pilot/inscriptions/pilot-inscription-list/pilot-inscription-list.component').then(m => m.PilotInscriptionListComponent) },

        ],

      },

    ],

  },



  {

    path: 'admin',

    canActivate: [authGuard, roleGuard],

    data: { roles: ['admin'] },

    loadComponent: () => import('./features/admin/admin-redirect/admin-redirect.component').then(m => m.AdminRedirectComponent),

  },

  { path: 'admin/**', redirectTo: 'admin' },



  { path: '**', redirectTo: 'error/404' },

];


