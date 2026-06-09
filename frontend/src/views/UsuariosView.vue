<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const usuarios = ref([])
const loading = ref(false)
const guardando = ref(false)
const error = ref('')
const mensaje = ref('')

const editandoId = ref(null)

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function limpiarFormulario() {
  editandoId.value = null
  form.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  }
  error.value = ''
  mensaje.value = ''
}

async function cargarUsuarios() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/usuarios')
    usuarios.value = data
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudieron cargar los usuarios.'
  } finally {
    loading.value = false
  }
}

async function guardarUsuario() {
  guardando.value = true
  error.value = ''
  mensaje.value = ''

  try {
    if (editandoId.value) {
      const payload = {
        name: form.value.name,
        email: form.value.email,
      }

      if (form.value.password) {
        payload.password = form.value.password
        payload.password_confirmation = form.value.password_confirmation
      }

      const { data } = await api.put(`/usuarios/${editandoId.value}`, payload)
      mensaje.value = data.message || 'Usuario actualizado correctamente.'
    } else {
      const { data } = await api.post('/usuarios', form.value)
      mensaje.value = data.message || 'Usuario creado correctamente.'
    }

    limpiarFormulario()
    await cargarUsuarios()
  } catch (e) {
    if (e.response?.data?.errors) {
      const errores = Object.values(e.response.data.errors).flat()
      error.value = errores.join(' ')
    } else {
      error.value = e.response?.data?.message || 'No se pudo guardar el usuario.'
    }
  } finally {
    guardando.value = false
  }
}

function editarUsuario(usuario) {
  editandoId.value = usuario.id
  form.value = {
    name: usuario.name,
    email: usuario.email,
    password: '',
    password_confirmation: '',
  }
  error.value = ''
  mensaje.value = ''
}

async function eliminarUsuario(usuario) {
  const confirmar = window.confirm(`¿Eliminar usuario ${usuario.email}?`)

  if (!confirmar) return

  error.value = ''
  mensaje.value = ''

  try {
    const { data } = await api.delete(`/usuarios/${usuario.id}`)
    mensaje.value = data.message || 'Usuario eliminado correctamente.'
    await cargarUsuarios()
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo eliminar el usuario.'
  }
}

function volverAspirantes() {
  window.location.href = '/aspirantes'
}

onMounted(() => {
  cargarUsuarios()
})
</script>

<template>
  <div class="usuarios-page">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-title-main">SIVICE</div>
        <div class="sidebar-subtitle">Coordinación General de Control Escolar</div>
      </div>

      <a href="/aspirantes" class="side-link">PINGRESO</a>
      <a href="/usuarios" class="side-link active-module">USUARIOS</a>
      <a href="#" class="side-link">OFMAYOR</a>
      <a href="#" class="side-link">FASE2</a>
      <a href="#" class="side-link">EGRESOS</a>
    </aside>

    <main class="main-panel">
      <div class="module-bar">
        <div class="module-title">USUARIOS</div>
      </div>

      <section class="content-area">
        <div class="content-wrapper">
          <div class="page-header">
            <span class="page-title">ADMINISTRACIÓN DE USUARIOS</span>

            <button class="btn-secondary" @click="volverAspirantes">
              Volver a PINGRESO
            </button>
          </div>

          <div class="usuarios-layout">
            <form class="user-form" @submit.prevent="guardarUsuario">
              <h2>{{ editandoId ? 'Editar usuario' : 'Crear usuario' }}</h2>

              <label>Nombre</label>
              <input v-model="form.name" type="text" required />

              <label>Correo / Usuario</label>
              <input v-model="form.email" type="email" required />

              <label>Contraseña</label>
              <input
                v-model="form.password"
                type="password"
                :required="!editandoId"
                placeholder="Mínimo 8 caracteres"
              />

              <label>Confirmar contraseña</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                :required="!editandoId"
              />

              <div v-if="error" class="alert error">{{ error }}</div>
              <div v-if="mensaje" class="alert success">{{ mensaje }}</div>

              <div class="form-actions">
                <button type="submit" class="btn-primary" :disabled="guardando">
                  {{ guardando ? 'Guardando...' : (editandoId ? 'Actualizar' : 'Crear usuario') }}
                </button>

                <button type="button" class="btn-secondary" @click="limpiarFormulario">
                  Limpiar
                </button>
              </div>
            </form>

            <div class="users-card">
              <h2>Usuarios registrados</h2>

              <div v-if="loading" class="empty-state">
                Cargando usuarios...
              </div>

              <table v-else class="users-table">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-if="usuarios.length === 0">
                    <td colspan="4" class="empty-state">
                      No hay usuarios registrados.
                    </td>
                  </tr>

                  <tr v-for="usuario in usuarios" :key="usuario.id">
                    <td>{{ usuario.name }}</td>
                    <td>{{ usuario.email }}</td>
                    <td>{{ usuario.created_at }}</td>
                    <td class="actions-cell">
                      <button class="btn-small" @click="editarUsuario(usuario)">
                        Editar
                      </button>
                      <button class="btn-small danger" @click="eliminarUsuario(usuario)">
                        Eliminar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
