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

      <nav class="sidebar-nav">
        <a href="/aspirantes" class="side-link">
          <i class="fas fa-user-graduate"></i> PINGRESO
        </a>
        <a href="/usuarios" class="side-link active-module">
          <i class="fas fa-users"></i> USUARIOS
        </a>
        <a href="#" class="side-link"><i class="fas fa-folder"></i> OFMAYOR</a>
        <a href="#" class="side-link"><i class="fas fa-layer-group"></i> FASE2</a>
        <a href="#" class="side-link"><i class="fas fa-door-open"></i> EGRESOS</a>
      </nav>
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
              <i class="fas fa-arrow-left"></i> Volver a PINGRESO
            </button>
          </div>

          <div class="usuarios-layout">
            <form class="user-form" @submit.prevent="guardarUsuario">
              <h2>{{ editandoId ? 'Editar usuario' : 'Crear usuario' }}</h2>

              <div class="input-group">
                <label>Nombre</label>
                <input v-model="form.name" type="text" required placeholder="Nombre completo" />
              </div>

              <div class="input-group">
                <label>Correo / Usuario</label>
                <input v-model="form.email" type="email" required placeholder="ejemplo@udg.mx" />
              </div>

              <div class="input-group">
                <label>Contraseña</label>
                <input
                  v-model="form.password"
                  type="password"
                  :required="!editandoId"
                  placeholder="Mínimo 8 caracteres"
                />
              </div>

              <div class="input-group">
                <label>Confirmar contraseña</label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  :required="!editandoId"
                  placeholder="Repite la contraseña"
                />
              </div>

              <div v-if="error" class="alert error"><i class="fas fa-exclamation-circle"></i> {{ error }}</div>
              <div v-if="mensaje" class="alert success"><i class="fas fa-check-circle"></i> {{ mensaje }}</div>

              <div class="form-actions">
                <button type="submit" class="btn-primary" :disabled="guardando">
                  {{ guardando ? 'Guardando...' : (editandoId ? 'Actualizar' : 'Crear usuario') }}
                </button>

                <button type="button" class="btn-clean" @click="limpiarFormulario">
                  Limpiar
                </button>
              </div>
            </form>

            <div class="users-card">
              <h2>Usuarios registrados</h2>

              <div v-if="loading" class="empty-state">
                <i class="fas fa-spinner fa-spin"></i> Cargando usuarios...
              </div>

              <div v-else class="table-container">
                <table class="users-table">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Correo</th>
                      <th>Creado</th>
                      <th class="text-center">Acciones</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-if="usuarios.length === 0">
                      <td colspan="4" class="empty-state">
                        No hay usuarios registrados.
                      </td>
                    </tr>

                    <tr v-for="usuario in usuarios" :key="usuario.id">
                      <td class="font-medium">{{ usuario.name }}</td>
                      <td class="text-muted">{{ usuario.email }}</td>
                      <td class="text-muted">{{ new Date(usuario.created_at).toLocaleDateString() }}</td>
                      <td class="actions-cell">
                        <button class="btn-action edit" @click="editarUsuario(usuario)">
                          <i class="far fa-edit"></i> Editar
                        </button>
                        <button class="btn-action danger" @click="eliminarUsuario(usuario)">
                          <i class="far fa-trash-alt"></i> Eliminar
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
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
  overflow: hidden;
}

:global(body) {
  background: #f1f5f9;
  font-family: system-ui, -apple-system, sans-serif;
  color: #1e293b;
}

.usuarios-page {
  display: grid;
  grid-template-columns: 260px 1fr;
  width: 100vw;
  height: 100vh;
  background: #f8fafc;
  overflow: hidden;
}

.sidebar {
  height: 100vh;
  overflow-y: auto;
  background: #ffffff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.sidebar-title-main {
  color: #1d4ed8;
  font-weight: 700;
  font-size: 24px;
  letter-spacing: 0.5px;
}

.sidebar-subtitle {
  color: #64748b;
  font-size: 11px;
  margin-top: 4px;
  line-height: 1.3;
}

.sidebar-nav {
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.side-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 16px;
  color: #475569;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s;
}

.side-link i {
  font-size: 1.1rem;
  color: #94a3b8;
}

.side-link:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.active-module {
  color: #1d4ed8;
  font-weight: 600;
  background: #eff6ff;
}

.active-module i {
  color: #1d4ed8;
}

.main-panel {
  height: 100vh;
  min-width: 0;
  overflow: hidden;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
}

.module-bar {
  flex-shrink: 0;
  border-bottom: 1px solid #e2e8f0;
  background: #ffffff;
}

.module-title {
  padding: 14px 24px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 3px;
  color: #475569;
}

.content-area {
  flex: 1;
  min-height: 0;
  padding: 16px 24px;
  overflow: hidden;
  display: flex;
}

.content-wrapper {
  width: 100%;
  height: 100%;
  min-height: 0;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  overflow: hidden;
}

.page-header {
  flex-shrink: 0;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  border-bottom: 1px solid #f1f5f9;
}

.page-title {
  font-size: 14px;
  font-weight: 700;
  color: #334155;
}

.btn-secondary {
  background: #ffffff;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #f8fafc;
  color: #0f172a;
}

.usuarios-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 20px;
  padding: 20px;
  flex: 1;
  min-height: 0;
  overflow: auto;
}

.user-form,
.users-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.01);
  display: flex;
  flex-direction: column;
}

h2 {
  margin: 0 0 20px;
  color: #0f172a;
  font-size: 16px;
  font-weight: 600;
}

.input-group {
  margin-bottom: 16px;
}

label {
  display: block;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}

input {
  width: 100%;
  box-sizing: border-box;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #0f172a;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 13px;
  transition: all 0.2s;
}

input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}

.btn-primary {
  background: #3b82f6;
  color: #ffffff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-primary:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-clean {
  background: #ffffff;
  color: #64748b;
  border: 1px solid #cbd5e1;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.btn-clean:hover {
  background: #f8fafc;
  color: #334155;
}

.alert {
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.alert.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fee2e2;
}

.alert.success {
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.table-container {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
  text-align: left;
}

.users-table th {
  background: #f8fafc;
  color: #475569;
  font-weight: 600;
}

.font-medium { font-weight: 500; color: #0f172a; }
.text-muted { color: #64748b; }

.actions-cell {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.btn-action {
  background: #ffffff;
  color: #3b82f6;
  border: 1px solid #3b82f6;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #3b82f6;
  color: #ffffff;
}

.btn-action.danger {
  color: #ef4444;
  border-color: #fca5a5;
}

.btn-action.danger:hover {
  background: #ef4444;
  color: #ffffff;
  border-color: #ef4444;
}

.empty-state {
  padding: 40px;
  color: #94a3b8;
  text-align: center;
  font-size: 14px;
}

@media (max-width: 1000px) {
  .usuarios-page { grid-template-columns: 1fr; }
  .sidebar { height: auto; }
  .usuarios-layout { grid-template-columns: 1fr; }
}
</style>