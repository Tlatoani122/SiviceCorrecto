<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import api from '@/services/api'

const loading = ref(false)
const cargandoDetalle = ref(false)
const cargandoExamen = ref(false)

const registros = ref([])
const busqueda = ref('')
const perPage = ref(50)
const page = ref(1)
const busquedaGlobal = ref(false)

const sortBy = ref('CALENDARIO')
const sortDir = ref('asc')

const ciclo = ref('05S')

const calendarios = [
  '93A', '93B', '94A', '94B', '94S', '95A', '95B', '95S',
  '96A', '96B', '96S', '97A', '97B', '97S', '98A', '98B', '98S',
  '99A', '99B', '99S', '00A', '00B', '00C', '00D', '00E',
  '01A', '01B', '01C', '02A', '02B', '02S', '03A', '03B', '03S', 
  '03T', '04A', '04B', '04S', '04T', '05A', '05S',
]

const detalle = ref(null)
const examen = ref(null)
const mostrarDetalle = ref(false)
const mostrarExamen = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
})

function paginasVisibles() {
  const total = pagination.value.last_page || 1
  const actual = pagination.value.current_page || 1
  const primeras = [1, 2, 3, 4].filter((p) => p <= total)
  const ultimas = [total - 3, total - 2, total - 1, total].filter((p) => p > 0)
  const centro = [actual - 1, actual, actual + 1].filter((p) => p > 0 && p <= total)
  const paginas = [...new Set([...primeras, ...centro, ...ultimas])].sort((a, b) => a - b)

  const resultado = []
  paginas.forEach((pagina, index) => {
    if (index > 0 && pagina - paginas[index - 1] > 1) {
      resultado.push('...')
    }
    resultado.push(pagina)
  })
  return resultado
}

async function cargar(newPage = 1) {
  loading.value = true
  page.value = newPage

  try {
    const params = {
      page: page.value,
      per_page: perPage.value,
      busqueda: busqueda.value,
      calendario: ciclo.value,
      global: busquedaGlobal.value ? 1 : 0,
      sort_by: sortBy.value,
      sort_dir: sortDir.value,
    }

    const response = await api.get('/aspirantes', { params })
    const paginator = response.data

    registros.value = paginator.data ?? []
    pagination.value = {
      current_page: paginator.current_page || 1,
      last_page: paginator.last_page || 1,
      total: paginator.total || 0,
      from: paginator.from || 0,
      to: paginator.to || 0,
    }
  } catch (error) {
    console.error('Error crítico cargando aspirantes de la API:', error)
    registros.value = []
  } finally {
    loading.value = false
  }
}

async function verDetalle(id) {
  cargandoDetalle.value = true
  mostrarDetalle.value = true
  mostrarExamen.value = false
  examen.value = null

  try {
    const response = await api.get(`/aspirantes/${id}`)
    detalle.value = response.data
  } catch (error) {
    console.error('Error cargando detalle:', error)
    detalle.value = null
  } finally {
    cargandoDetalle.value = false
  }
}

async function verExamen() {
  const obtId = detalle.value?.ID || detalle.value?.id;
  if (!obtId) return

  cargandoExamen.value = true
  mostrarExamen.value = true

  try {
    const response = await api.get(`/aspirantes/${obtId}/examen`)
    examen.value = response.data
  } catch (error) {
    console.error('Error cargando examen:', error)
    examen.value = null
  } finally {
    cargandoExamen.value = false
  }
}

function cerrarDetalle() {
  mostrarDetalle.value = false
  mostrarExamen.value = false
  detalle.value = null
  examen.value = null
}

async function cerrarSesion() {
  try {
    await api.post('/logout')
  } catch (error) {
    console.warn('Limpiando sesión local.', error)
  } finally {
    localStorage.removeItem('sivice_token')
    localStorage.removeItem('sivice_user')
    window.location.href = '/login'
  }
}

function textoSeguro(valor) {
  if (valor === null || valor === undefined || valor === '') return '—'
  return valor
}

