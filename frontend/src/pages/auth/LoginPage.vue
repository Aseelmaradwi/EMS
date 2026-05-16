<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { AxiosError } from 'axios'
import AuthLayout from '../../layouts/AuthLayout.vue'
import { useAuthStore } from '../../stores/auth.store'

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
  general: '',
})

const loading = ref(false)
const successMessage = ref('')

function resetMessages() {
  errors.email = ''
  errors.password = ''
  errors.general = ''
  successMessage.value = ''
}

function handleRequestError(error) {
  if (!(error instanceof AxiosError) || !error.response) {
    errors.general = 'Unable to connect to server. Please try again.'
    return
  }

  const { status, data } = error.response

  if (status === 401) {
    errors.general = data?.message || 'Invalid credentials. Please check your email and password.'
    return
  }

  if (status === 422) {
    const validationErrors = data?.errors || {}

    if (validationErrors.email?.length) {
      errors.email = validationErrors.email[0]
    }

    if (validationErrors.password?.length) {
      errors.password = validationErrors.password[0]
    }

    if (!errors.email && !errors.password) {
      errors.general = data?.message || 'Please review your input and try again.'
    }

    return
  }

  errors.general = data?.message || 'Login failed. Please try again in a moment.'
}

async function onSubmit() {
  if (loading.value) {
    return
  }

  resetMessages()
  loading.value = true

  try {
    await authStore.login({
      email: form.email,
      password: form.password,
    })

    successMessage.value = 'Login successful. Redirecting to dashboard...'
    setTimeout(() => {
      router.push('/dashboard')
    }, 500)
  } catch (error) {
    handleRequestError(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="soft-panel rounded-3xl p-8 sm:p-10">
      <div>
        <h2 class="font-display text-3xl font-bold text-slate-900">Welcome Back</h2>
        <p class="mt-2 text-sm text-slate-500">Login to your account</p>
      </div>

      <div
        v-if="errors.general"
        class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
      >
        {{ errors.general }}
      </div>

      <div
        v-if="successMessage"
        class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
      >
        {{ successMessage }}
      </div>

      <form class="mt-7 space-y-5" @submit.prevent="onSubmit">
        <div>
          <label for="email" class="field-label">Email</label>
          <input
            id="email"
            v-model.trim="form.email"
            type="email"
            class="field-input"
            placeholder="you@company.com"
            autocomplete="email"
            :disabled="loading"
          />
          <p class="field-error">{{ errors.email }}</p>
        </div>

        <div>
          <label for="password" class="field-label">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="field-input"
            placeholder="Enter your password"
            autocomplete="current-password"
            :disabled="loading"
          />
          <p class="field-error">{{ errors.password }}</p>
        </div>

        <button
          type="submit"
          class="mt-2 inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-300/40 transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-70"
          :disabled="loading"
        >
          <svg
            v-if="loading"
            class="mr-2 h-4 w-4 animate-spin"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path
              class="opacity-90"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            />
          </svg>
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
    </div>
  </AuthLayout>
</template>
