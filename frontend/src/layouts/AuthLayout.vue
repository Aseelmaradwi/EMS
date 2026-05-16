<template>
  <main class="min-h-screen py-4 sm:py-6 lg:py-8">
    <div class="mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-7xl overflow-hidden rounded-3xl border border-slate-200 bg-white/70 shadow-[0_35px_80px_rgba(15,23,42,0.15)] backdrop-blur-sm">
      <section class="relative hidden w-[46%] overflow-hidden lg:flex lg:flex-col lg:justify-between">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-600 to-violet-600"></div>
        <div class="absolute -top-24 -left-16 h-72 w-72 rounded-full bg-cyan-300/30 blur-3xl"></div>
        <div class="absolute -bottom-20 right-[-3.2rem] h-72 w-72 rounded-full bg-fuchsia-300/30 blur-3xl"></div>

        <div class="relative z-10 p-10 text-white fade-rise">
          <p class="mb-5 inline-flex rounded-full border border-white/35 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.2em]">
            EMPLOYEE MANAGEMENT SYSTEM
          </p>
          <h1 class="font-display text-4xl font-bold leading-tight">
            Manage teams with clarity and confidence.
          </h1>
          <p class="mt-5 max-w-sm text-sm leading-7 text-blue-100/95">
            Securely access attendance, payroll, and performance tools from a single intelligent workspace.
          </p>
        </div>

        <div class="relative z-10 p-10 pt-0 text-blue-50">
          <div class="rounded-2xl border border-white/30 bg-white/10 p-5 backdrop-blur-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">System Status</p>
            <p class="mt-2 text-lg font-bold">All core services operational</p>
            <p class="mt-1 text-sm text-blue-100/90">Identity service connected to Laravel API.</p>
          </div>
        </div>
      </section>

      <section class="flex w-full items-center justify-center bg-gradient-to-b from-white to-slate-50 px-6 py-10 sm:px-10 lg:w-[54%]">
        <div class="w-full max-w-md fade-rise">
          <slot />
        </div>
      </section>
    </div>
  </main>
</template>