function numeroRegistro(index) {
  const numero = ((pagination.value.current_page - 1) * perPage.value) + index + 1
  return String(numero).padStart(2, '0')
}

function ordenarPor(columna) {
  if (sortBy.value === columna) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = columna
    sortDir.value = 'asc'
  }
  cargar(1)
}

function iconoOrden(columna) {
  if (sortBy.value !== columna) return '↕'
  return sortDir.value === 'asc' ? '↑' : '↓'
}

function cerrarConEscape(event) {
  if (event.key === 'Escape' && mostrarDetalle.value) {
    cerrarDetalle()
  }
}

let timer = null
watch(busqueda, () => {
  if (timer) clearTimeout(timer)
  timer = setTimeout(() => {
    cargar(1)
  }, 450)
})

watch(perPage, () => { cargar(1) })
watch(ciclo, () => { cargar(1) })
watch(busquedaGlobal, () => { cargar(1) })

onMounted(() => {
  window.addEventListener('keydown', cerrarConEscape)
  cargar(1)
})

onBeforeUnmount(() => {
  if (timer) clearTimeout(timer)
  window.removeEventListener('keydown', cerrarConEscape)
})
</script>

<template>
  <div class="app-shell">
    <aside class="sidebar">
      <div class="sidebar-top-content">
        <div class="sidebar-header">
          <div class="sidebar-title-main">SIVICE</div>
          <div class="sidebar-subtitle">Coordinación General de Control Escolar</div>
        </div>

        <nav class="sidebar-nav">
          <a href="/aspirantes" class="side-link active-module">
            <i class="fas fa-user-graduate"></i> PINGRESO
          </a>
          <a href="/usuarios" class="side-link">
            <i class="fas fa-users"></i> USUARIOS
          </a>
          <a href="#" class="side-link"><i class="fas fa-folder"></i> OFMAYOR</a>
          <a href="#" class="side-link"><i class="fas fa-layer-group"></i> FASE2</a>
          <a href="#" class="side-link"><i class="fas fa-door-open"></i> EGRESOS</a>
        </nav>
      </div>
      
      <div class="sidebar-footer-btn">
        <button class="btn-logout-sidebar" @click="cerrarSesion">
          <i class="fas fa-sign-out-alt"></i> Salir del sistema
        </button>
      </div>
    </aside>

    <main class="main-panel">
      <div class="module-bar">
        <div class="module-title">PINGRESO</div>
      </div>

      <section class="content-area">
        <div class="content-wrapper">
          <div class="page-header">
            <span class="page-title">
              PADRÓN DE ASPIRANTES &gt; CICLO {{ busquedaGlobal ? 'TODOS' : ciclo }}
            </span>

            <div class="quick-search">
              <select v-model="ciclo" class="calendar-select" :disabled="busquedaGlobal">
                <option v-for="cal in calendarios" :key="cal" :value="cal">
                  {{ cal }}
                </option>
              </select>

              <label class="global-check">
                <input v-model="busquedaGlobal" type="checkbox" />
                Toda la base
              </label>

              <div class="search-input-container">
                <i class="fas fa-search search-icon"></i>
                <input
                  v-model="busqueda"
                  type="text"
                  placeholder="Buscar por código o nombre completo..."
                  class="quick-search-input"
                  @keyup.enter="cargar(1)"
                />
              </div>

              <button class="quick-search-btn" @click="cargar(1)">
                BUSCAR
              </button>
            </div>
          </div>

          <div class="table-scroll-container">
            <table class="suite-table">
              <thead>
                <tr>
                  <th class="reg-header">Reg</th>
                  <th @click="ordenarPor('nombreCompleto')" class="sortable-th">
                    NOMBRE COMPLETO <span class="order-icon">{{ iconoOrden('nombreCompleto') }}</span>
                  </th>
                  <th @click="ordenarPor('CODIGO')" class="sortable-th">
                    CÓDIGO <span class="order-icon">{{ iconoOrden('CODIGO') }}</span>
                  </th>
                  <th @click="ordenarPor('CALENDARIO')" class="sortable-th">
                    CALENDARIO <span class="order-icon">{{ iconoOrden('CALENDARIO') }}</span>
                  </th>
                  <th @click="ordenarPor('CEDU_CARRERA')" class="sortable-th">
                    CARRERA <span class="order-icon">{{ iconoOrden('CEDU_CARRERA') }}</span>
                  </th>
                  <th @click="ordenarPor('CEDU_SEDE')" class="sortable-th">
                    SEDE <span class="order-icon">{{ iconoOrden('CEDU_SEDE') }}</span>
                  </th>
                  <th @click="ordenarPor('CEDU_GRADO')" class="sortable-th">
                    GRADO <span class="order-icon">{{ iconoOrden('CEDU_GRADO') }}</span>
                  </th>
                  <th @click="ordenarPor('CEDU_PROMEDIO')" class="sortable-th">
                    PROMEDIO <span class="order-icon">{{ iconoOrden('CEDU_PROMEDIO') }}</span>
                  </th>
                  <th @click="ordenarPor('CAPTURO')" class="sortable-th">
                    CAPTURÓ <span class="order-icon">{{ iconoOrden('CAPTURO') }}</span>
                  </th>
                  <th @click="ordenarPor('resultadoExam')" class="sortable-th">
                    RESULTADO <span class="order-icon">{{ iconoOrden('resultadoExam') }}</span>
                  </th>
                  <th class="text-center">ACCIONES</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="loading">
                  <td colspan="11" class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i> Cargando registros del inventario...
                  </td>
                </tr>

                <tr v-else-if="registros.length === 0">
                  <td colspan="11" class="empty-state">
                    No se encontraron resultados con estos filtros.
                  </td>
                </tr>

                <tr v-else v-for="(asp, index) in registros" :key="'asp-' + (asp.ID || index)">
                  <td class="reg-cell">{{ numeroRegistro(index) }}</td>
                  <td class="name-cell font-medium">{{ textoSeguro(asp.nombreCompleto) }}</td>
                  <td class="font-mono font-bold text-dark">{{ textoSeguro(asp.CODIGO) }}</td>
                  <td>{{ textoSeguro(asp.CALENDARIO) }}</td>
                  <td>{{ textoSeguro(asp.CEDU_CARRERA) }}</td>
                  <td>{{ textoSeguro(asp.CEDU_SEDE) }}</td>
                  <td>{{ textoSeguro(asp.CEDU_GRADO) }}</td>
                  <td>{{ textoSeguro(asp.CEDU_PROMEDIO) }}</td>
                  <td class="text-muted">{{ textoSeguro(asp.CAPTURO) }}</td>
                  <td>
                    <span :class="['badge-status', asp.resultadoExam === 'Admitido' ? 'admitido' : 'no-admitido']">
                      {{ textoSeguro(asp.resultadoExam) }}
                    </span>
                  </td>
                  <td class="action-cell">
                    <button class="detail-btn" @click="verDetalle(asp.ID)" title="Ver expediente">
                      <i class="far fa-eye"></i> Ver
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="controls-footer">
            <div class="pagination-footer">
              <div class="pagination-spacer"></div>

              <div class="pagination-controls">
                <span>Filas por página:</span>
                <select v-model="perPage" class="suite-select">
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                  <option :value="200">200</option>
                </select>

                <span class="page-indicator">
                  Página {{ pagination.current_page }} de {{ pagination.last_page }}
                </span>

                <button class="page-btn" :disabled="pagination.current_page <= 1" @click="cargar(1)">
                  <i class="fas fa-angle-double-left"></i>
                </button>

                <button class="page-btn" :disabled="pagination.current_page <= 1" @click="cargar(pagination.current_page - 1)">
                  <i class="fas fa-angle-left"></i>
                </button>

                <template v-for="(item, idx) in paginasVisibles()" :key="'page-' + idx">
                  <span v-if="item === '...'" class="page-dots">...</span>
                  <button
                    v-else
                    class="page-btn page-number"
                    :class="{ active: item === pagination.current_page }"
                    @click="cargar(item)"
                  >
                    {{ item }}
                  </button>
                </template>

                <button class="page-btn" :disabled="pagination.current_page >= pagination.last_page" @click="cargar(pagination.current_page + 1)">
                  <i class="fas fa-angle-right"></i>
                </button>

                <button class="page-btn" :disabled="pagination.current_page >= pagination.last_page" @click="cargar(pagination.last_page)">
                  <i class="fas fa-angle-double-right"></i>
                </button>
              </div>

              <div class="results-total">
                {{ Number(pagination.total || 0).toLocaleString() }} resultados en total.
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <div v-if="mostrarDetalle" class="modal-overlay" @click.self="cerrarDetalle">
      <div class="modal-card">
        <div class="modal-header">
          <div class="header-user-info">
            <div class="user-avatar">
              <i class="fas fa-user-graduate"></i>
            </div>
            <div>
              <h2>Expediente del aspirante</h2>
              <p v-if="detalle" class="modal-user-name">{{ detalle.nombreCompleto }}</p>
            </div>
          </div>

          <div class="modal-actions">
            <button class="btn-examen" @click="verExamen" :disabled="cargandoExamen">
              <i class="fas fa-file-alt"></i> Examen de admisión
            </button>
            <button class="btn-close" @click="cerrarDetalle">&times;</button>
          </div>
        </div>

        <div v-if="cargandoDetalle" class="modal-loading">
          <i class="fas fa-circle-notch fa-spin"></i> Leyendo información del expediente...
        </div>

        <div v-else-if="detalle" class="modal-body">
          <div class="detail-grid">
            <div class="detail-item"><span>CÓDIGO</span><strong>{{ textoSeguro(detalle.CODIGO) }}</strong></div>
            <div class="detail-item"><span>CALENDARIO</span><strong>{{ textoSeguro(detalle.CALENDARIO) }}</strong></div>
            <div class="detail-item"><span>APELLIDO PATERNO</span><strong>{{ textoSeguro(detalle.APE_PAT) }}</strong></div>
            <div class="detail-item"><span>APELLIDO MATERNO</span><strong>{{ textoSeguro(detalle.APE_MAT) }}</strong></div>
            <div class="detail-item"><span>NOMBRE</span><strong>{{ textoSeguro(detalle.NOMBRE) }}</strong></div>
            <div class="detail-item"><span>FECHA NACIMIENTO</span><strong>{{ textoSeguro(detalle.FEC_NAC) }}</strong></div>
            <div class="detail-item full"><span>DOMICILIO</span><strong>{{ textoSeguro(detalle.DOMICILIO) }}</strong></div>
            <div class="detail-item"><span>COLONIA</span><strong>{{ textoSeguro(detalle.COLONIA) }}</strong></div>
            <div class="detail-item"><span>CP</span><strong>{{ textoSeguro(detalle.CP) }}</strong></div>
            <div class="detail-item phone-highlight"><span>TELÉFONO</span><strong>{{ textoSeguro(detalle.TELEFONO) }}</strong></div>
            <div class="detail-item"><span>ESTADO</span><strong>{{ textoSeguro(detalle.ESTA_VIV) }}</strong></div>
          </div>

          <div v-if="mostrarExamen" class="exam-panel">
            <div class="exam-title"><i class="fas fa-star"></i> DETALLES DEL EXAMEN DE ADMISIÓN</div>

            <div v-if="cargandoExamen" class="modal-loading">
              <i class="fas fa-circle-notch fa-spin"></i> Cargando puntajes...
            </div>

            <div v-else-if="examen" class="detail-grid">
              <div class="detail-item"><span>FECHA EXAMEN</span><strong>{{ textoSeguro(examen.COLE_FECHA_EX) }}</strong></div>
              <div class="detail-item"><span>HABILIDAD</span><strong>{{ textoSeguro(examen.COLE_HABILIDAD) }}</strong></div>
              <div class="detail-item"><span>RESULTADO RAW</span><strong>{{ textoSeguro(examen.COLE_RESULTADO) }}</strong></div>
              <div class="detail-item"><span>ESPAÑOL</span><strong>{{ textoSeguro(examen.COLE_ESPANIOL) }}</strong></div>
              <div class="detail-item"><span>MATEMÁTICAS</span><strong>{{ textoSeguro(examen.COLE_MATEMAT) }}</strong></div>
              <div class="detail-item"><span>INGLÉS</span><strong>{{ textoSeguro(examen.COLE_INGLES) }}</strong></div>
              <div class="detail-item"><span>GRAMÁTICA</span><strong>{{ textoSeguro(examen.COLE_GRAMATICA) }}</strong></div>
              <div class="detail-item"><span>LITERATURA</span><strong>{{ textoSeguro(examen.COLE_LITERATURA) }}</strong></div>
              <div class="detail-item"><span>ÁLGEBRA B</span><strong>{{ textoSeguro(examen.COLE_ALGEBRA_B) }}</strong></div>
              <div class="detail-item"><span>GEOMETRÍA</span><strong>{{ textoSeguro(examen.COLE_GEOMETRIA) }}</strong></div>
              <div class="detail-item"><span>LECTURA</span><strong>{{ textoSeguro(examen.COLE_LECTUR) }}</strong></div>
              <div class="detail-item"><span>TIPO EXAMEN</span><strong>{{ textoSeguro(examen.COLE_TIPO) }}</strong></div>
            </div>
          </div>
        </div>

        <div v-else class="modal-loading error-state">
          <i class="fas fa-exclamation-triangle"></i> No se pudo vincular el expediente seleccionado.
        </div>
      </div>
    </div>
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

