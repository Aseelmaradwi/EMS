<template>
  <div class="auth-container" ref="authContainer">
    <!-- Animated Background -->
    <div class="animated-bg">
      <canvas ref="particleCanvas" class="particle-canvas"></canvas>
      <div class="gradient-orbs">
        <div class="orb orb-1" ref="orb1"></div>
        <div class="orb orb-2" ref="orb2"></div>
        <div class="orb orb-3" ref="orb3"></div>
      </div>
    </div>

    <!-- Main Auth Card -->
    <div class="auth-card" ref="authCard">
      <!-- Floating Elements -->
      <div class="floating-elements">
        <div class="float-element float-1" ref="float1">✨</div>
        <div class="float-element float-2" ref="float2">🌟</div>
        <div class="float-element float-3" ref="float3">💫</div>
        <div class="float-element float-4" ref="float4">🎯</div>
      </div>

      <!-- Logo Section -->
      <div class="logo-section" ref="logoSection">
        <div class="logo-container">
          <div class="logo-ring" ref="logoRing"></div>
          <div class="logo-icon">👤</div>
        </div>
        <h1 class="app-title" ref="appTitle">Employee Portal</h1>
        <p class="app-subtitle" ref="appSubtitle">Welcome to the Future</p>
      </div>

      <!-- Form Section -->
      <div class="form-section" ref="formSection">
        <div class="tab-container">
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'login' }"
            @click="switchTab('login')"
            ref="loginTab"
          >
            Login
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'register' }"
            @click="switchTab('register')"
            ref="registerTab"
          >
            Register
          </button>
        </div>

        <!-- Login Form -->
        <form v-if="activeTab === 'login'" class="auth-form" @submit.prevent="handleLogin" ref="loginForm">
          <div class="input-group" ref="emailGroup">
            <div class="input-wrapper">
              <input 
                type="email" 
                v-model="loginData.email"
                class="auth-input"
                placeholder="Email"
                required
              >
              <div class="input-border"></div>
              <div class="input-icon">📧</div>
            </div>
          </div>

          <div class="input-group" ref="passwordGroup">
            <div class="input-wrapper">
              <input 
                :type="showPassword ? 'text' : 'password'"
                v-model="loginData.password"
                class="auth-input"
                placeholder="Password"
                required
              >
              <div class="input-border"></div>
              <div class="input-icon">🔒</div>
              <button 
                type="button" 
                class="password-toggle"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox-container">
              <input type="checkbox" v-model="rememberMe">
              <span class="checkmark"></span>
              Remember me
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="auth-btn" ref="loginBtn" :disabled="isLoading">
            <span class="btn-text">{{ isLoading ? 'Logging in...' : 'Login' }}</span>
            <div class="btn-glow"></div>
          </button>
        
          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
        </form>

        <!-- Register Form -->
        <form v-if="activeTab === 'register'" class="auth-form" @submit.prevent="handleRegister" ref="registerForm">
          <div class="input-group" ref="regNameGroup">
            <div class="input-wrapper">
              <input 
                type="text" 
                v-model="registerData.name"
                class="auth-input"
                placeholder="Full Name"
                required
              >
              <div class="input-border"></div>
              <div class="input-icon">👤</div>
            </div>
          </div>

          <div class="input-group" ref="regEmailGroup">
            <div class="input-wrapper">
              <input 
                type="email" 
                v-model="registerData.email"
                class="auth-input"
                placeholder="Email"
                required
              >
              <div class="input-border"></div>
              <div class="input-icon">📧</div>
            </div>
          </div>

          <div class="input-group" ref="regPasswordGroup">
            <div class="input-wrapper">
              <input 
                :type="showRegPassword ? 'text' : 'password'"
                v-model="registerData.password"
                class="auth-input"
                placeholder="Password"
                required
              >
              <div class="input-border"></div>
              <div class="input-icon">🔒</div>
              <button 
                type="button" 
                class="password-toggle"
                @click="showRegPassword = !showRegPassword"
              >
                {{ showRegPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>
          </div>

          <div class="input-group" ref="regConfirmGroup">
            <div class="input-wrapper">
              <input 
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="registerData.confirmPassword"
                class="auth-input"
                placeholder="Confirm Password"
                required
              >
              <div class="input-border"></div>
              <div class="input-icon">🔒</div>
              <button 
                type="button" 
                class="password-toggle"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                {{ showConfirmPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>
          </div>

          <button type="submit" class="auth-btn" ref="registerBtn">
            <span class="btn-text">Create Account</span>
            <div class="btn-glow"></div>
          </button>
        </form>

        <!-- Social Login -->
        <div class="social-section" ref="socialSection">
          <div class="divider">
            <span>OR</span>
          </div>
          <div class="social-buttons">
            <button class="social-btn google" ref="googleBtn">
              <span class="social-icon">🌐</span>
              <span>Google</span>
            </button>
            <button class="social-btn microsoft" ref="microsoftBtn">
              <span class="social-icon">🪟</span>
              <span>Microsoft</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="success-modal" ref="successModal">
      <div class="modal-content">
        <div class="success-icon">🎉</div>
        <h3>Success!</h3>
        <p>{{ successMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { gsap } from 'gsap'
import { useMotion } from '@vueuse/motion'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.store'

// Reactive data
const activeTab = ref('login')
const showPassword = ref(false)
const showRegPassword = ref(false)
const showConfirmPassword = ref(false)
const rememberMe = ref(false)
const showSuccessModal = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

const loginData = ref({
  email: '',
  password: ''
})

const registerData = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
})

// Router and store
const router = useRouter()
const authStore = useAuthStore()

// Template refs
const authContainer = ref(null)
const authCard = ref(null)
const logoSection = ref(null)
const appTitle = ref(null)
const appSubtitle = ref(null)
const formSection = ref(null)
const loginTab = ref(null)
const registerTab = ref(null)
const loginForm = ref(null)
const registerForm = ref(null)
const loginBtn = ref(null)
const registerBtn = ref(null)
const socialSection = ref(null)
const googleBtn = ref(null)
const microsoftBtn = ref(null)
const successModal = ref(null)
const particleCanvas = ref(null)
const orb1 = ref(null)
const orb2 = ref(null)
const orb3 = ref(null)
const float1 = ref(null)
const float2 = ref(null)
const float3 = ref(null)
const float4 = ref(null)
const logoRing = ref(null)
const emailGroup = ref(null)
const passwordGroup = ref(null)
const regNameGroup = ref(null)
const regEmailGroup = ref(null)
const regPasswordGroup = ref(null)
const regConfirmGroup = ref(null)

// Particle animation
let particles = []
let animationId = null

// Methods
const switchTab = (tab) => {
  activeTab.value = tab
  
  if (tab === 'login') {
    gsap.to(registerForm.value, {
      opacity: 0,
      x: 50,
      duration: 0.3,
      onComplete: () => {
        loginForm.value.style.display = 'block'
        registerForm.value.style.display = 'none'
        gsap.fromTo(loginForm.value, 
          { opacity: 0, x: -50 },
          { opacity: 1, x: 0, duration: 0.3 }
        )
      }
    })
  } else {
    gsap.to(loginForm.value, {
      opacity: 0,
      x: -50,
      duration: 0.3,
      onComplete: () => {
        registerForm.value.style.display = 'block'
        loginForm.value.style.display = 'none'
        gsap.fromTo(registerForm.value, 
          { opacity: 0, x: 50 },
          { opacity: 1, x: 0, duration: 0.3 }
        )
      }
    })
  }
}

const handleLogin = async () => {
  try {
    isLoading.value = true
    errorMessage.value = ''
    
    gsap.to(loginBtn.value, {
      scale: 0.95,
      duration: 0.1,
      yoyo: true,
      repeat: 1
    })
    
    // Use real API login
    await authStore.login({
      email: loginData.value.email,
      password: loginData.value.password
    })
    
    showSuccess('Login successful! Redirecting...')
    
    // Redirect based on role from API response
    setTimeout(() => {
      const redirectPath = authStore.getRedirectPath()
      router.push(redirectPath)
    }, 2000)
    
  } catch (error) {
    console.error('Login failed:', error)
    errorMessage.value = error.message || 'Login failed. Please try again.'
    isLoading.value = false
  }
}

const handleRegister = () => {
  if (registerData.value.password !== registerData.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match!'
    return
  }
  
  // TODO: Implement registration API call
  console.log('Registration data:', registerData.value)
}

const showSuccess = (message) => {
  successMessage.value = message
  showSuccessModal.value = true
  
  gsap.fromTo(successModal.value, 
    { opacity: 0, scale: 0.8 },
    { opacity: 1, scale: 1, duration: 0.3 }
  )
  
  setTimeout(() => {
    gsap.to(successModal.value, {
      opacity: 0,
      scale: 0.8,
      duration: 0.3,
      onComplete: () => {
        showSuccessModal.value = false
      }
    })
  }, 2000)
}

const initParticles = () => {
  const canvas = particleCanvas.value
  const ctx = canvas.getContext('2d')
  
  const resizeCanvas = () => {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  }
  
  resizeCanvas()
  window.addEventListener('resize', resizeCanvas)
  
  // Create particles
  for (let i = 0; i < 50; i++) {
    particles.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      vx: (Math.random() - 0.5) * 0.5,
      vy: (Math.random() - 0.5) * 0.5,
      radius: Math.random() * 2 + 1,
      opacity: Math.random() * 0.5 + 0.2
    })
  }
  
  const animate = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    
    particles.forEach(particle => {
      particle.x += particle.vx
      particle.y += particle.vy
      
      if (particle.x < 0 || particle.x > canvas.width) particle.vx *= -1
      if (particle.y < 0 || particle.y > canvas.height) particle.vy *= -1
      
      ctx.beginPath()
      ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2)
      ctx.fillStyle = `rgba(147, 51, 234, ${particle.opacity})`
      ctx.fill()
    })
    
    // Draw connections
    particles.forEach((p1, i) => {
      particles.slice(i + 1).forEach(p2 => {
        const distance = Math.sqrt((p1.x - p2.x) ** 2 + (p1.y - p2.y) ** 2)
        if (distance < 100) {
          ctx.beginPath()
          ctx.moveTo(p1.x, p1.y)
          ctx.lineTo(p2.x, p2.y)
          ctx.strokeStyle = `rgba(147, 51, 234, ${0.1 * (1 - distance / 100)})`
          ctx.stroke()
        }
      })
    })
    
    animationId = requestAnimationFrame(animate)
  }
  
  animate()
}

