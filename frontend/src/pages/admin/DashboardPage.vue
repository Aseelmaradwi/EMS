<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <h2>Admin Dashboard</h2>
      <p class="dashboard-subtitle">Welcome back, {{ user?.name || 'Admin' }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <h3>Total Employees</h3>
          <p class="stat-number">248</p>
          <span class="stat-change positive">+12% from last month</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-content">
          <h3>Departments</h3>
          <p class="stat-number">12</p>
          <span class="stat-change neutral">No change</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <h3>Pending Leaves</h3>
          <p class="stat-number">18</p>
          <span class="stat-change negative">+5 from yesterday</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-content">
          <h3>Today's Attendance</h3>
          <p class="stat-number">92%</p>
          <span class="stat-change positive">+3% from last week</span>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
      <div class="chart-card">
        <h3>Employee Growth</h3>
        <div class="chart-placeholder">
          <div class="chart-bar" style="height: 60%"></div>
          <div class="chart-bar" style="height: 75%"></div>
          <div class="chart-bar" style="height: 85%"></div>
          <div class="chart-bar" style="height: 90%"></div>
          <div class="chart-bar" style="height: 95%"></div>
          <div class="chart-bar" style="height: 100%"></div>
        </div>
        <div class="chart-labels">
          <span>Jan</span>
          <span>Feb</span>
          <span>Mar</span>
          <span>Apr</span>
          <span>May</span>
          <span>Jun</span>
        </div>
      </div>

      <div class="chart-card">
        <h3>Department Distribution</h3>
        <div class="pie-chart-placeholder">
          <div class="pie-slice slice-1"></div>
          <div class="pie-slice slice-2"></div>
          <div class="pie-slice slice-3"></div>
          <div class="pie-slice slice-4"></div>
        </div>
        <div class="pie-legend">
          <div class="legend-item">
            <span class="legend-color color-1"></span>
            <span>Engineering (35%)</span>
          </div>
          <div class="legend-item">
            <span class="legend-color color-2"></span>
            <span>Sales (25%)</span>
          </div>
          <div class="legend-item">
            <span class="legend-color color-3"></span>
            <span>HR (20%)</span>
          </div>
          <div class="legend-item">
            <span class="legend-color color-4"></span>
            <span>Other (20%)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="recent-activities">
      <h3>Recent Activities</h3>
      <div class="activity-list">
        <div class="activity-item">
          <div class="activity-icon">👤</div>
          <div class="activity-content">
            <p><strong>John Doe</strong> joined as Software Engineer</p>
            <span class="activity-time">2 hours ago</span>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-icon">📅</div>
          <div class="activity-content">
            <p><strong>Jane Smith</strong> requested leave for 3 days</p>
            <span class="activity-time">4 hours ago</span>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-icon">💰</div>
          <div class="activity-content">
            <p>Salary processed for <strong>45 employees</strong></p>
            <span class="activity-time">6 hours ago</span>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-icon">🏢</div>
          <div class="activity-content">
            <p>New department <strong>Marketing</strong> created</p>
            <span class="activity-time">1 day ago</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <button class="action-btn">
          <span class="action-icon">➕</span>
          <span>Add Employee</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">📊</span>
          <span>Generate Report</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">📧</span>
          <span>Send Announcement</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">⚙️</span>
          <span>Settings</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../stores/auth.store'

const authStore = useAuthStore()
const user = computed(() => authStore.user)
</script>

<style scoped>
.admin-dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.dashboard-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.dashboard-subtitle {
  color: #6b7280;
  font-size: 1.1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-content h3 {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.stat-change {
  font-size: 0.85rem;
  font-weight: 500;
}

.stat-change.positive {
  color: #10b981;
}

.stat-change.negative {
  color: #ef4444;
}

.stat-change.neutral {
  color: #6b7280;
}

.charts-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-card h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.chart-placeholder {
  display: flex;
  align-items: end;
  justify-content: space-around;
  height: 200px;
  margin-bottom: 1rem;
}

.chart-bar {
  width: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 4px 4px 0 0;
  transition: height 0.3s ease;
}

.chart-bar:hover {
  opacity: 0.8;
}

.chart-labels {
  display: flex;
  justify-content: space-around;
  color: #6b7280;
  font-size: 0.9rem;
}

.pie-chart-placeholder {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background: conic-gradient(
    #667eea 0deg 126deg,
    #10b981 126deg 216deg,
    #f59e0b 216deg 288deg,
    #ef4444 288deg 360deg
  );
  margin: 0 auto 1.5rem;
  position: relative;
}

.pie-legend {
  display: grid;
  gap: 0.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #374151;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.color-1 { background: #667eea; }
.color-2 { background: #10b981; }
.color-3 { background: #f59e0b; }
.color-4 { background: #ef4444; }

.recent-activities {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.recent-activities h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  transition: background 0.3s ease;
}

.activity-item:hover {
  background: #f9fafb;
}

.activity-icon {
  font-size: 1.5rem;
  background: #f3f4f6;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-content p {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
}

.activity-time {
  font-size: 0.85rem;
  color: #6b7280;
}

.quick-actions {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.quick-actions h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.action-icon {
  font-size: 1.25rem;
}

@media (max-width: 768px) {
  .charts-section {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
}
</style>
