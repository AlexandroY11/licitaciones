<?php
$esEdicion = isset($_GET['id']);
$tituloPagina = $esEdicion ? 'Editar oferta' : 'Nueva oferta';
?>
<?php require_once __DIR__.'/../layout/header.php'; ?>

<div id="app" v-cloak>
    <div v-if="guardadoExitoso" class="toast-overlay">
        <div class="toast-box">
            <div class="toast-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <div class="toast-title">¡Oferta guardada!</div>
            <div class="toast-subtitle">Redirigiendo al listado...</div>
            <div class="toast-bar">
                <div class="toast-bar-fill"></div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-semibold mb-0">
            <i class="bi bi-<?php echo $esEdicion ? 'pencil' : 'plus-circle'; ?> me-2 text-primary"></i>
            <?php echo $tituloPagina; ?>
        </h5>
        <a href="/licitaciones/public/ofertas" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <!-- Alertas -->
    <div v-if="errores.length" class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <li v-for="e in errores" :key="e">{{ e }}</li>
        </ul>
    </div>
    <div v-if="mensaje" class="alert alert-success">{{ mensaje }}</div>

    <form @submit.prevent="guardar" novalidate>

        <!-- SECCIÓN 1: Información básica -->
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-info-circle me-2"></i>Sección 1 — Información básica
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Objeto <span class="text-danger">*</span></label>
                    <input
                        v-model.trim="form.objeto"
                        type="text"
                        maxlength="150"
                        class="form-control"
                        :class="{ 'is-invalid': campoInvalido('objeto') }"
                        placeholder="Objeto de la licitación"
                    >
                    <div class="d-flex justify-content-between">
                        <div class="invalid-feedback">{{ campoInvalido('objeto') }}</div>
                        <small class="text-muted ms-auto">{{ form.objeto.length }}/150</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción / Alcance <span class="text-danger">*</span></label>
                    <textarea
                        v-model.trim="form.descripcion"
                        maxlength="400"
                        rows="4"
                        class="form-control"
                        :class="{ 'is-invalid': campoInvalido('descripcion') }"
                        placeholder="Descripción detallada del alcance"
                    ></textarea>
                    <div class="d-flex justify-content-between">
                        <div class="invalid-feedback">{{ campoInvalido('descripcion') }}</div>
                        <small class="text-muted ms-auto">{{ form.descripcion.length }}/400</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Moneda <span class="text-danger">*</span></label>
                        <select
                            v-model="form.moneda"
                            class="form-select"
                            :class="{ 'is-invalid': campoInvalido('moneda') }"
                        >
                            <option value="">Seleccione...</option>
                            <option value="COP">COP — Peso colombiano</option>
                            <option value="USD">USD — Dólar</option>
                            <option value="EUR">EUR — Euro</option>
                        </select>
                        <div class="invalid-feedback">{{ campoInvalido('moneda') }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Presupuesto <span class="text-danger">*</span></label>
                        <input
                            v-model="form.presupuesto"
                            @input="validarPresupuesto"
                            type="text"
                            inputmode="decimal"
                            class="form-control"
                            :class="{ 'is-invalid': campoInvalido('presupuesto') }"
                            placeholder="0.00"
                        >
                        <div class="invalid-feedback">{{ campoInvalido('presupuesto') }}</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Actividad <span class="text-danger">*</span></label>

                        <div v-if="actividadSeleccionada" class="actividad-seleccionada">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <div class="actividad-tag" :title="actividadSeleccionada.segmento + ' › ' + actividadSeleccionada.clase + ' › ' + actividadSeleccionada.producto">
                                        <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                        <span class="actividad-tag-text">{{ actividadSeleccionada.producto }}</span>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size:.75rem">
                                        {{ actividadSeleccionada.segmento }} › {{ actividadSeleccionada.familia }}
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" @click="limpiarActividad">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Buscador -->
                        <div v-else class="actividad-wrapper">
                            <div class="actividad-input-wrap">
                                <i class="bi bi-search actividad-search-icon"></i>
                                <input
                                    v-model="busquedaActividad"
                                    @input="onBuscarActividad"
                                    @focus="dropdownAbierto = actividades.length > 0"
                                    @blur="cerrarDropdown"
                                    type="text"
                                    class="form-control actividad-input"
                                    :class="{ 'is-invalid': campoInvalido('actividad_id') }"
                                    placeholder="Buscar actividad (mín. 3 caracteres)..."
                                    autocomplete="off"
                                >
                                <span v-if="cargandoActividades" class="actividad-spinner">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </span>
                            </div>

                            <!-- Hint -->
                            <small v-if="busquedaActividad.length > 0 && busquedaActividad.length < 3" class="text-muted">
                                Escribe al menos 3 caracteres...
                            </small>
                            <small v-else-if="actividades.length === 0 && busquedaActividad.length >= 3 && !cargandoActividades" class="text-muted">
                                Sin resultados para "{{ busquedaActividad }}"
                            </small>

                            <!-- Dropdown de resultados -->
                            <div
                                v-if="dropdownAbierto && actividades.length > 0"
                                class="actividad-dropdown"
                                @mousedown.prevent
                            >
                                <div
                                    v-for="a in actividades"
                                    :key="a.id"
                                    class="actividad-option"
                                    :class="{ 'actividad-option--hover': hoverId === a.id }"
                                    @mouseenter="hoverId = a.id"
                                    @mouseleave="hoverId = null"
                                    @click="elegirActividad(a)"
                                >
                                    <div class="actividad-option-titulo">{{ a.producto }}</div>
                                    <div class="actividad-option-ruta">
                                        <span>{{ a.segmento }}</span>
                                        <i class="bi bi-chevron-right"></i>
                                        <span>{{ a.familia }}</span>
                                        <i class="bi bi-chevron-right"></i>
                                        <span>{{ a.clase }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="invalid-feedback d-block" v-if="campoInvalido('actividad_id')">
                            {{ campoInvalido('actividad_id') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: Cronograma -->
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-calendar-range me-2"></i>Sección 2 — Cronograma
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Fecha inicio <span class="text-danger">*</span></label>
                        <input
                            v-model="form.fecha_inicio"
                            type="date"
                            class="form-control"
                            :class="{ 'is-invalid': campoInvalido('fecha_inicio') }"
                        >
                        <div class="invalid-feedback">{{ campoInvalido('fecha_inicio') }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hora inicio <span class="text-danger">*</span></label>
                        <input
                            v-model="form.hora_inicio"
                            type="time"
                            class="form-control"
                            :class="{ 'is-invalid': campoInvalido('hora_inicio') }"
                        >
                        <div class="invalid-feedback">{{ campoInvalido('hora_inicio') }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha cierre <span class="text-danger">*</span></label>
                        <input
                            v-model="form.fecha_cierre"
                            type="date"
                            class="form-control"
                            :class="{ 'is-invalid': campoInvalido('fecha_cierre') }"
                            :min="form.fecha_inicio"
                        >
                        <div class="invalid-feedback">{{ campoInvalido('fecha_cierre') }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hora cierre <span class="text-danger">*</span></label>
                        <input
                            v-model="form.hora_cierre"
                            type="time"
                            class="form-control"
                            :class="{ 'is-invalid': campoInvalido('hora_cierre') }"
                        >
                        <div class="invalid-feedback">{{ campoInvalido('hora_cierre') }}</div>
                    </div>
                </div>
                <div v-if="campoInvalido('cronograma')" class="alert alert-warning mt-3 py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ campoInvalido('cronograma') }}
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: Documentos (solo edición) -->
        <div v-if="esEdicion" class="card mb-4">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-paperclip me-2"></i>Sección 3 — Documentos</span>
                <button type="button" class="btn btn-sm btn-primary" @click="abrirModalDocumento">
                    <i class="bi bi-plus-lg me-1"></i>Agregar documento
                </button>
            </div>
            <div class="card-body p-0">
                <div v-if="documentos.length === 0" class="text-center text-muted py-4">
                    <i class="bi bi-folder2-open fs-2 d-block mb-2"></i>
                    Sin documentos. Debe agregar al menos uno.
                </div>
                <table v-else class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Archivo</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in documentos" :key="d.id">
                            <td>{{ d.titulo }}</td>
                            <td class="text-muted small">{{ d.descripcion }}</td>
                            <td>
                                <a :href="`/licitaciones/public/${d.archivo}`" target="_blank" class="small">
                                    <i class="bi bi-download me-1"></i>Descargar
                                </a>
                            </td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    @click="confirmarEliminarDocumento(d)"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botón guardar -->
        <div class="d-flex justify-content-end">
            <button
                type="submit"
                class="btn btn-primary px-4"
                :disabled="guardando || !formularioValido"
            >
                <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-check-lg me-2"></i>
                {{ guardando ? 'Guardando...' : 'Guardar oferta' }}
            </button>
        </div>
    </form>

    <!-- Modal agregar documento -->
    <div v-if="modalDocumento" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1050;display:flex;align-items:center;justify-content:center">
        <div class="card" style="width:480px;max-width:95vw">
            <div class="card-header fw-semibold d-flex justify-content-between">
                <span>Agregar documento</span>
                <button type="button" class="btn-close" @click="cerrarModal"></button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Título <span class="text-danger">*</span></label>
                    <input v-model.trim="docForm.titulo" type="text" maxlength="100" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <input v-model.trim="docForm.descripcion" type="text" maxlength="200" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Archivo (PDF o ZIP) <span class="text-danger">*</span></label>
                    <input @change="seleccionarArchivo" type="file" accept=".pdf,.zip" class="form-control">
                    <div v-if="docError" class="text-danger small mt-1">{{ docError }}</div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="cerrarModal">Cancelar</button>
                <button
                    type="button"
                    class="btn btn-primary"
                    @click="subirDocumento"
                    :disabled="subiendoDoc || !docFormValido"
                >
                    <span v-if="subiendoDoc" class="spinner-border spinner-border-sm me-1"></span>
                    Agregar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE ELIMINACIÓN -->
    <div v-if="modalEliminarDoc" class="toast-overlay">
        <div class="toast-box" style="min-width:340px">

            <div class="toast-icon" style="background:#fef2f2">
                <i class="bi bi-trash text-danger" style="font-size:1.75rem"></i>
            </div>

            <div class="toast-title">Eliminar documento</div>

            <div class="toast-subtitle mb-3">
                ¿Estás seguro que deseas eliminar el documento
                <strong>{{ documentoEliminar?.titulo }}</strong>?
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-outline-secondary" @click="modalEliminarDoc = false">
                    Cancelar
                </button>

                <button class="btn btn-danger" @click="borrarDocumento">
                    Sí, eliminar
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
        esEdicion:            <?php echo $esEdicion ? 'true' : 'false'; ?>,
        guardadoExitoso: false,
        dropdownAbierto: false,
        hoverId: null,
        ofertaId:             <?php echo (int) ($_GET['id'] ?? 0); ?>,
        modalEliminarDoc: false,
        documentoEliminar: null,
        form: {
            objeto:       '',
            descripcion:  '',
            moneda:       '',
            presupuesto:  '',
            actividad_id: '',
            fecha_inicio: '',
            hora_inicio:  '',
            fecha_cierre: '',
            hora_cierre:  '',

        },
        // Actividades con debounce
        actividades:          [],
        busquedaActividad:    '',
        actividadSeleccionada: null,
        cargandoActividades:  false,
        timerActividad:       null,

        documentos:           [],
        errores:              [],
        mensaje:              '',
        guardando:            false,
        erroresBackend:       {},
        modalDocumento:       false,
        docForm: { titulo: '', descripcion: '', archivo: null },
        docError:             '',
        subiendoDoc:          false,
    },
    computed: {
        formularioValido() {
            const f = this.form;
            return f.objeto && f.descripcion && f.moneda &&
                   parseFloat(f.presupuesto) > 0 && f.actividad_id &&
                   f.fecha_inicio && f.hora_inicio &&
                   f.fecha_cierre && f.hora_cierre;
        },
        docFormValido() {
            return this.docForm.titulo && this.docForm.descripcion && this.docForm.archivo;
        },
    },
    async mounted() {
        if (this.esEdicion && this.ofertaId) {
            await this.cargarOferta();
        }
    },
    methods: {
        // ── Actividades con debounce ──────────────────────────
        onBuscarActividad() {
            clearTimeout(this.timerActividad);

            // Limpia resultados si borra el texto
            if (this.busquedaActividad.length < 3) {
                this.actividades = [];
                return;
            }

            // Espera 400ms después de que el usuario deja de escribir
            this.timerActividad = setTimeout(() => {
                this.buscarActividades();
            }, 400);
        },
        elegirActividad(actividad) {
            this.form.actividad_id     = actividad.id;
            this.actividadSeleccionada = actividad;
            this.actividades           = [];
            this.busquedaActividad     = '';
            this.dropdownAbierto       = false;
        },
        cerrarDropdown() {
            // Pequeño delay para permitir que el click en la opción se registre
            setTimeout(() => { this.dropdownAbierto = false; }, 150);
        },
        async buscarActividades() {
            this.cargandoActividades = true;
            try {
                const { data } = await axios.get('/licitaciones/public/api/actividades', {
                    params: { q: this.busquedaActividad, pagina: 1 }
                });
                if (data.success) {
                    this.actividades = data.data.actividades;
                    this.dropdownAbierto = this.actividades.length > 0; 
                }
            } catch (error) {
                console.error('Error al buscar actividades:', error);
            } finally {
                this.cargandoActividades = false;
            }
        },
        seleccionarActividad() {
            const encontrada = this.actividades.find(a => a.id == this.form.actividad_id);
            if (encontrada) {
                this.actividadSeleccionada = encontrada;
                this.actividades           = [];
                this.busquedaActividad     = '';
            }
        },
        limpiarActividad() {
            this.actividadSeleccionada = null;
            this.form.actividad_id     = '';
            this.busquedaActividad     = '';
            this.actividades           = [];
        },

        // ── Oferta ───────────────────────────────────────────
        async cargarOferta() {
            try {
                const { data } = await axios.get(`/licitaciones/public/ofertas/detalle?id=${this.ofertaId}`);
                if (data.success) {
                    const o = data.data;
                    this.form = {
                        objeto:       o.objeto,
                        descripcion:  o.descripcion,
                        moneda:       o.moneda,
                        presupuesto:  o.presupuesto,
                        actividad_id: o.actividad_id,
                        fecha_inicio: o.fecha_inicio,
                        hora_inicio:  o.hora_inicio?.substring(0, 5),
                        fecha_cierre: o.fecha_cierre,
                        hora_cierre:  o.hora_cierre?.substring(0, 5),
                    };
                    this.documentos = o.documentos || [];

                    // Precarga la actividad seleccionada en edición
                    if (o.actividad) {
                        this.actividadSeleccionada = o.actividad;
                    }
                }
            } catch (error) {
                console.error('Error al cargar oferta:', error);
            }
        },
        validarPresupuesto() {
            this.form.presupuesto = this.form.presupuesto.replace(/[^0-9.]/g, '');
        },
        campoInvalido(campo) {
            return this.erroresBackend[campo] || null;
        },

        // ── Guardar ──────────────────────────────────────────
        async guardar() {
            this.errores        = [];
            this.erroresBackend = {};
            this.mensaje        = '';

            const inicio = new Date(`${this.form.fecha_inicio}T${this.form.hora_inicio}`);
            const cierre = new Date(`${this.form.fecha_cierre}T${this.form.hora_cierre}`);
            if (inicio >= cierre) {
                this.erroresBackend.cronograma = 'La fecha/hora de cierre debe ser posterior a la de inicio.';
                return;
            }

            const fd = new FormData();
            Object.entries(this.form).forEach(([k, v]) => fd.append(k, v));
            if (this.esEdicion) fd.append('id', this.ofertaId);

            const url = this.esEdicion
                ? '/licitaciones/public/ofertas/actualizar'
                : '/licitaciones/public/ofertas/guardar';

            this.guardando = true;
            try {
                const { data } = await axios.post(url, fd);
                if (data.success) {
                    // Muestra overlay con barra de progreso y redirige al terminar
                    this.guardadoExitoso = true;
                    setTimeout(() => {
                        window.location.href = '/licitaciones/public/ofertas';
                    }, 1900); // coincide con la animación de progress (1.8s + margen)
                }
            } catch ({ response }) {
                if (response?.data?.errores) {
                    this.erroresBackend = response.data.errores;
                    this.errores        = Object.values(response.data.errores);
                } else {
                    this.errores = ['Ocurrió un error inesperado. Intente de nuevo.'];
                }
            } finally {
                this.guardando = false;
            }
        },

        // ── Documentos ───────────────────────────────────────
        abrirModalDocumento() {
            this.docForm  = { titulo: '', descripcion: '', archivo: null };
            this.docError = '';
            this.modalDocumento = true;
        },
        cerrarModal() {
            this.modalDocumento = false;
        },
        seleccionarArchivo(e) {
            const file = e.target.files[0];
            const ext  = file?.name.split('.').pop().toLowerCase();
            if (!['pdf', 'zip'].includes(ext)) {
                this.docError        = 'Solo se permiten archivos PDF o ZIP.';
                this.docForm.archivo = null;
                return;
            }
            this.docError        = '';
            this.docForm.archivo = file;
        },
        async subirDocumento() {
            const fd = new FormData();
            fd.append('licitacion_id', this.ofertaId);
            fd.append('titulo',        this.docForm.titulo);
            fd.append('descripcion',   this.docForm.descripcion);
            fd.append('archivo',       this.docForm.archivo);

            this.subiendoDoc = true;
            try {
                const { data } = await axios.post('/licitaciones/public/ofertas/documento/subir', fd);
                if (data.success) {
                    this.documentos.push(data.data);
                    this.cerrarModal();
                }
            } catch ({ response }) {
                this.docError = response?.data?.mensaje || 'Error al subir el archivo.';
            } finally {
                this.subiendoDoc = false;
            }
        },
        async borrarDocumento() {
            const id = this.documentoEliminar.id;

            const fd = new FormData();
            fd.append('id', id);

            try {
                const { data } = await axios.post(
                    '/licitaciones/public/ofertas/documento/borrar',
                    fd
                );

                if (data.success) {
                    this.documentos = this.documentos.filter(d => d.id !== id);
                    this.modalEliminarDoc = false;
                    this.documentoEliminar = null;
                }
            } catch ({ response }) {
                alert(response?.data?.mensaje || 'No se pudo eliminar.');
            }
        },
        confirmarEliminarDocumento(doc) {
            this.documentoEliminar = doc;
            this.modalEliminarDoc = true;
        }
    },
});
</script>