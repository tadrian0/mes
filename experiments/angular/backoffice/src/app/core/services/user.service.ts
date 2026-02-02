import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { API_BASE_URL } from '../constants';
import { Observable } from 'rxjs';

export interface UserResponse {
  status: string;
  data?: any;
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = `${API_BASE_URL}/user.php`;

  constructor(private http: HttpClient) {}

  getUsers(search?: string, role?: string): Observable<UserResponse> {
    let params = new HttpParams().set('action', 'list');
    if (search) params = params.set('search', search);
    if (role) params = params.set('role', role);

    return this.http.get<UserResponse>(this.apiUrl, { params });
  }

  getUser(id: number): Observable<UserResponse> {
    let params = new HttpParams().set('action', 'get').set('id', id);
    return this.http.get<UserResponse>(this.apiUrl, { params });
  }

  createUser(user: any): Observable<UserResponse> {
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('username', user.username);
    formData.append('password', user.password);
    formData.append('role', user.role);

    return this.http.post<UserResponse>(this.apiUrl, formData);
  }

  updateUser(id: number, user: any): Observable<UserResponse> {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', id.toString());
    formData.append('username', user.username);
    if (user.password) {
        formData.append('password', user.password);
    }
    formData.append('role', user.role);

    return this.http.post<UserResponse>(this.apiUrl, formData);
  }

  deleteUser(id: number): Observable<UserResponse> {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id.toString());

    return this.http.post<UserResponse>(this.apiUrl, formData);
  }
}
