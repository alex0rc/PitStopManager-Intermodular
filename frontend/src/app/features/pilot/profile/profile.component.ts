import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../../core/services/user.service';
import { AuthService } from '../../../core/services/auth.service';
import { RouterLink } from '@angular/router';
import { User } from '../../../core/models/user.model';
import { environment } from '../../../../environments/environment';

interface PilotProfile {
  nickname: string;
  birth_date: string;
  license_number: string;
  bio: string;
}

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [FormsModule, RouterLink],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.scss',
})
export class ProfileComponent implements OnInit {
  user: User | null = null;
  pilotProfile: PilotProfile = { nickname: '', birth_date: '', license_number: '', bio: '' };

  userName = '';
  userEmail = '';

  avatarPreview: string | null = null;
  avatarFile: File | null = null;

  loading = true;
  pilotLoading = true;
  userSaving = false;
  pilotSaving = false;
  avatarSaving = false;

  userSuccess = '';
  userError = '';
  pilotSuccess = '';
  pilotError = '';
  avatarSuccess = '';
  avatarError = '';

  constructor(
    private userService: UserService,
    public authService: AuthService,
  ) {}

  get isOrganizer(): boolean {
    return this.authService.hasRole('organizer');
  }

  ngOnInit(): void {
    this.loadUser();
  }

  loadUser(): void {
    this.loading = true;
    this.userService.getProfile().subscribe({
      next: (user) => {
        this.user = user;
        this.userName = user.name;
        this.userEmail = user.email;
        this.avatarPreview = user.avatar ? user.avatar : null;
        this.loading = false;
        this.loadPilotProfile();
      },
      error: () => {
        this.userError = 'No se pudo cargar el perfil.';
        this.loading = false;
      },
    });
  }

  loadPilotProfile(): void {
    this.pilotLoading = true;
    this.userService.getPilotProfile().subscribe({
      next: (profile) => {
        this.pilotProfile = {
          nickname: profile.nickname ?? '',
          birth_date: profile.birth_date ?? '',
          license_number: profile.license_number ?? '',
          bio: profile.bio ?? '',
        };
        this.pilotLoading = false;
      },
      error: () => {
        this.pilotLoading = false;
      },
    });
  }

  saveUser(): void {
    this.userSaving = true;
    this.userSuccess = '';
    this.userError = '';

    this.userService.updateProfile({ name: this.userName, email: this.userEmail }).subscribe({
      next: (user) => {
        this.user = user;
        this.userSuccess = 'Datos de usuario actualizados correctamente.';
        this.userSaving = false;
        this.authService.getUser().subscribe();
      },
      error: () => {
        this.userError = 'Error al actualizar los datos de usuario.';
        this.userSaving = false;
      },
    });
  }

  savePilotProfile(): void {
    this.pilotSaving = true;
    this.pilotSuccess = '';
    this.pilotError = '';

    this.userService.updatePilotProfile(this.pilotProfile).subscribe({
      next: () => {
        this.pilotSuccess = 'Perfil de piloto actualizado correctamente.';
        this.pilotSaving = false;
      },
      error: () => {
        this.pilotError = 'Error al actualizar el perfil de piloto.';
        this.pilotSaving = false;
      },
    });
  }

  onAvatarSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    this.avatarFile = input.files[0];
    const reader = new FileReader();
    reader.onload = () => (this.avatarPreview = reader.result as string);
    reader.readAsDataURL(this.avatarFile);
  }

  uploadAvatar(): void {
    if (!this.avatarFile) return;

    this.avatarSaving = true;
    this.avatarSuccess = '';
    this.avatarError = '';

    this.userService.uploadAvatar(this.avatarFile).subscribe({
      next: (user) => {
        this.user = user;
        this.avatarPreview = user.avatar;
        this.avatarFile = null;
        this.avatarSuccess = 'Avatar actualizado correctamente.';
        this.avatarSaving = false;
        this.authService.syncUser(user);
      },
      error: () => {
        this.avatarError = 'Error al subir el avatar.';
        this.avatarSaving = false;
      },
    });
  }
}
