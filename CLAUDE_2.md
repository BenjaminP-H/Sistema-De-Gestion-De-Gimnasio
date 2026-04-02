# CLAUDE.md — GymAdmin (Sistema SaaS Multi-Gym)

> **Instrucciones para agentes de IA:** Este archivo es la fuente de verdad del proyecto.
> Leelo **completo** antes de tocar cualquier archivo. Contiene arquitectura, convenciones,
> reglas de seguridad y el schema real de la base de datos.

---

## 1. ¿QUÉ ES ESTE PROYECTO?

**GymAdmin** es una aplicación web SaaS multi-tenant para administrar gimnasios.

- El **dueño del sistema** (`admin_general`) vende acceso a la plataforma a distintos gimnasios.
- Cada **gimnasio** (`admin_gym`) gestiona sus propios clientes, planes y pagos de forma aislada.
- Los **empleados** (`empleado`) operan dentro de un solo gimnasio.

Corre sobre **XAMPP** en desarrollo: `http://localhost/gym/`
Panel del dueño: `http://localhost/gym/admin/login.php`
Panel de gym: `http://localhost/gym/frontend/index.php`

---

## 2. STACK TECNOLÓGICO

| Capa | Tecnología | Notas |
|------|-----------|-------|
| Frontend | HTML5, Bootstrap 5.3.3, CSS custom, JS Vanilla | Bootstrap está en `/bootstrap-5.3.3-dist/` (local, sin CDN) |
| Backend | PHP puro (sin framework) | Arquitectura MVC manual |
| Base de datos | MySQL / MariaDB (XAMPP) | PDO con prepared statements, siempre |
| Autenticación | Sesiones PHP nativas (`$_SESSION`) | Verificadas en `reutilizable/session.php` (gym) y `admin/reutilizable/session_admin.php` (admin) |
| Servidor | Apache + XAMPP | `.htaccess` para rutas limpias |

---

## 3. ESTRUCTURA DE ARCHIVOS (REAL Y COMPLETA)

```
C:\xampp\htdocs\gym\
│
├── index.php                              # Punto de entrada / redirect al frontend
│
├── admin/                                 # ← PANEL DEL DUEÑO (admin_general)
│   ├── login.php                          # Login exclusivo del admin general
│   ├── logout.php                         # Cierra sesión del admin general
│   ├── panel_inicio.php                   # Dashboard: tabla gyms, filtros, buscador
│   ├── nuevo_gym.php                      # Formulario: crear gym + usuario admin_gym
│   ├── ver_gym.php                        # Detalle de gym + auditoría + acciones
│   ├── cobros.php                         # Historial y registro de cobros
│   │
│   ├── backend/
│   │   ├── procesar_login.php             # Valida login y crea sesión admin
│   │   ├── guardar_gym.php                # INSERT gym + usuario (transacción)
│   │   ├── suspender_gym.php              # Estado → suspendido + auditoría
│   │   ├── activar_gym.php                # Estado → activo + auditoría
│   │   ├── cancelar_gym.php               # Estado → cancelado + auditoría
│   │   ├── registrar_cobro.php            # INSERT pago + actualiza suscripcion_vence
│   │   ├── entrar_gym.php                 # Inicia impersonation
│   │   └── salir_gym.php                  # Termina impersonation
│   │
│   └── reutilizable/
│       ├── session_admin.php              # Verifica rol admin_general
│       ├── header.php                     # Header del panel admin
│       ├── menu.php                       # Menú + banner de impersonation
│       └── footer.php                     # Footer del panel admin
│
├── frontend/                              # ← PANEL DEL GYM (admin_gym / empleado)
│   ├── index.php                          # Login del gym
│   ├── inicio.php                         # Dashboard post-login
│   ├── clientes.php                       # Listado de clientes
│   ├── buscar_cliente.php                 # Búsqueda AJAX de clientes
│   ├── registro.php                       # Alta de nuevo cliente
│   ├── cliente_actualizado.php            # Confirmación de edición
│   └── renovacion.php                     # Formulario de renovación de membresía
│
├── backend/                               # Backend compartido del panel gym
│   ├── procesar.php                       # Procesa alta de cliente
│   ├── procesar_renovacion.php            # Procesa renovación de membresía
│   ├── confirmar_registro.php             # Confirma y persiste el registro
│   ├── confirmar_renovacion.php           # Confirma y persiste la renovación
│   ├── cancelar_registro.php              # Cancela un registro pendiente
│   └── cargar_usuario.php                 # Carga datos de usuario (AJAX)
│
├── reutilizable/                          # Componentes PHP globales (ambos paneles)
│   ├── header.php                         # <head> + apertura de layout
│   ├── menu.php                           # Navbar / sidebar de navegación
│   ├── footer.php                         # Cierre de layout + scripts
│   ├── session.php                        # Inicia sesión PHP
│   └── funciones.php                      # Conexión PDO + funciones utilitarias
│
├── js/
│   ├── main.js
│   ├── utils.js
│   ├── buscar-cliente.js
│   ├── api/
│   │   ├── autch.js                       # ⚠️ typo conocido, no renombrar sin avisar
│   │   └── clientes.js
│   └── components/
│       ├── alert.js
│       └── modal.js
│
├── css/
│   └── estilos.css
│
├── bootstrap-5.3.3-dist/                  # Bootstrap local — NO tocar
│   ├── css/
│   └── js/
│
├── img/
│   ├── clientes/                          # Fotos confirmadas (cliente_{hash}.ext)
│   └── temporal/                          # Fotos pre-confirmación (tmp_{hash}.ext)
│
├── database/
│   ├── registrogym.sql                    # Schema base inicial
│   └── patches/
│       ├── 2026-03-12_multigym.sql
│       ├── 2026-03-12_security_renovacion.sql
│       └── 2026-03-30_admin_general.sql   # Tablas del panel admin_general
│
├── CLAUDE.md
└── README.md
```