onMounted(async () => {
  await nextTick()
  
  // Initialize particles
  initParticles()
  
  // Initial animations
  gsap.fromTo(authCard.value, 
    { opacity: 0, y: 50, scale: 0.9 },
    { opacity: 1, y: 0, scale: 1, duration: 1, ease: "power3.out" }
  )
  
  // Logo animation
  gsap.fromTo(logoRing.value, 
    { rotation: 0, scale: 0 },
    { rotation: 360, scale: 1, duration: 1.5, ease: "back.out(1.7)" }
  )
  
  gsap.fromTo([appTitle.value, appSubtitle.value], 
    { opacity: 0, y: 20 },
    { opacity: 1, y: 0, duration: 0.8, stagger: 0.2, delay: 0.5 }
  )
  
  // Form section animation
  gsap.fromTo(formSection.value, 
    { opacity: 0, x: -30 },
    { opacity: 1, x: 0, duration: 0.8, delay: 0.8 }
  )
  
  // Input groups stagger animation
  const inputGroups = [emailGroup.value, passwordGroup.value]
  gsap.fromTo(inputGroups, 
    { opacity: 0, x: -20 },
    { opacity: 1, x: 0, duration: 0.5, stagger: 0.1, delay: 1 }
  )
  
  // Floating elements animation
  const floatElements = [float1.value, float2.value, float3.value, float4.value]
  floatElements.forEach((el, i) => {
    gsap.to(el, {
      y: "random(-20, 20)",
      x: "random(-10, 10)",
      rotation: "random(-10, 10)",
      duration: "random(2, 4)",
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut",
      delay: i * 0.2
    })
  })
  
  // Orb animations
  gsap.to(orb1.value, {
    x: "random(-50, 50)",
    y: "random(-50, 50)",
    duration: 10,
    repeat: -1,
    yoyo: true,
    ease: "sine.inOut"
  })
  
  gsap.to(orb2.value, {
    x: "random(-30, 30)",
    y: "random(-30, 30)",
    duration: 8,
    repeat: -1,
    yoyo: true,
    ease: "sine.inOut",
    delay: 1
  })
  
  gsap.to(orb3.value, {
    x: "random(-40, 40)",
    y: "random(-40, 40)",
    duration: 12,
    repeat: -1,
    yoyo: true,
    ease: "sine.inOut",
    delay: 2
  })
  
  // Social buttons animation
  gsap.fromTo([googleBtn.value, microsoftBtn.value], 
    { opacity: 0, y: 20 },
    { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 1.5 }
  )
})
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.animated-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.particle-canvas {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.gradient-orbs {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(40px);
  opacity: 0.6;
}

.orb-1 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(147, 51, 234, 0.8) 0%, transparent 70%);
  top: -100px;
  left: -100px;
}

