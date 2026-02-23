import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { UserService } from '../../core/services/user.service';
import { User } from '../../core/services/auth.service';

@Component({
  selector: 'app-user-list',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
      <h1 class="h2">Users</h1>
      <div class="btn-toolbar mb-2 mb-md-0">
        <a routerLink="/users/new" class="btn btn-sm btn-outline-primary">
          <i class="fa-solid fa-plus me-1"></i> New User
        </a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Username</th>
            <th scope="col">Roles</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr *ngFor="let user of users">
            <td>{{ user.OperatorID }}</td>
            <td>{{ user.OperatorUsername }}</td>
            <td>
                <span class="badge bg-secondary me-1" *ngFor="let role of user.OperatorRoles.split(';')">
                    {{ role }}
                </span>
            </td>
            <td>
              <a [routerLink]="['/users', user.OperatorID]" class="btn btn-sm btn-link text-decoration-none">
                <i class="fa-solid fa-pencil"></i>
              </a>
              <button class="btn btn-sm btn-link text-danger" (click)="deleteUser(user.OperatorID)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr *ngIf="users.length === 0">
            <td colspan="4" class="text-center">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  `
})
export class UserListComponent implements OnInit {
  users: User[] = [];

  constructor(private userService: UserService) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers() {
    this.userService.getUsers().subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.users = res.data;
        }
      },
      error: (err) => console.error(err)
    });
  }

  deleteUser(id: number) {
    if (confirm('Are you sure you want to delete this user?')) {
      this.userService.deleteUser(id).subscribe({
        next: (res) => {
          if (res.status === 'success') {
            this.loadUsers();
          } else {
            alert('Failed to delete user: ' + res.message);
          }
        },
        error: (err) => alert('Error deleting user')
      });
    }
  }
}
