import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { UserService } from '../../core/services/user.service';

@Component({
  selector: 'app-user-edit',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  template: `
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
      <h1 class="h2">{{ isEdit ? 'Edit User' : 'New User' }}</h1>
    </div>

    <div class="row">
      <div class="col-md-6">
        <form (ngSubmit)="saveUser()">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" [(ngModel)]="user.username" name="username" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" [(ngModel)]="user.password" name="password" [required]="!isEdit">
            <div *ngIf="isEdit" class="form-text">Leave blank to keep current password.</div>
          </div>

          <div class="mb-3">
            <label for="role" class="form-label">Roles</label>
            <select class="form-select" id="role" [(ngModel)]="user.role" name="role" required>
                <option value="Operator">Operator</option>
                <option value="Admin">Admin</option>
                <option value="Admin;Operator">Admin & Operator</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary" [disabled]="loading">Save</button>
          <a routerLink="/users" class="btn btn-secondary ms-2">Cancel</a>
        </form>
      </div>
    </div>
  `
})
export class UserEditComponent implements OnInit {
  user: any = { username: '', password: '', role: 'Operator' };
  isEdit = false;
  id: number = 0;
  loading = false;

  constructor(
    private userService: UserService,
    private route: ActivatedRoute,
    private router: Router
  ) {}

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    if (idParam) {
      this.isEdit = true;
      this.id = +idParam;
      this.loadUser();
    }
  }

  loadUser() {
    this.userService.getUser(this.id).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          const data = res.data;
          this.user = {
            username: data.OperatorUsername,
            role: data.OperatorRoles,
            password: ''
          };
        }
      },
      error: (err) => console.error(err)
    });
  }

  saveUser() {
    this.loading = true;
    if (this.isEdit) {
      this.userService.updateUser(this.id, this.user).subscribe({
        next: (res) => {
          this.loading = false;
          if (res.status === 'success') {
            this.router.navigate(['/users']);
          } else {
            alert(res.message);
          }
        },
        error: (err) => {
          this.loading = false;
          alert('Error updating user');
        }
      });
    } else {
      this.userService.createUser(this.user).subscribe({
        next: (res) => {
          this.loading = false;
          if (res.status === 'success') {
            this.router.navigate(['/users']);
          } else {
            alert(res.message);
          }
        },
        error: (err) => {
          this.loading = false;
          alert('Error creating user');
        }
      });
    }
  }
}
