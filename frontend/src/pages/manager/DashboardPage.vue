<template>
  <div class="manager-dashboard">
    <div class="dashboard-header">
      <h2>Manager Dashboard</h2>
      <p class="dashboard-subtitle">Welcome back, {{ user?.name || 'Manager' }}!</p>
    </div>

    <!-- Team Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <h3>Team Members</h3>
          <p class="stat-number">24</p>
          <span class="stat-change positive">+2 this month</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <h3>Pending Approvals</h3>
          <p class="stat-number">8</p>
          <span class="stat-change neutral">3 leave requests</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-content">
          <h3>Today's Attendance</h3>
          <p class="stat-number">22/24</p>
          <span class="stat-change positive">92% present</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-content">
          <h3>Team Performance</h3>
          <p class="stat-number">87%</p>
          <span class="stat-change positive">+5% this month</span>
        </div>
      </div>
    </div>

    <!-- Team Performance Chart -->
    <div class="performance-section">
      <div class="chart-card">
        <h3>Team Performance Overview</h3>
        <div class="performance-chart">
          <div class="performance-item">
            <span class="employee-name">John Doe</span>
            <div class="performance-bar">
              <div class="bar-fill" style="width: 95%"></div>
            </div>
            <span class="performance-score">95%</span>
          </div>
          <div class="performance-item">
            <span class="employee-name">Jane Smith</span>
            <div class="performance-bar">
              <div class="bar-fill" style="width: 88%"></div>
            </div>
            <span class="performance-score">88%</span>
          </div>
          <div class="performance-item">
            <span class="employee-name">Mike Johnson</span>
            <div class="performance-bar">
              <div class="bar-fill" style="width: 92%"></div>
            </div>
            <span class="performance-score">92%</span>
          </div>
          <div class="performance-item">
            <span class="employee-name">Sarah Williams</span>
            <div class="performance-bar">
              <div class="bar-fill" style="width: 85%"></div>
            </div>
            <span class="performance-score">85%</span>
          </div>
          <div class="performance-item">
            <span class="employee-name">Tom Brown</span>
            <div class="performance-bar">
              <div class="bar-fill" style="width: 78%"></div>
            </div>
            <span class="performance-score">78%</span>
          </div>
        </div>
      </div>

      <!-- Leave Requests -->
      <div class="leave-card">
        <h3>Recent Leave Requests</h3>
        <div class="leave-list">
          <div class="leave-item pending">
            <div class="leave-info">
              <p><strong>Jane Smith</strong></p>
              <span class="leave-dates">Dec 15-17, 2024</span>
            </div>
            <div class="leave-actions">
              <button class="approve-btn">✓ Approve</button>
              <button class="reject-btn">✗ Reject</button>
            </div>
          </div>
          <div class="leave-item pending">
            <div class="leave-info">
              <p><strong>Mike Johnson</strong></p>
              <span class="leave-dates">Dec 20-21, 2024</span>
            </div>
            <div class="leave-actions">
              <button class="approve-btn">✓ Approve</button>
              <button class="reject-btn">✗ Reject</button>
            </div>
          </div>
          <div class="leave-item approved">
            <div class="leave-info">
              <p><strong>Sarah Williams</strong></p>
              <span class="leave-dates">Dec 10-11, 2024</span>
            </div>
            <div class="leave-status approved">Approved</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Attendance -->
    <div class="attendance-section">
      <h3>Today's Team Attendance</h3>
      <div class="attendance-grid">
        <div class="attendance-item present">
          <div class="attendance-avatar">👤</div>
          <div class="attendance-info">
            <p class="employee-name">John Doe</p>
            <span class="attendance-time">9:00 AM</span>
          </div>
          <div class="attendance-status">Present</div>
        </div>
        <div class="attendance-item present">
          <div class="attendance-avatar">👤</div>
          <div class="attendance-info">
            <p class="employee-name">Jane Smith</p>
            <span class="attendance-time">8:45 AM</span>
          </div>
          <div class="attendance-status">Present</div>
        </div>
        <div class="attendance-item late">
          <div class="attendance-avatar">👤</div>
          <div class="attendance-info">
            <p class="employee-name">Mike Johnson</p>
            <span class="attendance-time">9:15 AM</span>
          </div>
          <div class="attendance-status late">Late</div>
        </div>
        <div class="attendance-item absent">
          <div class="attendance-avatar">👤</div>
          <div class="attendance-info">
            <p class="employee-name">Tom Brown</p>
            <span class="attendance-time">--</span>
          </div>
          <div class="attendance-status absent">Absent</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <button class="action-btn">
          <span class="action-icon">📊</span>
          <span>View Reports</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">📅</span>
          <span>Approve Leaves</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">⏰</span>
          <span>Mark Attendance</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">💬</span>
          <span>Team Chat</span>
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
.manager-dashboard {
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
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.performance-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-card, .leave-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-card h3, .leave-card h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.performance-chart {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.performance-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.employee-name {
  min-width: 120px;
  font-weight: 500;
  color: #374151;
}

.performance-bar {
  flex: 1;
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.performance-score {
  min-width: 40px;
  font-weight: 600;
  color: #059669;
  text-align: right;
}

.leave-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.leave-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.leave-item.pending {
  background: #fef3c7;
  border-color: #f59e0b;
}

.leave-item.approved {
  background: #d1fae5;
  border-color: #10b981;
}

.leave-info p {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
  font-weight: 500;
}

.leave-dates {
  font-size: 0.85rem;
  color: #6b7280;
}

.leave-actions {
  display: flex;
  gap: 0.5rem;
}

.approve-btn, .reject-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.approve-btn {
  background: #10b981;
  color: white;
}

.approve-btn:hover {
  background: #059669;
}

.reject-btn {
  background: #ef4444;
  color: white;
}

.reject-btn:hover {
  background: #dc2626;
}

.leave-status {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
}

.leave-status.approved {
  background: #10b981;
  color: white;
}

.attendance-section {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.attendance-section h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.attendance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.attendance-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  transition: background 0.3s ease;
}

.attendance-item:hover {
  background: #f9fafb;
}

.attendance-item.present {
  border-left: 4px solid #10b981;
}

.attendance-item.late {
  border-left: 4px solid #f59e0b;
}

.attendance-item.absent {
  border-left: 4px solid #ef4444;
}

.attendance-avatar {
  font-size: 1.5rem;
  background: #f3f4f6;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.attendance-info {
  flex: 1;
}

.attendance-info .employee-name {
  margin: 0 0 0.25rem 0;
  color: #1f2937;
  font-weight: 500;
}

.attendance-time {
  font-size: 0.85rem;
  color: #6b7280;
}

.attendance-status {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.attendance-status.present {
  background: #d1fae5;
  color: #065f46;
}

.attendance-status.late {
  background: #fef3c7;
  color: #92400e;
}

.attendance-status.absent {
  background: #fee2e2;
  color: #991b1b;
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
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.action-icon {
  font-size: 1.25rem;
}

@media (max-width: 768px) {
  .performance-section {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
  
  .attendance-grid {
    grid-template-columns: 1fr;
  }
}
</style>
