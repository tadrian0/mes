import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { SidebarComponent } from '../sidebar/sidebar.component';

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, SidebarComponent],
  template: `
    <app-sidebar></app-sidebar>
    <div style="margin-left: 280px; padding: 20px;">
      <router-outlet></router-outlet>
    </div>
  `
})
export class LayoutComponent {}
