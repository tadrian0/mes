import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap } from 'rxjs';
import { API_BASE_URL } from '../constants';

export interface User {
  OperatorID: number;
  OperatorUsername: string;
  OperatorRoles: string;
}

export interface LoginResponse {
  status: string;
  api_key: string;
  user: User;
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = `${API_BASE_URL}/backoffice-login.php`;
  private apiKeyKey = 'mes_api_key';
  private userKey = 'mes_user';

  currentUser = signal<User | null>(this.getUserFromStorage());

  constructor(private http: HttpClient, private router: Router) {}

  login(username: string, password: string) {
    return this.http.post<LoginResponse>(this.apiUrl, { username, password }).pipe(
      tap(response => {
        if (response.status === 'success') {
          localStorage.setItem(this.apiKeyKey, response.api_key);
          localStorage.setItem(this.userKey, JSON.stringify(response.user));
          this.currentUser.set(response.user);
          this.router.navigate(['/dashboard']);
        }
      })
    );
  }

  logout() {
    localStorage.removeItem(this.apiKeyKey);
    localStorage.removeItem(this.userKey);
    this.currentUser.set(null);
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    if (typeof localStorage !== 'undefined') {
        return localStorage.getItem(this.apiKeyKey);
    }
    return null;
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  private getUserFromStorage(): User | null {
    if (typeof localStorage !== 'undefined') {
        const userStr = localStorage.getItem(this.userKey);
        return userStr ? JSON.parse(userStr) : null;
    }
    return null;
  }
}