.app-shell {
  display: grid;
  grid-template-columns: 260px 1fr; /* Mantiene la barra lateral fija a la izquierda siempre */
  width: 100vw;
  height: 100vh;
  background: #f8fafc;
  overflow: hidden;
}

.sidebar {
  height: 100vh;
  background: #ffffff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  justify-content: space-between; /* Empuja el botón "Salir" perfectamente al extremo inferior */
  box-sizing: border-box;
}

.sidebar-top-content {
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

/* CONTENEDOR FIJO PARA EL BOTÓN DE LOGOUT (Evita desalineaciones) */
.sidebar-footer-btn {
  padding: 16px 14px;
  border-top: 1px solid #f1f5f9;
  background: #ffffff;
}

.btn-logout-sidebar {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 16px;
  background: transparent;
  color: #ef4444;
  border: 1px solid transparent;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  box-sizing: border-box;
}

.btn-logout-sidebar:hover {
  background: #fef2f2;
  border-color: #fca5a5;
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
  gap: 16px;
  background: #ffffff;
  border-bottom: 1px solid #f1f5f9;
}

.page-title {
  font-size: 14px;
  font-weight: 700;
  color: #334155;
}

.quick-search {
  display: flex;
  gap: 10px;
  align-items: center;
}

.calendar-select {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #0f172a;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 13px;
  width: 90px;
  cursor: pointer;
}

.search-input-container {
  position: relative;
  width: 280px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 11px;
  color: #94a3b8;
  font-size: 0.9rem;
}

.quick-search-input {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #0f172a;
  padding: 8px 12px 8px 36px;
  border-radius: 8px;
  font-size: 13px;
  width: 100%;
  box-sizing: border-box;
}

.quick-search-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.global-check {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #475569;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
}

.quick-search-btn {
  background: #ffffff;
  color: #1d4ed8;
  border: 1px solid #1d4ed8;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}

.quick-search-btn:hover {
  background: #1d4ed8;
  color: #ffffff;
}

.table-scroll-container {
  flex: 1;
  min-height: 0;
  overflow: auto;
  background-color: #ffffff;
  margin: 0 20px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}

.suite-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1300px;
}

.suite-table th {
  position: sticky;
  top: 0;
  background-color: #f8fafc;
  z-index: 2;
  padding: 12px 14px;
  text-align: left;
  color: #475569;
  font-weight: 600;
  border-bottom: 2px solid #e2e8f0;
  font-size: 13px;
}

.sortable-th {
  cursor: pointer;
  user-select: none;
}

.sortable-th:hover {
  background-color: #f1f5f9 !important;
  color: #1d4ed8;
}

.order-icon {
  color: #94a3b8;
  margin-left: 4px;
}

.suite-table td {
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
  font-size: 13px;
}

.suite-table tr:hover td {
  background-color: #f8fafc;
}

.reg-header,
.reg-cell {
  width: 60px;
  text-align: center;
}

.reg-cell {
  color: #64748b;
  font-weight: 600;
}

.font-medium { font-weight: 500; }
.font-mono { font-family: monospace; font-size: 14px; }
.font-bold { font-weight: 700; }
.text-dark { color: #0f172a; }
.text-muted { color: #64748b; }

.badge-status {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  display: inline-block;
}
.badge-status.admitido {
  background: #dcfce7;
  color: #15803d;
}
.badge-status.no-admitido {
  background: #fee2e2;
  color: #b91c1c;
}

.detail-btn {
  background: #ffffff;
  color: #1d4ed8;
  border: 1px solid #1d4ed8;
  border-radius: 6px;
  cursor: pointer;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s;
}

.detail-btn:hover {
  background: #1d4ed8;
  color: #ffffff;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
  font-size: 14px;
}

.controls-footer {
  flex-shrink: 0;
  padding: 14px 20px;
  background-color: #ffffff;
  border-top: 1px solid #f1f5f9;
}

.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  font-size: 13px;
  color: #475569;
  gap: 8px;
}

.suite-select {
  background-color: #ffffff;
  color: #0f172a;
  border: 1px solid #cbd5e1;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.page-indicator {
  margin: 0 4px;
  color: #64748b;
}

.page-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background-color: #1d4ed8;
  color: #ffffff;
  border-color: #1d4ed8;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-number.active {
  background-color: #1d4ed8;
  border-color: #1d4ed8;
  color: #ffffff;
}

.results-total {
  font-weight: 600;
  color: #334155;
  font-size: 13px;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.25);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 2000;
}

