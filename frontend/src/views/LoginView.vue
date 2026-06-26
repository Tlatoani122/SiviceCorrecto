<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const email = ref('admin@sivice.local')
const password = ref('Admin12345')
const loading = ref(false)
const error = ref('')

async function login() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('sivice_token', data.token)
    localStorage.setItem('sivice_user', JSON.stringify(data.user))

    router.push('/aspirantes')
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo iniciar sesión.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrapper">
    <div class="brand-panel">
      <div class="decorations">
        <div class="circle c1"></div>
        <div class="circle c2"></div>
        <div class="dots"></div>
      </div>
      <div class="brand-content">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5f/Escudo_UdeG.svg" alt="Escudo UdeG" class="udeg-logo" />
        <h1 class="university-title">UNIVERSIDAD DE GUADALAJARA</h1>
        <p class="university-subtitle">Red Universitaria e Institución Benemérita de Jalisco</p>
      </div>
    </div>

    <div class="form-panel">
      <div class="login-box">
        <div class="suite-badge">
          <div class="badge-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <span class="suite-name">SUITE CGCE</span>
          <span class="suite-desc">Ingresa tus credenciales para iniciar sesión</span>
        </div>

        <form @submit.prevent="login" class="suite-form">
          <div class="input-group">
            <label for="email">Correo electrónico</label>
            <input id="email" v-model="email" type="email" autocomplete="username" placeholder="email@example.com" />
          </div>

          <div class="input-group">
            <label for="password">Contraseña</label>
            <input id="password" v-model="password" type="password" autocomplete="current-password" placeholder="••••••••" />
          </div>

          <div class="remember-me">
            <input type="checkbox" id="remember" />
            <label for="remember" class="check-label">Recordarme</label>
          </div>

          <div v-if="error" class="suite-error">
            <i class="fas fa-exclamation-circle"></i> {{ error }}
          </div>

          <button type="submit" class="btn-submit" :disabled="loading">
            {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-wrapper {
  display: flex;
  min-height: 100vh;
  width: 100vw;
  font-family: system-ui, -apple-system, sans-serif;
  background: #ffffff;
  overflow: hidden;
}

/* Panel Izquierdo */
.brand-panel {
  flex: 1.2;
  background: linear-gradient(135deg, #4d7c8a 0%, #76a89c 50%, #abcbb5 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 40px;
}

.brand-content {
  text-align: center;
  color: #0f172a;
  z-index: 10;
}

.udeg-logo {
  width: 140px;
  height: auto;
  margin-bottom: 24px;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
}

.university-title {
  font-size: 2.2rem;
  font-weight: 500;
  margin: 0 0 8px 0;
  letter-spacing: 1px;
}

.university-subtitle {
  font-size: 1rem;
  opacity: 0.85;
  margin: 0;
  font-weight: 400;
}

/* Decoraciones Geométricas */
.decorations {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  overflow: hidden;
}
.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
}
.c1 { width: 120px; height: 120px; top: -40px; left: 40px; }
.c2 { width: 80px; height: 80px; bottom: 80px; left: -20px; }

/* Panel Derecho */
.form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  padding: 40px;
}

.login-box {
  width: 100%;
  max-width: 380px;
}

.suite-badge {
  text-align: center;
  margin-bottom: 32px;
}

.badge-icon {
  width: 48px;
  height: 48px;
  background: #eff6ff;
  color: #3b82f6;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 16px;
}

.suite-name {
  display: block;
  font-size: 1.4rem;
  font-weight: 600;
  color: #0f172a;
}

.suite-desc {
  display: block;
  font-size: 0.85rem;
  color: #64748b;
  margin-top: 4px;
}

/* Formulario */
.input-group {
  margin-bottom: 20px;
}

.input-group label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
}

.input-group input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #0f172a;
  box-sizing: border-box;
  transition: all 0.2s;
}

.input-group input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 24px;
}

.check-label {
  font-size: 0.85rem;
  color: #64748b;
  cursor: pointer;
}

.btn-submit {
  width: 100%;
  padding: 11px;
  background: #3b82f6;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-submit:hover {
  background: #2563eb;
}

.btn-submit:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.suite-error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fee2e2;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.8rem;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 868px) {
  .brand-panel { display: none; }
}
</style>