---

## 4. BASE DE DATOS — SCHEMA COMPLETO

**Base de datos:** `sistema_gimnasios`

### Tabla: `gyms`
```sql
id                   INT AUTO_INCREMENT PRIMARY KEY
nombre               VARCHAR(100) NOT NULL
direccion            VARCHAR(150)
telefono             VARCHAR(30)
email                VARCHAR(100)
estado               ENUM('activo','suspendido','cancelado') DEFAULT 'activo'
suscripcion_vence    DATE NULL
ultimo_acceso        TIMESTAMP NULL
suscripcion_plan_id  INT NULL
fecha_creacion       TIMESTAMP DEFAULT CURRENT_TIMESTAMP

FK: suscripcion_plan_id → suscripciones_planes(id) ON DELETE SET NULL
```

---

### Tabla: `usuarios`
```sql
id              INT AUTO_INCREMENT PRIMARY KEY
gym_id          INT NULL               -- NULL si es admin_general
nombre          VARCHAR(100) NOT NULL
usuario         VARCHAR(50) NOT NULL UNIQUE
password        VARCHAR(255) NOT NULL  -- bcrypt, nunca texto plano
rol             ENUM('admin_general','admin_gym','empleado')
activo          TINYINT(1) DEFAULT 1
fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP

FK: gym_id → gyms(id) ON DELETE CASCADE
```

---

### Tabla: `clientes`
```sql
id               INT AUTO_INCREMENT PRIMARY KEY
gym_id           INT NOT NULL
nombre           VARCHAR(100) NOT NULL
apellido         VARCHAR(100)
dni              VARCHAR(20)
telefono         VARCHAR(30)
email            VARCHAR(100)
fecha_nacimiento DATE
fecha_alta       DATE NOT NULL
estado           ENUM('activo','inactivo') DEFAULT 'activo'

FK: gym_id → gyms(id) ON DELETE CASCADE
```

---

### Tabla: `planes`
```sql
id              INT AUTO_INCREMENT PRIMARY KEY
nombre          VARCHAR(100) NOT NULL
precio_base     DECIMAL(10,2) DEFAULT 0
duracion_dias   INT DEFAULT 30
```
> Catálogo global. Datos base: Pase libre, Aparatos, Funcional, Zumba (30 días), Día (1 día).

---

### Tabla: `gym_planes`
```sql
id       INT AUTO_INCREMENT PRIMARY KEY
gym_id   INT NOT NULL
plan_id  INT NOT NULL
precio   DECIMAL(10,2) NOT NULL
activo   TINYINT(1) DEFAULT 1

FK: gym_id  → gyms(id)   ON DELETE CASCADE
FK: plan_id → planes(id) ON DELETE CASCADE
```

---

### Tabla: `pagos`
```sql
id                INT AUTO_INCREMENT PRIMARY KEY
gym_id            INT NOT NULL
cliente_id        INT NOT NULL
gym_plan_id       INT NOT NULL
monto             DECIMAL(10,2) NOT NULL
fecha_pago        DATE NOT NULL
fecha_vencimiento DATE NOT NULL

FK: gym_id      → gyms(id)       ON DELETE CASCADE
FK: cliente_id  → clientes(id)   ON DELETE CASCADE
FK: gym_plan_id → gym_planes(id) ON DELETE CASCADE
```