.orb-2 {
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.8) 0%, transparent 70%);
  bottom: -50px;
  right: -50px;
}

.orb-3 {
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(236, 72, 153, 0.8) 0%, transparent 70%);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.auth-card {
  position: relative;
  z-index: 10;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 3rem;
  width: 90%;
  max-width: 450px;
  box-shadow: 
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.floating-elements {
  position: absolute;
  top: -20px;
  left: -20px;
  width: calc(100% + 40px);
  height: calc(100% + 40px);
  pointer-events: none;
  z-index: 15;
}

.float-element {
  position: absolute;
  font-size: 1.5rem;
  opacity: 0.6;
}

.float-1 { top: 10%; left: 10%; }
.float-2 { top: 15%; right: 15%; }
.float-3 { bottom: 20%; left: 8%; }
.float-4 { bottom: 10%; right: 10%; }

.logo-section {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo-container {
  position: relative;
  display: inline-block;
  margin-bottom: 1.5rem;
}

.logo-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80px;
  height: 80px;
  border: 3px solid transparent;
  border-top-color: #9333ea;
  border-right-color: #3b82f6;
  border-radius: 50%;
  animation: rotate 2s linear infinite;
}

@keyframes rotate {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.logo-icon {
  font-size: 3rem;
  position: relative;
  z-index: 1;
}

.app-title {
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.app-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
}

.tab-container {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  background: rgba(147, 51, 234, 0.1);
  padding: 0.25rem;
  border-radius: 12px;
}

.tab-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  background: transparent;
  color: #6b7280;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: white;
  color: #9333ea;
  box-shadow: 0 2px 8px rgba(147, 51, 234, 0.2);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.input-group {
  position: relative;
}

.input-wrapper {
  position: relative;
}

.auth-input {
  width: 100%;
  padding: 1rem 1rem 1rem 3rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.auth-input:focus {
  outline: none;
  border-color: #9333ea;
  box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
}

.input-border {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #9333ea, #3b82f6);
  transition: width 0.3s ease;
}

.auth-input:focus ~ .input-border {
  width: 100%;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.2rem;
  opacity: 0.5;
}

.password-toggle {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  opacity: 0.5;
  transition: opacity 0.3s ease;
}

.password-toggle:hover {
  opacity: 0.8;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
}

.checkbox-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-container input[type="checkbox"] {
  display: none;
}

.checkmark {
  width: 18px;
  height: 18px;
  border: 2px solid #e5e7eb;
  border-radius: 4px;
  position: relative;
  transition: all 0.3s ease;
}

.checkbox-container input[type="checkbox"]:checked + .checkmark {
  background: #9333ea;
  border-color: #9333ea;
}

.checkbox-container input[type="checkbox"]:checked + .checkmark::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 12px;
}

.forgot-link {
  color: #9333ea;
  text-decoration: none;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: #7c3aed;
}

.auth-btn {
  position: relative;
  padding: 1rem;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.auth-btn:hover {
  transform: translateY(-2px);
}

.btn-glow {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.auth-btn:hover .btn-glow {
  left: 100%;
}

.social-section {
  margin-top: 2rem;
}

.divider {
  text-align: center;
  position: relative;
  margin: 1.5rem 0;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #e5e7eb;
}

.divider span {
  background: rgba(255, 255, 255, 0.95);
  padding: 0 1rem;
  color: #6b7280;
  font-size: 0.9rem;
  position: relative;
}

.social-buttons {
  display: flex;
  gap: 1rem;
}

.social-btn {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  color: #374151;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.social-btn:hover {
  border-color: #9333ea;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(147, 51, 234, 0.2);
}

.social-icon {
  font-size: 1.2rem;
}

.success-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  text-align: center;
  max-width: 300px;
}

.success-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.modal-content h3 {
  margin-bottom: 0.5rem;
  color: #9333ea;
}

.modal-content p {
  color: #6b7280;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  border-radius: 8px;
  font-size: 0.9rem;
  text-align: center;
}

.auth-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .auth-card {
    padding: 2rem;
    margin: 1rem;
  }
  
  .app-title {
    font-size: 1.5rem;
  }
  
  .social-buttons {
    flex-direction: column;
  }
}
</style>
