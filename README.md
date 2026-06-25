# 🏛️ Módulo de Licitaciones — Prueba Técnica FullStack PHP Junior

Sistema de gestión de licitaciones desarrollado en PHP 7+ bajo el patrón MVC, con frontend en Vue.js 2.6.

---

## 📋 Tabla de contenidos

- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Decisiones técnicas justificadas](#decisiones-técnicas-justificadas)
- [Funcionalidades implementadas](#funcionalidades-implementadas)
- [API endpoints](#api-endpoints)
- [Credenciales por defecto](#credenciales-por-defecto)

---

## Requisitos

| Herramienta | Versión mínima |
|---|---|
| PHP | 7.4+ |
| MySQL / MariaDB | 5.7+ |
| Composer | 2.x |
| Servidor web | Apache con `mod_rewrite` activo |

> Se recomienda **Laragon** como entorno local — incluye PHP, MySQL, Apache y Composer preconfigurados.

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/AlexandroY11/licitaciones.git
cd licitaciones
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita `.env` con tus credenciales de base de datos:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=licitaciones_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Crear la base de datos

Ejecuta el script SQL incluido:

```bash
mysql -u root -p < bd/schema.sql
```

O desde phpMyAdmin: importa el archivo `bd/schema.sql`.

### 5. Importar el clasificador UNSPSC

La tabla `actividades` se alimenta del clasificador de bienes y servicios de Naciones Unidas. Ejecuta el script de importación:

```bash
php bd/importar_actividades.php
```

> El script descarga automáticamente el archivo Excel desde la fuente oficial, lo procesa en lotes de 500 registros e importa ~48.000 actividades. Requiere conexión a internet en la primera ejecución. Las ejecuciones posteriores usan el archivo en caché local (`bd/unspsc.xlsx`).

### 6. Configurar el servidor web

**Con Laragon:** coloca el proyecto en `C:\laragon\www\licitaciones\`. Laragon crea automáticamente el virtual host `licitaciones.test`.

**Con XAMPP/WAMP:** asegúrate de que `mod_rewrite` esté activo en `httpd.conf`:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

### 7. Acceder al sistema

```
http://localhost/licitaciones/public/
```

---

## Estructura del proyecto

```
licitaciones/
├── app/
│   ├── Controllers/
│   │   ├── ActividadController.php   # API de búsqueda paginada UNSPSC
│   │   ├── DocumentoController.php   # Upload y gestión de documentos
│   │   └── OfertaController.php      # CRUD completo de licitaciones
│   ├── Helpers/
│   │   ├── Response.php              # Respuestas JSON estandarizadas
│   │   └── Validator.php             # Validaciones reutilizables con encadenamiento
│   └── Models/
│       ├── Actividad.php             # Modelo UNSPSC con relación a ofertas
│       ├── Oferta.php                # Modelo principal + generación de consecutivo
│       └── OfertaDocumento.php       # Modelo de documentos adjuntos
├── bd/
│   ├── schema.sql                    # Script DDL de la base de datos
│   └── importar_actividades.php      # Script de importación UNSPSC
├── bootstrap/
│   ├── database.php                  # Inicialización de Eloquent standalone
│   └── router.php                    # Router HTTP nativo
├── public/
│   ├── .htaccess                     # Rewrite rules para Apache
│   ├── index.php                     # Front controller — único punto de entrada
│   └── uploads/                      # Archivos subidos (PDF/ZIP)
├── views/
│   ├── errors/
│   │   └── 404.php                   # Página de error personalizada
│   ├── layout/
│   │   ├── header.php                # Layout compartido — cabecera + CDNs
│   │   └── footer.php                # Layout compartido — scripts
│   └── ofertas/
│       ├── index.php                 # Listado con filtros y paginación
│       ├── form.php                  # Formulario crear/editar (mismo componente)
│       └── detalle.php               # Vista de detalle con pestañas
├── .env.example                      # Plantilla de variables de entorno
├── composer.json
└── README.md
```

---

## Decisiones técnicas justificadas

### ¿Por qué un router nativo en lugar de FastRoute u otra librería?

Dado el alcance de la prueba, los requerimientos de enrutamiento son relativamente simples y están limitados a un conjunto reducido de endpoints. Incorporar una librería externa para resolver esta necesidad introduciría una dependencia adicional sin aportar un beneficio significativo en términos de funcionalidad, mantenibilidad o escalabilidad dentro del contexto actual.

La implementación de un router nativo permite:

Mantener una base de código más ligera y con menos dependencias externas.
Tener control total sobre el flujo de resolución de rutas y generación de respuestas.
Adaptar fácilmente el comportamiento del router a los requisitos específicos de la aplicación, como la diferenciación entre respuestas HTML y JSON.
Reducir la complejidad operativa y el acoplamiento con componentes de terceros para una funcionalidad que puede resolverse de manera sencilla y predecible.

En este proyecto, el router no requiere características avanzadas como middleware complejos, cacheo de rutas, expresiones de enrutamiento sofisticadas o integración con otros componentes de un framework. Por ello, una implementación nativa resulta suficiente, mantiene la solución más simple y evita incorporar abstracciones adicionales que no generan valor para los objetivos planteados.

Además, al tratarse de una pieza central pero de alcance controlado, su implementación propia facilita comprender y modificar el comportamiento de la aplicación sin depender de APIs o convenciones externas.
---

### ¿Por qué Eloquent standalone en lugar de PDO puro?

La prueba permite explícitamente el uso de un ORM tipo Eloquent. Se optó por `illuminate/database` en su versión standalone (sin Laravel) porque:

- Provee consultas seguras contra inyección SQL mediante query bindings
- El patrón ActiveRecord mantiene la lógica de negocio en el modelo
- `lockForUpdate()` permite transacciones atómicas sin complejidad adicional
- Los scopes y relaciones (`hasMany`, `belongsTo`) hacen el código más expresivo y mantenible

---

### ¿Por qué Vue.js via CDN en lugar de Vite + SPA?

Se eligió integración via CDN deliberadamente por tres razones:

1. **Facilidad de evaluación** — Solo se necesita PHP y MySQL para correr el proyecto. No requiere `node`, `npm install` ni `npm run build`.
2. **La prueba especifica Vue 2.6** — Vite es más natural con Vue 3. Usar Vite con Vue 2 habría añadido complejidad sin beneficio real.
3. **Separación clara MVC** — las vistas PHP sirven el HTML y Vue toma el control del lado del cliente, respetando la arquitectura solicitada.

---

### ¿Por qué el consecutivo se reinicia por año?

El formato `O-{0001}-{YY}` implica que el sufijo del año es parte identificadora del consecutivo. En sistemas de licitaciones reales (y en normativa de contratación pública), los consecutivos se reinician anualmente para facilitar la trazabilidad por vigencia presupuestal. Así, `O-0001-27` identifica inequívocamente la primera licitación del año 2027.

La generación usa `lockForUpdate()` dentro de una transacción para garantizar atomicidad y evitar condiciones de carrera en entornos concurrentes.

---

### ¿Por qué búsqueda con debounce en el selector de actividades?

El clasificador UNSPSC contiene ~48.000 registros. Cargar todo el catálogo en un `<select>` sería inviable. Se implementó:

- **Mínimo 3 caracteres** antes de consultar — evita queries inútiles
- **Debounce de 400ms** — agrupa las pulsaciones del teclado y solo consulta cuando el usuario se detiene
- **Paginación de 20 resultados** — la API nunca devuelve más de 20 registros por consulta
- **Búsqueda en producto, clase y familia** — resultados relevantes desde cualquier nivel del clasificador

---

### ¿Por qué un Validator con encadenamiento de métodos?

El `Validator` implementa el patrón **Fluent Interface**, que permite encadenar validaciones de forma legible:

```php
$v->requerido('objeto', 'Objeto')
  ->maxLength('objeto', 150, 'Objeto')
  ->moneda('moneda')
  ->cronograma('fecha_inicio', 'hora_inicio', 'fecha_cierre', 'hora_cierre');
```

Esto centraliza todas las reglas de negocio en el controlador, evita repetición de código y hace que las validaciones sean fácilmente auditables y extensibles.

---

## Funcionalidades implementadas

| Funcionalidad | Estado |
|---|---|
| Crear licitación con consecutivo automático | ✅ |
| Editar licitación | ✅ |
| Cargar documentos PDF/ZIP en edición | ✅ |
| Validación mínimo 1 documento en edición | ✅ |
| Listar licitaciones con paginación | ✅ |
| Filtrar por consecutivo, objeto o descripción | ✅ |
| Ver detalle con pestañas | ✅ |
| Exportar Excel desde el listado | ✅ |
| Búsqueda de actividades con debounce | ✅ |
| Validaciones frontend (Vue) y backend (PHP) | ✅ |
| Validación cruzada fecha/hora inicio < cierre | ✅ |
| Restricción de tipo de archivo (PDF/ZIP) | ✅ |
| Página 404 personalizada | ✅ |
| Importación UNSPSC desde Excel oficial | ✅ |

---

## API endpoints

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/ofertas` | Listado paginado con filtros |
| GET | `/ofertas/crear` | Vista formulario creación |
| GET | `/ofertas/editar?id=` | Vista formulario edición |
| GET | `/ofertas/detalle?id=` | Detalle de oferta |
| GET | `/ofertas/exportar?q=` | Descarga Excel |
| GET | `/api/actividades?q=&pagina=` | Búsqueda paginada UNSPSC |
| POST | `/ofertas/guardar` | Crear oferta |
| POST | `/ofertas/actualizar` | Actualizar oferta |
| POST | `/ofertas/documento/subir` | Subir documento |
| POST | `/ofertas/documento/borrar` | Eliminar documento |

---

## Credenciales por defecto

El proyecto no requiere autenticación. La base de datos se crea con el script `bd/schema.sql` usando las credenciales definidas en `.env`.

---

> Desarrollado por **Alexandro** como prueba técnica para Suplos — Junio 2025.