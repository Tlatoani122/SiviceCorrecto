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
  <div class="login-page">
    <div class="login-card">
      <div class="brand">SIVICE</div>
      <div class="subtitle">Coordinación General de Control Escolar</div>

      <div class="form-title">Inicio de sesión</div>

      <form @submit.prevent="login">
        <label>Usuario / Correo</label>
        <input v-model="email" type="email" autocomplete="username" />

        <label>Contraseña</label>
        <input v-model="password" type="password" autocomplete="current-password" />

        <div v-if="error" class="error">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Validando...' : 'INGRESAR' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at top, #10233a 0%, #05080f 55%);
  color: #e5eef8;
  font-family: Arial, Helvetica, sans-serif;
}

.login-card {
  width: 360px;
  background: #0d1420;
  border: 1px solid #24405f;
  border-radius: 10px;
  padding: 28px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
}

.brand {
  color: #58a6ff;
  font-size: 34px;
  font-weight: 700;
  letter-spacing: 2px;
  text-align: center;
  display: block;
  width: 100%;
}

.subtitle {
  color: #8998a8;
  font-size: 12px;
  margin-top: 4px;
  margin-bottom: 28px;
  text-align: center;
}

.form-title {
  color: #64ffda;
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 18px;
}

label {
  display: block;
  color: #8ecaff;
  font-size: 12px;
  margin-bottom: 6px;
}

input {
  width: 100%;
  box-sizing: border-box;
  background: #0d2238;
  border: 1px solid #1e4263;
  color: #fff;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 14px;
}

input:focus {
  outline: none;
  border-color: #64ffda;
}

button {
  width: 100%;
  margin-top: 8px;
  background: #15304a;
  color: #64ffda;
  border: 1px solid #64ffda;
  padding: 10px;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 700;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}

.error {
  background: #3e1a1a;
  color: #ff8a80;
  border: 1px solid #703232;
  padding: 9px;
  border-radius: 5px;
  font-size: 12px;
  margin-bottom: 12px;
}
</style>