import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  template: `
    <h1>Dashboard</h1>
    <p>Welcome to the MES Backoffice (Angular Edition).</p>
    <div class="row mt-4">
      <div class="col-md-4">
        <div class="card text-bg-primary mb-3">
          <div class="card-header">Production</div>
          <div class="card-body">
            <h5 class="card-title">Status: Running</h5>
            <p class="card-text">All systems operational.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-bg-success mb-3">
          <div class="card-header">Efficiency</div>
          <div class="card-body">
            <h5 class="card-title">98%</h5>
            <p class="card-text">OEE is above target.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-bg-warning mb-3">
          <div class="card-header">Alerts</div>
          <div class="card-body">
            <h5 class="card-title">0 Active</h5>
            <p class="card-text">No critical alerts.</p>
          </div>
        </div>
      </div>
    </div>
  `
})
export class DashboardComponent {}