---

### Tabla: `suscripciones_planes`
```sql
id          INT AUTO_INCREMENT PRIMARY KEY
nombre      VARCHAR(100) NOT NULL
precio      DECIMAL(10,2) NOT NULL
descripcion VARCHAR(255)
activo      TINYINT(1) DEFAULT 1
```
> Planes que el dueño ofrece a los gyms. Actualmente vacía.

---

### Tabla: `pagos_suscripciones`
```sql
id              INT AUTO_INCREMENT PRIMARY KEY
gym_id          INT NOT NULL
monto           DECIMAL(10,2) NOT NULL
fecha_pago      DATE NOT NULL
periodo_desde   DATE NOT NULL
periodo_hasta   DATE NOT NULL
notas           VARCHAR(255)
registrado_por  INT NOT NULL
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

FK: gym_id         → gyms(id)     ON DELETE CASCADE
FK: registrado_por → usuarios(id)
```
> Al registrar un cobro se actualiza automáticamente `gyms.suscripcion_vence`.

---

### Tabla: `auditoria`
```sql
id          INT AUTO_INCREMENT PRIMARY KEY
gym_id      INT NOT NULL
usuario_id  INT NOT NULL
accion      VARCHAR(100) NOT NULL
detalle     TEXT
created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP

FK: gym_id     → gyms(id)     ON DELETE CASCADE
FK: usuario_id → usuarios(id)
```
> Log inmutable. Solo INSERT, nunca UPDATE ni DELETE.
> Acciones registradas: `crear_gym`, `suspender`, `activar`, `cancelar`, `registrar_cobro`.

---

### Relaciones entre tablas

```
gyms
 ├── usuarios              (gym_id)
 ├── clientes              (gym_id)
 ├── gym_planes            (gym_id) → planes (plan_id)
 ├── pagos                 (gym_id, cliente_id, gym_plan_id)
 ├── pagos_suscripciones   (gym_id)
 ├── auditoria             (gym_id)
 └── suscripciones_planes  (via gyms.suscripcion_plan_id)
```

---

## 5. ROLES Y PERMISOS

| Rol | `gym_id` | Accede a | Puede hacer |
|-----|----------|----------|-------------|
| `admin_general` | NULL | `/admin/` | Gestionar todos los gyms, cobros, auditoría |
| `admin_gym` | gym específico | `/frontend/` | CRUD clientes, planes y pagos de su gym |
| `empleado` | gym específico | `/frontend/` | Registrar pagos/renovaciones, buscar clientes |

---

## 6. SEPARACIÓN DE PANELES E IMPERSONATION

Los dos paneles son **completamente independientes**. Nunca mezclar archivos entre ellos.

```
/admin/        → exclusivo admin_general
/frontend/     → exclusivo admin_gym y empleado
/reutilizable/ → compartido (funciones.php, session.php)
```

### Impersonation
```php
// entrar_gym.php guarda:
$_SESSION['impersonando_gym_id']     = $gym_id;
$_SESSION['impersonando_gym_nombre'] = $gym_nombre;

// frontend lee gym_id así:
$gym_id = $_SESSION['gym_id'] ?? $_SESSION['impersonando_gym_id'] ?? null;

// salir_gym.php limpia:
unset($_SESSION['impersonando_gym_id'], $_SESSION['impersonando_gym_nombre']);
```
El menú del admin muestra un banner amarillo cuando hay impersonation activa.

---

## 7. REGLAS CRÍTICAS DE SEGURIDAD

> **⚠️ Agente: estas reglas no son opcionales.**

1. **PDO siempre con prepared statements.**
```php
// ✅ CORRECTO
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND gym_id = ?");
$stmt->execute([$id, $gym_id]);
// ❌ NUNCA
$pdo->query("SELECT * FROM clientes WHERE id = $id");
```

2. **Siempre filtrar por `gym_id`** en queries de clientes, pagos y gym_planes.

3. **Verificar sesión al inicio de cada archivo.**
```php
require_once __DIR__ . '/reutilizable/session_admin.php'; // panel admin
require_once __DIR__ . '/../reutilizable/session.php';    // panel gym
```
> Usar siempre `__DIR__` en los require_once para evitar errores de ruta.

