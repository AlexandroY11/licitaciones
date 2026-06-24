<?php
$esEdicion    = isset($_GET['id']);
$tituloPagina = $esEdicion ? 'Editar oferta' : 'Nueva oferta';
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div id="app" v-cloak>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-semibold mb-0">
            <i class="bi bi-<?= $esEdicion ? 'pencil' : 'plus-circle' ?> me-2 text-primary"></i>
            <?= $tituloPagina ?>
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
                        <select
                            v-model="form.actividad_id"
                            class="form-select"
                            :class="{ 'is-invalid': campoInvalido('actividad_id') }"
                            :disabled="cargandoActividades"
                        >
                            <option value="">
                                {{ cargandoActividades ? 'Cargando...' : 'Seleccione...' }}
                            </option>
                            <option v-for="a in actividades" :key="a.id" :value="a.id">
                                {{ a.producto }}
                            </option>
                        </select>
                        <div class="invalid-feedback">{{ campoInvalido('actividad_id') }}</div>
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
                                    @click="borrarDocumento(d.id)"
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
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
<script>
new Vue({
    el: '#app',
    data: {
        esEdicion:           <?= $esEdicion ? 'true' : 'false' ?>,
        ofertaId:            <?= (int)($_GET['id'] ?? 0) ?>,
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
        actividades:         [],
        cargandoActividades: false,
        documentos:          [],
        errores:             [],
        mensaje:             '',
        guardando:           false,
        erroresBackend:      {},
        modalDocumento:      false,
        docForm: { titulo: '', descripcion: '', archivo: null },
        docError:            '',
        subiendoDoc:         false,
    },
    computed: {
        formularioValido() {
            const f = this.form;
            return f.objeto && f.descripcion && f.moneda &&
                   f.presupuesto > 0 && f.actividad_id &&
                   f.fecha_inicio && f.hora_inicio &&
                   f.fecha_cierre && f.hora_cierre;
        },
        docFormValido() {
            return this.docForm.titulo && this.docForm.descripcion && this.docForm.archivo;
        },
    },
    async mounted() {
        await this.cargarActividades();
        if (this.esEdicion && this.ofertaId) {
            await this.cargarOferta();
        }
    },
    methods: {
        async cargarActividades() {
            this.cargandoActividades = true;
            try {
                const { data } = await axios.get('/licitaciones/public/api/actividades');
                if (data.success) this.actividades = data.data;
            } catch (error) {
                console.error('Error al cargar actividades:', error);
            } finally {
                this.cargandoActividades = false;
            }
        },
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
                        hora_inicio:  o.hora_inicio,
                        fecha_cierre: o.fecha_cierre,
                        hora_cierre:  o.hora_cierre,
                    };
                    this.documentos = o.documentos || [];
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
                    this.mensaje = data.mensaje;
                    if (!this.esEdicion) {
                        setTimeout(() => { window.location.href = '/licitaciones/public/ofertas'; }, 1500);
                    }
                }
            } catch ({ response }) {
                if (response?.data?.errores) {
                    this.erroresBackend = response.data.errores;
                    this.errores = Object.values(response.data.errores);
                } else {
                    this.errores = ['Ocurrió un error inesperado. Intente de nuevo.'];
                }
            } finally {
                this.guardando = false;
            }
        },
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
        async borrarDocumento(id) {
            if (!confirm('¿Eliminar este documento?')) return;
            const fd = new FormData();
            fd.append('id', id);
            try {
                const { data } = await axios.post('/licitaciones/public/ofertas/documento/borrar', fd);
                if (data.success) {
                    this.documentos = this.documentos.filter(d => d.id !== id);
                }
            } catch ({ response }) {
                alert(response?.data?.mensaje || 'No se pudo eliminar.');
            }
        },
    },
});
</script>