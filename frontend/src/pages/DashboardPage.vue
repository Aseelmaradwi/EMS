<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'

const router = useRouter()
const authStore = useAuthStore()

const displayName = computed(() => {
  return authStore.user?.name || authStore.user?.email || 'Employee'
})

const displayEmail = computed(() => authStore.user?.email || 'No email available')

function logout() {
  authStore.logout()
  router.push('/login')
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center p-6">
    <section class="soft-panel w-full max-w-2xl rounded-3xl p-8 sm:p-10">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">EMS Dashboard</p>
      <h1 class="font-display mt-3 text-3xl font-bold text-slate-900">
        Welcome, {{ displayName }}
      </h1>
      <p class="mt-3 text-slate-600">
        You are successfully authenticated with the Laravel API.
      </p>

      <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <p class="text-sm font-semibold text-slate-700">Signed in as</p>
        <p class="mt-1 text-sm text-slate-500">{{ displayEmail }}</p>
      </div>

      <button
        type="button"
        class="mt-8 inline-flex rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
        @click="logout"
      >
        Logout
      </button>
    </section>
  </main>
</template>