.modal-card {
  width: min(850px, 95vw);
  max-height: 88vh;
  overflow: auto;
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03);
  animation: fadeIn 0.25s ease-out;
}

.modal-header {
  position: sticky;
  top: 0;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(4px);
  z-index: 2;
  display: flex;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  align-items: center;
}

.header-user-info {
  display: flex;
  align-items: center;
  gap: 14px;
}

.user-avatar {
  width: 46px;
  height: 46px;
  background: #eff6ff;
  color: #1d4ed8;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.modal-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 17px;
  font-weight: 700;
}

.modal-user-name {
  margin: 3px 0 0;
  color: #1d4ed8;
  font-weight: 600;
  font-size: 14px;
}

.btn-examen {
  background: #ffffff;
  color: #1d4ed8;
  border: 1px solid #1d4ed8;
  padding: 7px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-examen:hover {
  background: #eff6ff;
}

.btn-close {
  background: transparent;
  color: #94a3b8;
  border: none;
  font-size: 24px;
  cursor: pointer;
  padding: 0 4px;
}

.btn-close:hover {
  color: #475569;
}

.modal-body {
  padding: 24px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(200px, 1fr));
  gap: 16px;
}

.detail-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px;
}

.detail-item.full {
  grid-column: 1 / -1;
}

.detail-item span {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 4px;
  text-transform: uppercase;
}

.detail-item strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 500;
}

.phone-highlight {
  grid-column: span 2;
  background: #f0fdf4;
  border-color: #bbf7d0;
}
.phone-highlight span { color: #166534; }
.phone-highlight strong { color: #15803d; font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }

.exam-panel {
  margin-top: 24px;
  border-top: 1px solid #e2e8f0;
  padding-top: 20px;
}

.exam-title {
  color: #b45309;
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* ELIMINADA la media query disruptiva para asegurar consistencia del layout de escritorio */
</style>