:global(html),
:global(body),
:global(#app) {
  margin: 0;
  width: 100%;
  height: 100%;
}

:global(body) {
  background: #05080f;
  font-family: Arial, Helvetica, sans-serif;
  color: #e5eef8;
}

.usuarios-page {
  display: grid;
  grid-template-columns: 245px 1fr;
  width: 100vw;
  height: 100vh;
  background: #06090f;
  overflow: hidden;
}

.sidebar {
  height: 100vh;
  overflow-y: auto;
  background: linear-gradient(180deg, #17181d 0%, #101217 100%);
  border-right: 1px solid #222d39;
}

.sidebar-header {
  padding: 14px 16px 12px;
  border-bottom: 1px solid #222d39;
}

.sidebar-title-main {
  color: #58a6ff;
  font-weight: 700;
  font-size: 27px;
}

.sidebar-subtitle {
  color: #8998a8;
  font-size: 11px;
  margin-top: 3px;
}

.side-link {
  display: block;
  padding: 12px 16px;
  color: #b8c6d6;
  text-decoration: none;
  border-bottom: 1px solid #1b2430;
  font-size: 13px;
}

.active-module {
  color: #64ffda;
  font-weight: bold;
  background: rgba(100, 255, 218, 0.06);
  border-left: 3px solid #64ffda;
}

.main-panel {
  height: 100vh;
  min-width: 0;
  overflow: hidden;
  background: #05080f;
  display: flex;
  flex-direction: column;
}

.module-bar {
  flex-shrink: 0;
  border-bottom: 1px solid #1a2430;
  background: #080b10;
}

.module-title {
  padding: 8px 22px;
  font-size: 13px;
  letter-spacing: 4px;
  color: #d9dee4;
}

.content-area {
  flex: 1;
  min-height: 0;
  padding: 10px 16px 12px;
  overflow: auto;
  display: flex;
}

.content-wrapper {
  width: 100%;
  min-height: 100%;
  background: linear-gradient(180deg, #151515 0%, #101010 100%);
  border: 1px solid #242424;
}

.page-header {
  border-bottom: 1px solid #2d2d2d;
  padding: 12px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 13px;
  font-weight: 700;
  color: #12a0ff;
}

.usuarios-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 18px;
  padding: 18px;
}

.user-form,
.users-card {
  background: #0d1420;
  border: 1px solid #24405f;
  border-radius: 10px;
  padding: 18px;
}

h2 {
  margin: 0 0 16px;
  color: #64ffda;
  font-size: 17px;
}

label {
  display: block;
  color: #8ecaff;
  font-size: 12px;
  margin-bottom: 5px;
}

input {
  width: 100%;
  box-sizing: border-box;
  background: #0d2238;
  border: 1px solid #1e4263;
  color: #fff;
  padding: 9px;
  border-radius: 5px;
  margin-bottom: 12px;
}

input:focus {
  outline: none;
  border-color: #64ffda;
}

.form-actions {
  display: flex;
  gap: 8px;
}

button {
  cursor: pointer;
}

.btn-primary,
.btn-secondary,
.btn-small {
  background: #15304a;
  color: #64ffda;
  border: 1px solid #64ffda;
  padding: 8px 12px;
  border-radius: 5px;
  font-size: 12px;
}

.btn-secondary {
  color: #fff;
  border-color: #444;
  background: #333;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: default;
}

.btn-small {
  padding: 5px 9px;
}

.btn-small.danger {
  background: #31161a;
  color: #ff9ea0;
  border-color: #c05f66;
}

.alert {
  padding: 9px;
  border-radius: 5px;
  font-size: 12px;
  margin-bottom: 12px;
}

.alert.error {
  background: #3e1a1a;
  color: #ff8a80;
  border: 1px solid #703232;
}

.alert.success {
  background: #123225;
  color: #64ffda;
  border: 1px solid #2f7d60;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 9px;
  border-bottom: 1px solid #222;
  font-size: 12px;
  text-align: left;
}

.users-table th {
  background: #252525;
  color: #e0e0e0;
}

.users-table td {
  color: #bfc7d1;
}

.actions-cell {
  display: flex;
  gap: 6px;
}

.empty-state {
  padding: 20px;
  color: #8b98a8;
  text-align: center;
}

@media (max-width: 1000px) {
  .usuarios-page {
    grid-template-columns: 1fr;
    height: auto;
    min-height: 100vh;
    overflow: auto;
  }

  .sidebar {
    height: auto;
  }

  .usuarios-layout {
    grid-template-columns: 1fr;
  }
}
</style>