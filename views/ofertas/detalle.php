<?php $tituloPagina = 'Detalle de oferta'; ?>
<?php require_once __DIR__.'/../layout/header.php'; ?>

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
                    class="btn btn-primary btn-sm"
                    :class="{ 'disabled': oferta.estado === 'cerrado' }"
                    :title="oferta.estado === 'cerrado' ? 'No se puede editar una oferta cerrada' : ''">
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
            <div class="card-body p-0">

                <!-- Header con título y badges -->
                <div class="p-4 d-flex align-items-start gap-3" style="border-bottom:1px solid var(--gray-200)">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                        style="width:48px;height:48px;background:var(--primary-light)">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size:1.25rem"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-2" style="font-size:1rem;line-height:1.4">{{ oferta.objeto }}</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="consecutive-badge">{{ oferta.consecutivo }}</span>
                            <span :class="badgeEstado(oferta.estado)" class="badge">{{ oferta.estado }}</span>
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="p-4" style="border-bottom:1px solid var(--gray-200)">
                    <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">
                        Descripción / Alcance
                    </div>
                    <p style="font-size:.9rem;line-height:1.7;margin:0">{{ oferta.descripcion }}</p>
                </div>

                <!-- Métricas: presupuesto, moneda, actividad -->
                <div class="row g-0" style="border-bottom:1px solid var(--gray-200)">
                    <div class="col-md-4 p-4" style="border-right:1px solid var(--gray-200)">
                        <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Presupuesto</div>
                        <div class="fw-semibold" style="font-size:1.2rem">{{ formatMoneda(oferta.presupuesto, oferta.moneda) }}</div>
                        <div class="text-muted" style="font-size:.8rem">{{ oferta.moneda }}</div>
                    </div>
                    <div class="col-md-4 p-4" style="border-right:1px solid var(--gray-200)">
                        <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Moneda</div>
                        <div class="fw-semibold" style="font-size:1.2rem">{{ oferta.moneda }}</div>
                        <div class="text-muted" style="font-size:.8rem">{{ nombreMoneda(oferta.moneda) }}</div>
                    </div>
                    <div class="col-md-4 p-4">
                        <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Actividad</div>
                        <div class="fw-semibold" style="font-size:.95rem">{{ oferta.actividad ? oferta.actividad.producto : '-' }}</div>
                        <div class="text-muted" style="font-size:.8rem">{{ oferta.actividad ? oferta.actividad.segmento : '' }}</div>
                    </div>
                </div>

                <!-- Footer con fechas de auditoría -->
                <div class="px-4 py-3 d-flex gap-4 flex-wrap" style="background:var(--gray-50)">
                    <span class="text-muted d-flex align-items-center gap-1" style="font-size:.8rem">
                        <i class="bi bi-calendar-plus"></i>
                        Creada el {{ formatFechaHora(oferta.creado_en) }}
                    </span>
                    <span class="text-muted d-flex align-items-center gap-1" style="font-size:.8rem">
                        <i class="bi bi-arrow-clockwise"></i>
                        Actualizada el {{ formatFechaHora(oferta.actualizado_en) }}
                    </span>
                </div>

            </div>
        </div>

        <!-- Tab: Cronograma -->
        <div v-show="tabActivo === 'cronograma'" class="card">
            <div class="card-body">

                <!-- Cards de fechas -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                            <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Apertura</div>
                            <div class="fw-semibold" style="font-size:1.15rem">{{ formatFecha(oferta.fecha_inicio) }}</div>
                            <div class="text-muted" style="font-size:.85rem">{{ formatHora(oferta.hora_inicio) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                            <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em">Cierre</div>
                            <div class="fw-semibold" style="font-size:1.15rem">{{ formatFecha(oferta.fecha_cierre) }}</div>
                            <div class="text-muted" style="font-size:.85rem">{{ formatHora(oferta.hora_cierre) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div class="mb-4 p-3 rounded-3" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold" style="font-size:.875rem">Progreso de la licitación</span>
                        <span class="badge" :class="badgeCronograma.clase">{{ badgeCronograma.texto }}</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;background:var(--gray-200)">
                        <div
                            class="progress-bar"
                            :class="badgeCronograma.barra"
                            :style="{ width: progreso + '%' }"
                            style="border-radius:4px;transition:width .6s ease"
                        ></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">{{ formatFecha(oferta.fecha_inicio) }}</small>
                        <small class="text-muted" v-if="diasRestantes > 0">Faltan {{ diasRestantes }} día(s)</small>
                        <small class="text-muted" v-else-if="diasRestantes === 0">Vence hoy</small>
                        <small class="text-muted" v-else>Cerrada</small>
                        <small class="text-muted">{{ formatFecha(oferta.fecha_cierre) }}</small>
                    </div>
                </div>

                <!-- Línea de tiempo -->
                <div class="p-3 rounded-3" style="background:var(--gray-50);border:1px solid var(--gray-200)">
                    <div class="fw-semibold mb-3" style="font-size:.875rem">Línea de tiempo</div>

                    <!-- Evento apertura -->
                    <div class="d-flex gap-3">
                        <div class="d-flex flex-column align-items-center" style="width:20px;flex-shrink:0">
                            <div style="width:12px;height:12px;border-radius:50%;background:var(--success);margin-top:3px"></div>
                            <div style="width:2px;flex:1;background:var(--gray-200);min-height:32px"></div>
                        </div>
                        <div class="pb-3">
                            <div class="fw-semibold" style="font-size:.875rem">Apertura de licitación</div>
                            <div class="text-muted" style="font-size:.8rem">{{ formatFecha(oferta.fecha_inicio) }} · {{ formatHora(oferta.hora_inicio) }}</div>
                        </div>
                    </div>

                    <!-- Evento hoy (solo si está en curso) -->
                    <div v-if="enCurso" class="d-flex gap-3">
                        <div class="d-flex flex-column align-items-center" style="width:20px;flex-shrink:0">
                            <div style="width:12px;height:12px;border-radius:50%;background:var(--primary);margin-top:3px;box-shadow:0 0 0 3px rgba(37,99,235,.15)"></div>
                            <div style="width:2px;flex:1;background:var(--gray-200);min-height:32px"></div>
                        </div>
                        <div class="pb-3">
                            <div class="fw-semibold" style="font-size:.875rem">Hoy</div>
                            <div class="text-muted" style="font-size:.8rem">{{ hoy }} · Faltan {{ diasRestantes }} día(s) para el cierre</div>
                        </div>
                    </div>

                    <!-- Evento cierre -->
                    <div class="d-flex gap-3">
                        <div class="d-flex flex-column align-items-center" style="width:20px;flex-shrink:0">
                            <div style="width:12px;height:12px;border-radius:50%;margin-top:3px"
                                :style="{ background: diasRestantes <= 0 ? 'var(--danger)' : 'transparent', border: diasRestantes <= 0 ? 'none' : '2px solid var(--gray-200)' }">
                            </div>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:.875rem">Cierre de licitación</div>
                            <div class="text-muted" style="font-size:.8rem">{{ formatFecha(oferta.fecha_cierre) }} · {{ formatHora(oferta.hora_cierre) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de duración -->
                <div class="mt-3 p-3 rounded-3" style="background:var(--primary-light);border-left:3px solid var(--primary)">
                    <small style="color:var(--primary)">
                        <strong>Duración total:</strong> {{ duracionTotal }} día(s) &nbsp;·&nbsp;
                        <strong>Transcurridos:</strong> {{ diasTranscurridos }} día(s) &nbsp;·&nbsp;
                        <strong>Restantes:</strong> {{ Math.max(0, diasRestantes) }} día(s)
                    </small>
                </div>

            </div>
        </div>

        <!-- Tab: Documentos -->
        <div v-show="tabActivo === 'documentos'" class="card">
            <div class="card-body">

                <div
                    v-if="!oferta.documentos || oferta.documentos.length === 0"
                    class="text-center text-muted py-5"
                >
                    <i class="bi bi-folder2-open fs-1 d-block mb-3"></i>
                    No existen documentos asociados a esta oferta.
                </div>

                <transition-group
                    v-else
                    name="documento"
                    tag="div"
                    class="row g-4"
                >
                    <div
                        v-for="d in oferta.documentos"
                        :key="d.id"
                        class="col-md-6 col-lg-4"
                    >
                        <div class="document-card">

                            <div class="document-card-header">
                                <div class="document-icon">
                                    <i :class="iconoDocumento(d.archivo)"></i>
                                </div>

                                <span
                                    class="badge"
                                    :class="badgeDocumento(d.archivo)"
                                >
                                    {{ extensionDocumento(d.archivo).toUpperCase() }}
                                </span>
                            </div>

                            <div class="document-card-body">
                                <h6 class="document-title">
                                    {{ d.titulo }}
                                </h6>

                                <p class="document-description">
                                    {{ d.descripcion }}
                                </p>
                            </div>

                            <div class="document-card-footer">
                                <a
                                    :href="`/licitaciones/public/${d.archivo}`"
                                    target="_blank"
                                    class="btn btn-outline-primary btn-sm w-100"
                                >
                                    <i class="bi bi-download me-2"></i>
                                    Descargar documento
                                </a>
                            </div>

                        </div>
                    </div>
                </transition-group>

            </div>
        </div>
    </template>

    <div v-else class="alert alert-warning">Oferta no encontrada.</div>
</div>

<?php require_once __DIR__.'/../layout/footer.php'; ?>
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
    computed: {
        hoy() {
            return new Date().toISOString().split('T')[0].split('-').reverse().join('/');
        },
        duracionTotal() {
            if (!this.oferta) return 0;
            const ini   = new Date(this.oferta.fecha_inicio);
            const fin   = new Date(this.oferta.fecha_cierre);
            return Math.ceil((fin - ini) / 86400000);
        },
        diasTranscurridos() {
            if (!this.oferta) return 0;
            const ini  = new Date(this.oferta.fecha_inicio);
            const hoy  = new Date();
            return Math.max(0, Math.min(this.duracionTotal, Math.ceil((hoy - ini) / 86400000)));
        },
        diasRestantes() {
            if (!this.oferta) return 0;
            const fin = new Date(this.oferta.fecha_cierre);
            const hoy = new Date();
            return Math.ceil((fin - hoy) / 86400000);
        },
        progreso() {
            if (this.duracionTotal === 0) return 0;
            return Math.min(100, Math.max(0, (this.diasTranscurridos / this.duracionTotal) * 100));
        },
        enCurso() {
            return this.diasTranscurridos > 0 && this.diasRestantes > 0;
        },
        badgeCronograma() {
            if (this.diasRestantes < 0) return { texto: 'Cerrada',   clase: 'bg-danger',  barra: 'bg-danger'  };
            if (this.diasRestantes === 0) return { texto: 'Vence hoy', clase: 'bg-warning text-dark', barra: 'bg-warning' };
            if (this.diasRestantes <= 3)  return { texto: `${this.diasRestantes} día(s) restante(s)`, clase: 'bg-warning text-dark', barra: 'bg-warning' };
            return { texto: `En curso · ${this.diasRestantes} días restantes`, clase: 'bg-success', barra: 'bg-success' };
        },
    },
    methods: {
        formatFecha(f) {
            if (!f) return '-';
            const [y, m, d] = f.split('-');
            return `${d}/${m}/${y}`;
        },
        formatHora(h) {
            if (!h) return '-';
            return h.substring(0, 5);
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
        nombreMoneda(moneda) {
            const mapa = { COP: 'Peso colombiano', USD: 'Dólar estadounidense', EUR: 'Euro' };
            return mapa[moneda] || moneda;
        },
        formatFechaHora(dt) {
            if (!dt) return '-';
            const d = new Date(dt);
            return d.toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' })
                + ' · ' + d.toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit' });
        },
        extensionDocumento(path) {
            return path.split('.').pop().toLowerCase();
        },

        iconoDocumento(path) {
            const ext = this.extensionDocumento(path);

            return ext === 'pdf'
                ? 'bi bi-file-earmark-pdf-fill text-danger'
                : 'bi bi-file-zip-fill text-warning';
        },

        badgeDocumento(path) {
            const ext = this.extensionDocumento(path);

            return ext === 'pdf'
                ? 'bg-danger-subtle text-danger'
                : 'bg-warning-subtle text-warning';
        },
    },
});
</script>