4. **Passwords con `password_hash()` / `password_verify()`.** Nunca MD5, SHA1 ni texto plano.

5. **Imágenes:** temporales en `img/temporal/`, confirmadas en `img/clientes/`.

6. **Toda acción crítica sobre gyms debe registrarse en `auditoria`.**

7. **Login del panel gym debe validar `activo = 1`.**
```php
// ✅ CORRECTO — usuarios desactivados no pueden entrar
WHERE usuario = ? AND activo = 1
```

8. **Login del panel admin debe regenerar el ID de sesión.**
```php
// En admin/backend/procesar_login.php, tras verificar credenciales:
session_regenerate_id(true);
$_SESSION['admin_login'] = true;
```

9. **`session_admin.php` verifica doble condición de sesión.**
```php
// Debe exigir AMBAS condiciones:
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin_general' || !isset($_SESSION['admin_login'])) {
    header('Location: ../login.php'); exit;
}
```

10. **Acciones críticas del admin usan POST, nunca GET.**
```php
// ✅ CORRECTO — verificar método antes de ejecutar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel_inicio.php'); exit;
}
$gym_id = (int)($_POST['id'] ?? 0);
```
> Los botones de suspender/activar/cancelar/entrar en las vistas usan formularios POST, no links con `?id=`.

---

## 8. CONVENCIONES DE CÓDIGO

### PHP
- Archivos: `snake_case.php`
- Variables y funciones: `camelCase`
- Conexión PDO: siempre desde `reutilizable/funciones.php`

### SQL
- Tablas: `snake_case` plural — Columnas: `snake_case`
- Fechas: siempre `DATE` o `TIMESTAMP`, nunca `VARCHAR`

### JavaScript
- Vanilla JS, sin jQuery, sin frameworks
- Módulos AJAX en `js/api/` — Componentes UI en `js/components/`

### HTML — REGLA CRÍTICA ⚠️
> **Nunca usar `<div>` como contenedor estructural.**

```html
<!-- ❌ NUNCA -->
<div class="bloque-gyms">
    <div class="gym-item">...</div>
</div>

<!-- ✅ SIEMPRE -->
<section class="bloque-gyms">
    <article class="gym-item">...</article>
</section>
```

- **`<section>`** → bloques de contenido principales de una página
- **`<article>`** → elementos repetibles o independientes (cards, filas, items)
- **`<div>`** → solo dentro de componentes Bootstrap que lo requieran estrictamente (ej: `dropdown-menu`, `collapse`, `modal-body`)

### CSS
- Bootstrap 5.3.3 local (`/bootstrap-5.3.3-dist/`)
- Clases custom con prefijo `ga-` (ej: `ga-card-cliente`)
- Estilos custom solo en `css/estilos.css`
- El `<body>` del panel admin tiene clase `ga-admin` — los estilos específicos del admin van bajo ese selector en `css/estilos.css`
- El panel admin usa fondo con imagen — no remover esa regla de `css/estilos.css`
- El `admin/reutilizable/header.php` incluye `?v=<?= time() ?>` en el CSS para evitar caché durante desarrollo

---

## 9. FLUJO DE OPERACIONES TÍPICAS

### Crear gym nuevo (admin)
```
admin/nuevo_gym.php → admin/backend/guardar_gym.php
  → BEGIN TRANSACTION
  → INSERT gyms + INSERT usuarios (admin_gym)
  → INSERT auditoria ('crear_gym')
  → COMMIT → redirect con mensaje
```

### Registrar cobro (admin)
```
admin/cobros.php → admin/backend/registrar_cobro.php
  → INSERT pagos_suscripciones
  → UPDATE gyms SET suscripcion_vence = periodo_hasta
  → INSERT auditoria ('registrar_cobro')
```

### Registrar cliente nuevo (gym)
```
frontend/registro.php → backend/procesar.php → frontend/confirmar → backend/confirmar_registro.php
```

### Renovar membresía (gym)
```
frontend/renovacion.php → backend/procesar_renovacion.php → backend/confirmar_renovacion.php
```

---

## 10. ARCHIVO: `reutilizable/funciones.php`

| Función | Descripción |
|---------|-------------|
| `conectar_db()` | Devuelve PDO a `sistema_gimnasios` |
| `verificarSesion()` | Verifica sesión activa |
| `verificarLogueo()` | Muestra/limpia `$_SESSION['error_login']` |
| `calcularNuevaFechaVencimiento($fecha, $dias)` | Calcula vencimiento de membresía |
| `obtenerPlanId($pdo, $nombre)` | Busca id de plan por nombre |
| `notificar_admin($usuario, $ip)` | Alerta de seguridad por email |

