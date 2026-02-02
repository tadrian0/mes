import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; height: 100vh; position: fixed; top: 0; left: 0;">
      <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fa-solid fa-industry me-2"></i>
        <span class="fs-4">MES Backoffice</span>
      </a>
      <hr>
      <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
          <a routerLink="/dashboard" routerLinkActive="active" class="nav-link text-white">
            <i class="fa-solid fa-gauge-high me-2"></i>
            Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a routerLink="/users" routerLinkActive="active" class="nav-link text-white">
            <i class="fa-solid fa-users me-2"></i>
            Users
          </a>
        </li>
      </ul>
      <hr>
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none" (click)="logout($event)">
           <i class="fa-solid fa-right-from-bracket me-2"></i>
           <strong>Sign out</strong>
        </a>
      </div>
    </div>
  `,
  styles: [`
    .nav-link.active {
      background-color: #0d6efd;
    }
    .nav-link:not(.active):hover {
        background-color: rgba(255,255,255,0.1);
    }
  `]
})
export class SidebarComponent {
  constructor(public authService: AuthService) {}

  logout(event: Event) {
    event.preventDefault();
    this.authService.logout();
  }
}
