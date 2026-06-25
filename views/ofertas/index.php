<?php $tituloPagina = 'Listado de ofertas'; ?>
<?php require_once __DIR__.'/../layout/header.php'; ?>

<div id="app" v-cloak>
    <div class="card mb-4">
        <div class="card-body">

            <!-- Fila principal -->
            <div class="d-flex gap-2 align-items-center">
                <div class="flex-grow-1 position-relative">
                    <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-500);font-size:.875rem"></i>
                    <input
                        v-model="filtros.q"
                        @input="buscar"
                        type="text"
                        class="form-control"
                        style="padding-left:2.25rem"
                        placeholder="Buscar por consecutivo, objeto o descripción..."
                    >
                </div>
                <button
                    @click="panelFiltros = !panelFiltros"
                    class="btn btn-outline-secondary d-flex align-items-center gap-2"
                    :class="{ 'btn-primary text-white': filtrosActivos }"
                >
                    <i class="bi bi-sliders"></i>
                    Filtros
                    <span v-if="filtrosActivos" class="badge bg-white text-primary" style="font-size:.7rem">{{ contadorFiltros }}</span>
                    <i class="bi" :class="panelFiltros ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size:.75rem"></i>
                </button>
                <button @click="exportar" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel"></i>
                    Excel
                </button>
            </div>

            <!-- Panel desplegable -->
            <div v-show="panelFiltros" class="mt-3 pt-3" style="border-top:1px solid var(--gray-200)">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500)">Estado</label>
                        <select v-model="filtros.estado" @change="buscar" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500)">Moneda</label>
                        <select v-model="filtros.moneda" @change="buscar" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            <option value="COP">COP</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500)">Fecha inicio desde</label>
                        <input v-model="filtros.fecha_desde" @change="buscar" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500)">Fecha cierre hasta</label>
                        <input v-model="filtros.fecha_hasta" @change="buscar" type="date" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <button @click="limpiarFiltros" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Limpiar filtros
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div v-if="cargando" class="text-center py-5">
                <div class="spinner-border text-primary"></div>
            </div>

            <div v-else-if="ofertas.length === 0" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No se encontraron ofertas.
            </div>

            <table v-else class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Consecutivo</th>
                        <th>Objeto</th>
                        <th>Descripción</th>
                        <th>Fecha inicio</th>
                        <th>Fecha cierre</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in ofertas" :key="o.id">
                        <td><span class="fw-semibold text-primary">{{ o.consecutivo }}</span></td>
                        <td>{{ o.objeto }}</td>
                        <td class="text-muted small">{{ truncar(o.descripcion, 60) }}</td>
                        <td>{{ formatFecha(o.fecha_inicio) }}</td>
                        <td>{{ formatFecha(o.fecha_cierre) }}</td>
                        <td>
                            <span :class="badgeEstado(o.estado)" class="badge badge-estado">
                                {{ o.estado }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a :href="`/licitaciones/public/ofertas/detalle?id=${o.id}`"
                                class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Ver detalle
                                </a>
                                <button
                                    @click="confirmarCambioEstado(o)"
                                    class="btn btn-sm"
                                    :class="o.estado === 'activo' ? 'btn-outline-danger' : 'btn-outline-success'"
                                >
                                    <i class="bi" :class="o.estado === 'activo' ? 'bi-slash-circle' : 'bi-check-circle'"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div v-if="totalPaginas > 1" class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ total }} oferta(s) encontrada(s)</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: paginaActual === 1 }">
                        <button class="page-link" @click="cambiarPagina(paginaActual - 1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                    </li>
                    <li
                        v-for="p in totalPaginas"
                        :key="p"
                        class="page-item"
                        :class="{ active: p === paginaActual }"
                    >
                        <button class="page-link" @click="cambiarPagina(p)">{{ p }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
                        <button class="page-link" @click="cambiarPagina(paginaActual + 1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal confirmar cambio de estado -->
    <div v-if="modalEstado" class="toast-overlay">
        <div class="toast-box" style="min-width:340px">
            <div class="toast-icon" :style="{ background: ofertaEstado.estado === 'activo' ? '#fef2f2' : '#f0fdf4' }">
                <i class="bi" :class="ofertaEstado.estado === 'activo' ? 'bi-slash-circle text-danger' : 'bi-check-circle text-success'" style="font-size:1.75rem"></i>
            </div>
            <div class="toast-title">
                {{ ofertaEstado.estado === 'activo' ? 'Inhabilitar oferta' : 'Habilitar oferta' }}
            </div>
            <div class="toast-subtitle mb-3">
                ¿Estás seguro que deseas
                <strong>{{ ofertaEstado.estado === 'activo' ? 'inhabilitar' : 'habilitar' }}</strong>
                la oferta <strong>{{ ofertaEstado.consecutivo }}</strong>?
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-outline-secondary" @click="modalEstado = false">
                    Cancelar
                </button>
                <button
                    class="btn"
                    :class="ofertaEstado.estado === 'activo' ? 'btn-danger' : 'btn-success'"
                    @click="cambiarEstado"
                    :disabled="cambiandoEstado"
                >
                    <span v-if="cambiandoEstado" class="spinner-border spinner-border-sm me-1"></span>
                    {{ ofertaEstado.estado === 'activo' ? 'Sí, inhabilitar' : 'Sí, habilitar' }}
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__.'/../layout/footer.php'; ?>
<script>
new Vue({
    el: '#app',
    data: {
        ofertas:      [],
        cargando:     false,
        total:        0,
        paginaActual: 1,
        totalPaginas: 1,
        timer:        null,
        panelFiltros: false,
        modalEstado:    false,
        ofertaEstado:   null,
        cambiandoEstado: false,
        filtros: {
            q:           '',
            estado:      '',
            moneda:      '',
            fecha_desde: '',
            fecha_hasta: '',
        },
    },
    computed: {
        filtrosActivos() {
            return Object.values(this.filtros).some(v => v !== '');
        },
        contadorFiltros() {
            return Object.values(this.filtros).filter(v => v !== '').length;
        },
    },
    mounted() {
        this.cargarOfertas();
    },
    methods: {
        async cargarOfertas() {
            this.cargando = true;
            try {
                const { data } = await axios.get('/licitaciones/public/ofertas', {
                    params: { ...this.filtros, pagina: this.paginaActual }
                });
                if (data.success) {
                    this.ofertas      = data.data.ofertas;
                    this.total        = data.data.total;
                    this.totalPaginas = data.data.total_paginas;
                }
            } catch (error) {
                console.error('Error al cargar ofertas:', error);
            } finally {
                this.cargando = false;
            }
        },
        buscar() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.paginaActual = 1;
                this.cargarOfertas();
            }, 350);
        },
        limpiarFiltros() {
            this.filtros = { q: '', estado: '', moneda: '', fecha_desde: '', fecha_hasta: '' };
            this.paginaActual = 1;
            this.cargarOfertas();
        },
        cambiarPagina(p) {
            if (p < 1 || p > this.totalPaginas) return;
            this.paginaActual = p;
            this.cargarOfertas();
        },
        exportar() {
            const params = new URLSearchParams({ ...this.filtros });
            window.open(`/licitaciones/public/ofertas/exportar?${params}`, '_blank');
        },
        confirmarCambioEstado(oferta) {
            this.ofertaEstado = { ...oferta };
            this.modalEstado  = true;
        },
        async cambiarEstado() {
            this.cambiandoEstado = true;
            const fd = new FormData();
            fd.append('id',     this.ofertaEstado.id);
            fd.append('estado', this.ofertaEstado.estado === 'activo' ? 'inactivo' : 'activo');
            try {
                const { data } = await axios.post('/licitaciones/public/ofertas/estado', fd);
                if (data.success) {
                    const oferta = this.ofertas.find(o => o.id === this.ofertaEstado.id);
                    if (oferta) oferta.estado = fd.get('estado');
                    this.modalEstado = false;
                }
            } catch (error) {
                console.error('Error al cambiar estado:', error);
            } finally {
                this.cambiandoEstado = false;
            }
        },
        truncar(texto, max) {
            return texto?.length > max ? texto.substring(0, max) + '…' : texto;
        },
        formatFecha(fecha) {
            if (!fecha) return '-';
            const [y, m, d] = fecha.split('-');
            return `${d}/${m}/${y}`;
        },
        badgeEstado(estado) {
            const mapa = {
                activo:    'bg-success',
                inactivo:  'bg-secondary',
                cerrado:   'bg-danger',
                pendiente: 'bg-warning text-dark',
            };
            return mapa[estado] || 'bg-secondary';
        },
    },
});
</script>