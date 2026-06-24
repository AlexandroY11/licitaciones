<?php $tituloPagina = 'Listado de ofertas'; ?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div id="app" v-cloak>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Buscar oferta</label>
                    <input
                        v-model="filtro"
                        @input="buscar"
                        type="text"
                        class="form-control"
                        placeholder="Consecutivo, objeto o descripción..."
                    >
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button @click="limpiar" class="btn btn-outline-secondary w-50">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </button>
                    <button @click="exportar" class="btn btn-success w-50">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
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
                            <a :href="`/licitaciones/public/ofertas/detalle?id=${o.id}`"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Ver detalle
                            </a>
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
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
<script>
new Vue({
    el: '#app',
    data: {
        ofertas:      [],
        filtro:       '',
        cargando:     false,
        total:        0,
        paginaActual: 1,
        totalPaginas: 1,
        timer:        null,
    },
    mounted() {
        this.cargarOfertas();
    },
    methods: {
        async cargarOfertas() {
            this.cargando = true;
            try {
                const { data } = await axios.get('/licitaciones/public/ofertas', {
                    params: { q: this.filtro, pagina: this.paginaActual }
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
        limpiar() {
            this.filtro       = '';
            this.paginaActual = 1;
            this.cargarOfertas();
        },
        cambiarPagina(p) {
            if (p < 1 || p > this.totalPaginas) return;
            this.paginaActual = p;
            this.cargarOfertas();
        },
        exportar() {
            const url = `/licitaciones/public/ofertas/exportar?q=${encodeURIComponent(this.filtro)}`;
            window.open(url, '_blank');
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