### Deuda técnica

| # | Problema | Estado |
|---|---------|--------|
| 1 | `$db = 'registrogym'` → `sistema_gimnasios` | ✅ Resuelto |
| 2 | `obtenerPlanId` usaba columnas incorrectas | ✅ Resuelto |
| 3 | `verificarSesion()` no validaba el rol | ✅ Resuelto |
| 4 | Credenciales hardcodeadas | ⏳ Resolver antes de producción |
| 5 | `$admin_email` y `$admin_sms` hardcodeados | ⏳ Resolver antes de producción |
| 6 | Columna `foto` no existe en `clientes` — se sube al disco pero no se persiste en BD | ⏳ Pendiente |
| 7 | Columna `metodo_pago` no existe en `pagos` — se captura en formulario pero se pierde | ⏳ Pendiente |

---

## 11. PENDIENTES Y PRÓXIMOS PASOS

**Resuelto en esta sesión por Codex ✅**
- [x] Login del gym bloquea usuarios con `activo = 0`
- [x] Login admin con `session_regenerate_id(true)` y `$_SESSION['admin_login']`
- [x] `session_admin.php` verifica doble condición de sesión
- [x] Bootstrap migrado de CDN a local en todo el proyecto
- [x] Acciones críticas del admin convertidas de GET a POST
- [x] `require_once` del admin usan `__DIR__` para rutas seguras
- [x] `admin/login.php` creado, `admin/login_1.php` eliminado
- [x] Diseño del panel admin mejorado con SVG en lugar de emojis

**Pendiente — próximos pasos**
- [ ] Implementar PIN de seguridad para acciones críticas
      → `ALTER TABLE usuarios ADD COLUMN pin_accion VARCHAR(255) NULL`
      → Crear `admin/backend/verificar_pin.php`
      → Descomentar modal de PIN en `admin/ver_gym.php` y `admin/panel_inicio.php`
- [ ] Actualizar `frontend/inicio.php` para soportar impersonation
      → `$gym_id = $_SESSION['gym_id'] ?? $_SESSION['impersonando_gym_id'] ?? null`
- [ ] Persistir nombre de foto en BD (agregar columna `foto` en `clientes`)
- [ ] Persistir `metodo_pago` en BD (agregar columna en `pagos`)
- [ ] Renombrar `js/api/autch.js` → `auth.js` cuando se refactorice
- [ ] Validación de tamaño/tipo de imagen en backend
- [ ] Credenciales de BD a `.env` antes de producción

---

## 12. COMANDOS ÚTILES

```bash
# URLs
http://localhost/gym/admin/login.php       # panel dueño
http://localhost/gym/frontend/index.php    # panel gym

# Importar BD desde cero (en orden)
mysql -u root -p < database/registrogym.sql
mysql -u root -p sistema_gimnasios < database/patches/2026-03-12_multigym.sql
mysql -u root -p sistema_gimnasios < database/patches/2026-03-12_security_renovacion.sql
mysql -u root -p sistema_gimnasios < database/patches/2026-03-30_admin_general.sql
```

---

## 13. LO QUE NO DEBE HACER UN AGENTE EN ESTE PROYECTO

- ❌ No sugerir Laravel, Symfony ni Composer (PHP puro)
- ❌ No usar jQuery (Vanilla JS únicamente)
- ❌ No usar Bootstrap por CDN — siempre local desde `/bootstrap-5.3.3-dist/`
- ❌ No crear nuevas conexiones PDO fuera de `reutilizable/funciones.php`
- ❌ No escribir queries con variables interpoladas
- ❌ No omitir el filtro `gym_id` en queries de datos operativos
- ❌ No guardar passwords en texto plano ni con MD5/SHA1
- ❌ No usar `<div>` como contenedor estructural — usar `<section>` y `<article>`
- ❌ No mezclar archivos de `/admin/` con `/frontend/`
- ❌ No omitir el INSERT en `auditoria` al suspender, activar, cancelar o crear gyms
- ❌ No usar links GET para acciones críticas del admin — siempre formularios POST
- ❌ No usar rutas relativas en `require_once` — siempre usar `__DIR__`
- ❌ No quitar la clase `ga-admin` del `<body>` del panel admin
- ❌ No quitar el fondo con imagen del panel admin (`css/estilos.css`)
- ❌ No usar emojis en el panel admin — usar SVG inline
