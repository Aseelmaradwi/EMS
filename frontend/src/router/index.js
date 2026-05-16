import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'
import AuthPage from '../pages/AuthPage.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import ManagerLayout from '../layouts/ManagerLayout.vue'
import EmployeeLayout from '../layouts/EmployeeLayout.vue'
import AdminDashboardPage from '../pages/admin/DashboardPage.vue'
import ManagerDashboardPage from '../pages/manager/DashboardPage.vue'
import EmployeeDashboardPage from '../pages/employee/DashboardPage.vue'

const routes = [
  {
    path: '/',
    redirect: '/login',
  },
  {
    path: '/login',
    name: 'login',
    component: AuthPage,
    meta: { guestOnly: true },
  },
  // Admin Routes
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: AdminDashboardPage
      },
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('../pages/admin/UsersPage.vue')
      },
      {
        path: 'employees',
        name: 'admin-employees',
        component: () => import('../pages/admin/EmployeesPage.vue')
      },
      {
        path: 'departments',
        name: 'admin-departments',
        component: () => import('../pages/admin/DepartmentsPage.vue')
      },
      {
        path: 'salaries',
        name: 'admin-salaries',
        component: () => import('../pages/admin/SalariesPage.vue')
      },
      {
        path: 'leaves',
        name: 'admin-leaves',
        component: () => import('../pages/admin/LeavesPage.vue')
      },
      {
        path: 'attendance',
        name: 'admin-attendance',
        component: () => import('../pages/admin/AttendancePage.vue')
      },
      {
        path: 'reports',
        name: 'admin-reports',
        component: () => import('../pages/admin/ReportsPage.vue')
      }
    ]
  },
  // Manager Routes
  {
    path: '/manager',
    component: ManagerLayout,
    meta: { requiresAuth: true, role: 'manager' },
    children: [
      {
        path: '',
        redirect: '/manager/dashboard'
      },
      {
        path: 'dashboard',
        name: 'manager-dashboard',
        component: ManagerDashboardPage
      },
      {
        path: 'employees',
        name: 'manager-employees',
        component: () => import('../pages/manager/EmployeesPage.vue')
      },
      {
        path: 'leaves',
        name: 'manager-leaves',
        component: () => import('../pages/manager/LeavesPage.vue')
      },
      {
        path: 'attendance',
        name: 'manager-attendance',
        component: () => import('../pages/manager/AttendancePage.vue')
      }
    ]
  },
  // Employee Routes
  {
    path: '/employee',
    component: EmployeeLayout,
    meta: { requiresAuth: true, role: 'employee' },
    children: [
      {
        path: '',
        redirect: '/employee/dashboard'
      },
      {
        path: 'dashboard',
        name: 'employee-dashboard',
        component: EmployeeDashboardPage
      },
      {
        path: 'profile',
        name: 'employee-profile',
        component: () => import('../pages/employee/ProfilePage.vue')
      },
      {
        path: 'leaves',
        name: 'employee-leaves',
        component: () => import('../pages/employee/LeavesPage.vue')
      },
      {
        path: 'attendance',
        name: 'employee-attendance',
        component: () => import('../pages/employee/AttendancePage.vue')
      }
    ]
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const userRole = authStore.user?.role?.name

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }

  // Check if route is for guests only (login page)
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    // Redirect based on user role
    if (userRole === 'admin') {
      return next('/admin/dashboard')
    } else if (userRole === 'manager') {
      return next('/manager/dashboard')
    } else if (userRole === 'employee') {
      return next('/employee/dashboard')
    }
    return next('/employee/dashboard') // fallback
  }

  // Check role-based access
  if (to.meta.role && userRole !== to.meta.role) {
    // User doesn't have the required role, redirect to appropriate dashboard
    if (userRole === 'admin') {
      return next('/admin/dashboard')
    } else if (userRole === 'manager') {
      return next('/manager/dashboard')
    } else if (userRole === 'employee') {
      return next('/employee/dashboard')
    }
    return next('/login') // fallback if no valid role
  }

  return next()
})

export default router
