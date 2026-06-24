<?php $tituloPagina = 'Detalle de oferta'; ?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div id="app" v-cloak>
    <div v-if="cargando" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
    </div>

    <template v-else-if="oferta">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h5 class="fw-semibold mb-1">{{ oferta.objeto }}</h5>
                <span class="badge bg-primary me-2">{{ oferta.consecutivo }}</span>
                <span :class="badgeEstado(oferta.estado)" class="badge">{{ oferta.estado }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="/licitaciones/public/ofertas" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
                <a :href="`/licitaciones/public/ofertas/editar?id=${oferta.id}`"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
            </div>
        </div>

        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item" v-for="tab in tabs" :key="tab.id">
                <button
                    class="nav-link"
                    :class="{ active: tabActivo === tab.id }"
                    @click="tabActivo = tab.id"
                >
                    <i :class="`bi bi-${tab.icono} me-1`"></i>{{ tab.label }}
                </button>
            </li>
        </ul>

        <!-- Tab: Información básica -->
        <div v-show="tabActivo === 'info'" class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Consecutivo</dt>
                    <dd class="col-sm-9 fw-semibold text-primary">{{ oferta.consecutivo }}</dd>

                    <dt class="col-sm-3 text-muted">Objeto</dt>
                    <dd class="col-sm-9">{{ oferta.objeto }}</dd>

                    <dt class="col-sm-3 text-muted">Descripción</dt>
                    <dd class="col-sm-9">{{ oferta.descripcion }}</dd>

                    <dt class="col-sm-3 text-muted">Moneda</dt>
                    <dd class="col-sm-9">{{ oferta.moneda }}</dd>

                    <dt class="col-sm-3 text-muted">Presupuesto</dt>
                    <dd class="col-sm-9">{{ formatMoneda(oferta.presupuesto, oferta.moneda) }}</dd>

                    <dt class="col-sm-3 text-muted">Actividad</dt>
                    <dd class="col-sm-9">{{ oferta.actividad ? oferta.actividad.producto : '-' }}</dd>
                </dl>
            </div>
        </div>

        <!-- Tab: Cronograma -->
        <div v-show="tabActivo === 'cronograma'" class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Fecha inicio</dt>
                    <dd class="col-sm-9">{{ formatFecha(oferta.fecha_inicio) }} — {{ oferta.hora_inicio }}</dd>

                    <dt class="col-sm-3 text-muted">Fecha cierre</dt>
                    <dd class="col-sm-9">{{ formatFecha(oferta.fecha_cierre) }} — {{ oferta.hora_cierre }}</dd>
                </dl>
            </div>
        </div>

        <!-- Tab: Documentos -->
        <div v-show="tabActivo === 'documentos'" class="card">
            <div class="card-body p-0">
                <div v-if="!oferta.documentos || oferta.documentos.length === 0"
                     class="text-center text-muted py-4">
                    Sin documentos adjuntos.
                </div>
                <table v-else class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in oferta.documentos" :key="d.id">
                            <td>{{ d.titulo }}</td>
                            <td class="text-muted small">{{ d.descripcion }}</td>
                            <td>
                                <a :href="`/licitaciones/public/${d.archivo}`"
                                   target="_blank" class="small">
                                    <i class="bi bi-download me-1"></i>Descargar
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </template>

    <div v-else class="alert alert-warning">Oferta no encontrada.</div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
<script>
new Vue({
    el: '#app',
    data: {
        oferta:    null,
        cargando:  true,
        tabActivo: 'info',
        tabs: [
            { id: 'info',       label: 'Información básica', icono: 'info-circle'    },
            { id: 'cronograma', label: 'Cronograma',         icono: 'calendar-range' },
            { id: 'documentos', label: 'Documentos',         icono: 'paperclip'      },
        ],
    },
    async mounted() {
        const id = new URLSearchParams(window.location.search).get('id');
        try {
            const { data } = await axios.get(`/licitaciones/public/ofertas/detalle?id=${id}`);
            if (data.success) this.oferta = data.data;
        } catch (error) {
            console.error('Error al cargar oferta:', error);
        } finally {
            this.cargando = false;
        }
    },
    methods: {
        formatFecha(f) {
            if (!f) return '-';
            const [y, m, d] = f.split('-');
            return `${d}/${m}/${y}`;
        },
        formatMoneda(valor, moneda) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency', currency: moneda || 'COP', minimumFractionDigits: 2,
            }).format(valor);
        },
        badgeEstado(estado) {
            const mapa = { activo: 'bg-success', inactivo: 'bg-secondary', cerrado: 'bg-danger' };
            return mapa[estado] || 'bg-secondary';
        },
    },
});
</script>