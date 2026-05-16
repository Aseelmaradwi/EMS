<template>
  <div class="employee-dashboard">
    <div class="dashboard-header">
      <h2>My Dashboard</h2>
      <p class="dashboard-subtitle">Welcome back, {{ user?.name || 'Employee' }}!</p>
    </div>

    <!-- Personal Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <h3>Leave Balance</h3>
          <p class="stat-number">12</p>
          <span class="stat-change neutral">days remaining</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-content">
          <h3>This Month</h3>
          <p class="stat-number">22/22</p>
          <span class="stat-change positive">100% attendance</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-content">
          <h3>Performance</h3>
          <p class="stat-number">92%</p>
          <span class="stat-change positive">+3% this month</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <h3>Salary Status</h3>
          <p class="stat-number">Paid</p>
          <span class="stat-change positive">Dec 1, 2024</span>
        </div>
      </div>
    </div>

    <!-- Personal Information -->
    <div class="personal-info-section">
      <div class="info-card">
        <h3>Personal Information</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Employee ID:</span>
            <span class="info-value">EMP001</span>
          </div>
          <div class="info-item">
            <span class="info-label">Department:</span>
            <span class="info-value">Engineering</span>
          </div>
          <div class="info-item">
            <span class="info-label">Position:</span>
            <span class="info-value">Software Developer</span>
          </div>
          <div class="info-item">
            <span class="info-label">Manager:</span>
            <span class="info-value">John Manager</span>
          </div>
          <div class="info-item">
            <span class="info-label">Join Date:</span>
            <span class="info-value">Jan 15, 2023</span>
          </div>
          <div class="info-item">
            <span class="info-label">Work Email:</span>
            <span class="info-value">{{ user?.email || 'employee@company.com' }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="activity-card">
        <h3>My Recent Activities</h3>
        <div class="activity-list">
          <div class="activity-item">
            <div class="activity-icon">📅</div>
            <div class="activity-content">
              <p>Leave request submitted</p>
              <span class="activity-time">Dec 10, 2024</span>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon">✅</div>
            <div class="activity-content">
              <p>Leave approved by manager</p>
              <span class="activity-time">Dec 11, 2024</span>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon">📊</div>
            <div class="activity-content">
              <p>Monthly performance review completed</p>
              <span class="activity-time">Dec 1, 2024</span>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon">💰</div>
            <div class="activity-content">
              <p>Salary credited for November</p>
              <span class="activity-time">Dec 1, 2024</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Overview -->
    <div class="attendance-section">
      <h3>Attendance Overview</h3>
      <div class="attendance-chart">
        <div class="attendance-legend">
          <div class="legend-item">
            <span class="legend-dot present"></span>
            <span>Present (22)</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot late"></span>
            <span>Late (0)</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot absent"></span>
            <span>Absent (0)</span>
          </div>
        </div>
        <div class="calendar-grid">
          <div v-for="day in 30" :key="day" 
               class="calendar-day"
               :class="getAttendanceStatus(day)">
            {{ day }}
          </div>
        </div>
      </div>
    </div>

    <!-- Leave History -->
    <div class="leave-section">
      <h3>Leave History</h3>
      <div class="leave-table">
        <div class="table-header">
          <span>Type</span>
          <span>From</span>
          <span>To</span>
          <span>Days</span>
          <span>Status</span>
        </div>
        <div class="table-row">
          <span>Annual Leave</span>
          <span>Dec 15, 2024</span>
          <span>Dec 17, 2024</span>
          <span>3</span>
          <span class="status approved">Approved</span>
        </div>
        <div class="table-row">
          <span>Sick Leave</span>
          <span>Nov 8, 2024</span>
          <span>Nov 9, 2024</span>
          <span>2</span>
          <span class="status approved">Approved</span>
        </div>
        <div class="table-row">
          <span>Personal Leave</span>
          <span>Oct 20, 2024</span>
          <span>Oct 20, 2024</span>
          <span>1</span>
          <span class="status approved">Approved</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <button class="action-btn">
          <span class="action-icon">📅</span>
          <span>Request Leave</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">👤</span>
          <span>Update Profile</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">📊</span>
          <span>View Payslip</span>
        </button>
        <button class="action-btn">
          <span class="action-icon">💬</span>
          <span>Contact HR</span>
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

const getAttendanceStatus = (day) => {
  // Simulate attendance data - in real app, this would come from API
  const absentDays = [5, 12] // Example absent days
  const lateDays = [8, 15] // Example late days
  
  if (absentDays.includes(day)) return 'absent'
  if (lateDays.includes(day)) return 'late'
  return 'present'
}
</script>

<style scoped>
.employee-dashboard {
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
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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

.personal-info-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.info-card, .activity-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-card h3, .activity-card h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.info-grid {
  display: grid;
  gap: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.info-label {
  font-weight: 500;
  color: #6b7280;
}

.info-value {
  font-weight: 600;
  color: #1f2937;
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

.attendance-legend {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #374151;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-dot.present {
  background: #10b981;
}

.legend-dot.late {
  background: #f59e0b;
}

.legend-dot.absent {
  background: #ef4444;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
  background: #f3f4f6;
  color: #6b7280;
}

.calendar-day.present {
  background: #d1fae5;
  color: #065f46;
}

.calendar-day.late {
  background: #fef3c7;
  color: #92400e;
}

.calendar-day.absent {
  background: #fee2e2;
  color: #991b1b;
}

.leave-section {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.leave-section h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.leave-table {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.table-header {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 0.5fr 1fr;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  font-weight: 600;
  color: #374151;
}

.table-row {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 0.5fr 1fr;
  gap: 1rem;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  transition: background 0.3s ease;
}

.table-row:hover {
  background: #f9fafb;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  text-align: center;
}

.status.approved {
  background: #d1fae5;
  color: #065f46;
}

.status.pending {
  background: #fef3c7;
  color: #92400e;
}

.status.rejected {
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
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.action-icon {
  font-size: 1.25rem;
}

@media (max-width: 768px) {
  .personal-info-section {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
  
  .table-header, .table-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
  
  .attendance-legend {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
