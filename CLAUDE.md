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

---

## 2. STACK TECNOLÓGICO

| Capa | Tecnología | Notas |
|------|-----------|-------|
| Frontend | HTML5, Bootstrap 5.3.3, CSS custom, JS Vanilla | Bootstrap está en `/bootstrap-5.3.3-dist/` (local, sin CDN) |
| Backend | PHP puro (sin framework) | Arquitectura MVC manual |
| Base de datos | MySQL (XAMPP) | PDO con prepared statements, siempre |
| Autenticación | Sesiones PHP nativas (`$_SESSION`) | Verificadas en `reutilizable/session.php` |
| Servidor | Apache + XAMPP | `.htaccess` para rutas limpias |

---

## 3. ESTRUCTURA DE ARCHIVOS (REAL)

```
C:\xampp\htdocs\gym\
│
├── index.php                        # Punto de entrada / redirect al frontend
│
├── frontend/                        # Vistas PHP (lo que ve el usuario)
│   ├── index.php                    # Login
│   ├── inicio.php                   # Dashboard principal post-login
│   ├── clientes.php                 # Listado de clientes del gym
│   ├── buscar_cliente.php           # Búsqueda AJAX de clientes
│   ├── registro.php                 # Alta de nuevo cliente
│   ├── cliente_actualizado.php      # Confirmación de edición
│   └── renovacion.php               # Formulario de renovación de membresía
│
├── backend/                         # Lógica PHP (procesamiento de formularios y AJAX)
│   ├── procesar.php                 # Procesa alta de cliente
│   ├── procesar_renovacion.php      # Procesa renovación de membresía
│   ├── confirmar_registro.php       # Confirma y persiste el registro
│   ├── confirmar_renovacion.php     # Confirma y persiste la renovación
│   ├── cancelar_registro.php        # Cancela un registro pendiente
│   └── cargar_usuario.php           # Carga datos de usuario (probablemente AJAX)
│
├── reutilizable/                    # Componentes PHP compartidos (include/require)
│   ├── header.php                   # <head> + apertura de layout
│   ├── menu.php                     # Navbar / sidebar de navegación
│   ├── footer.php                   # Cierre de layout + scripts
│   ├── session.php                  # Verifica sesión activa y rol del usuario
│   └── funciones.php               # Funciones PHP utilitarias globales
│
├── js/                              # JavaScript del frontend
│   ├── main.js                      # Inicialización general
│   ├── utils.js                     # Funciones JS reutilizables
│   ├── buscar-cliente.js            # Lógica de búsqueda de clientes
│   ├── api/
│   │   ├── autch.js                 # Llamadas AJAX relacionadas a auth (¡revisar typo: "autch" → "auth"!)
│   │   └── clientes.js              # Llamadas AJAX del módulo clientes
│   └── components/
│       ├── alert.js                 # Componente de alertas/notificaciones
│       └── modal.js                 # Lógica de modales Bootstrap
│
├── css/
│   └── estilos.css                  # Estilos custom del proyecto
│
├── bootstrap-5.3.3-dist/            # Bootstrap local (no tocar)
│   ├── css/
│   └── js/
│
├── img/
│   ├── clientes/                    # Fotos de perfil de clientes (nombre: cliente_{hash}.ext)
│   └── temporal/                    # Imágenes temporales pre-confirmación
│
├── database/
│   ├── registrogym.sql              # Schema completo de la BD (fuente de verdad)
│   └── patches/
│       ├── 2026-03-12_multigym.sql       # Migración: soporte multi-gym
│       └── 2026-03-12_security_renovacion.sql  # Migración: seguridad renovaciones
│
├── CLAUDE.md                        # ← Este archivo
└── README.md
```

---

## 4. BASE DE DATOS — SCHEMA COMPLETO

**Base de datos:** `sistema_gimnasios`

### Tabla: `gyms`
```sql
id              INT AUTO_INCREMENT PRIMARY KEY
nombre          VARCHAR(100) NOT NULL
direccion       VARCHAR(150)
telefono        VARCHAR(30)
email           VARCHAR(100)
fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```
> Representa cada gimnasio cliente (tenant). Todo dato operativo referencia esta tabla.

---

### Tabla: `usuarios`
```sql
id              INT AUTO_INCREMENT PRIMARY KEY
gym_id          INT NULL                          -- NULL si es admin_general
nombre          VARCHAR(100) NOT NULL
usuario         VARCHAR(50) NOT NULL UNIQUE
password        VARCHAR(255) NOT NULL             -- bcrypt hash, nunca texto plano
rol             ENUM('admin_general','admin_gym','empleado')
activo          TINYINT(1) DEFAULT 1
fecha_creacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP

FK: gym_id → gyms(id) ON DELETE CASCADE
```
> `admin_general` tiene `gym_id = NULL` porque opera sobre todos los gyms.
> `admin_gym` y `empleado` siempre tienen `gym_id` asignado.

---

### Tabla: `clientes`
```sql
id               INT AUTO_INCREMENT PRIMARY KEY
gym_id           INT NOT NULL                     -- tenant owner
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
> Catálogo **global** de tipos de plan. Cada gym personaliza precios en `gym_planes`.

**Planes base insertados:**
- Pase libre (30 días)
- Aparatos (30 días)
- Funcional (30 días)
- Zumba (30 días)
- Día (1 día)

---

### Tabla: `gym_planes`
```sql
id       INT AUTO_INCREMENT PRIMARY KEY
gym_id   INT NOT NULL
plan_id  INT NOT NULL
precio   DECIMAL(10,2) NOT NULL   -- precio que cobra ese gym específico
activo   TINYINT(1) DEFAULT 1

FK: gym_id → gyms(id) ON DELETE CASCADE
FK: plan_id → planes(id) ON DELETE CASCADE
```
> Cada gym tiene sus propios precios por plan. Relación N:N entre gyms y planes.

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

FK: gym_id      → gyms(id) ON DELETE CASCADE
FK: cliente_id  → clientes(id) ON DELETE CASCADE
FK: gym_plan_id → gym_planes(id) ON DELETE CASCADE
```
> Historial de pagos. `fecha_vencimiento` es la base para saber si una membresía está activa.

---

### Relaciones entre tablas

```
gyms
 ├── usuarios        (gym_id)
 ├── clientes        (gym_id)
 ├── gym_planes      (gym_id)
 │     └── planes    (plan_id)
 └── pagos           (gym_id, cliente_id, gym_plan_id)
```

---

## 5. ROLES Y PERMISOS

| Rol | `gym_id` | Puede hacer |
|-----|----------|-------------|
| `admin_general` | NULL | Ver/crear/suspender todos los gyms, gestionar usuarios globales |
| `admin_gym` | gym específico | CRUD clientes, planes, pagos de **su gym únicamente** |
| `empleado` | gym específico | Registrar pagos/renovaciones, buscar clientes (sin eliminar) |

---

## 6. REGLAS CRÍTICAS DE SEGURIDAD

> **⚠️ Agente: estas reglas no son opcionales. Aplicarlas en cada archivo que generes.**

1. **PDO siempre con prepared statements.**
   ```php
   // ✅ CORRECTO
   $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? AND gym_id = ?");
   $stmt->execute([$id, $gym_id]);

   // ❌ NUNCA HACER
   $pdo->query("SELECT * FROM clientes WHERE id = $id");
   ```

2. **Siempre filtrar por `gym_id` en queries de clientes, pagos y gym_planes.**
   ```php
   // ✅ Aislamiento de tenant
   WHERE cliente_id = ? AND gym_id = ?

   // ❌ Permite ver datos de otro gym
   WHERE cliente_id = ?
   ```

3. **Verificar sesión y rol al inicio de cada archivo backend/frontend.**
   ```php
   require_once '../reutilizable/session.php'; // siempre la primera línea útil
   ```

4. **Passwords con `password_hash()` y verificar con `password_verify()`.**
   Nunca MD5, nunca SHA1, nunca texto plano.

5. **Imágenes de clientes van a `img/clientes/` solo tras confirmación.**
   Temporales en `img/temporal/` hasta confirmar el registro.

---

## 7. CONVENCIONES DE CÓDIGO

### PHP
- Archivos de backend: `snake_case.php` (ej: `procesar_renovacion.php`)
- Clases (si se crean): `PascalCase`
- Variables y funciones: `$camelCase` / `camelCase()`
- Conexión PDO: siempre desde `reutilizable/funciones.php` — no crear nuevas instancias

### SQL
- Tablas: `snake_case` plural
- Columnas: `snake_case`
- Fechas: siempre `DATE` o `TIMESTAMP`, nunca `VARCHAR`

### JavaScript
- Archivos por módulo en `js/api/` para llamadas AJAX
- Componentes UI en `js/components/`
- Vanilla JS, sin jQuery, sin frameworks

### HTML/CSS
- Bootstrap 5.3.3 local (`/bootstrap-5.3.3-dist/`)
- Clases custom con prefijo `ga-` (ej: `ga-card-cliente`)
- Estilos custom solo en `css/estilos.css`

---

## 8. FLUJO DE UNA OPERACIÓN TÍPICA

### Ejemplo: Registrar un cliente nuevo

```
1. Usuario llena formulario en frontend/registro.php
2. JS valida campos en el cliente (js/api/clientes.js)
3. POST → backend/procesar.php
   - Verifica sesión y gym_id del usuario logueado
   - Valida y sanitiza inputs
   - Guarda imagen en img/temporal/ (nombre: tmp_{hash}.ext)
   - Guarda datos temporales en $_SESSION['registro_pendiente']
   - Redirige a frontend/confirmar_registro.php (preview)
4. Usuario confirma → POST → backend/confirmar_registro.php
   - Mueve imagen de temporal/ a img/clientes/ (nombre: cliente_{hash}.ext)
   - INSERT en clientes (con gym_id del usuario logueado)
   - Limpia $_SESSION['registro_pendiente']
5. Redirige a frontend/clientes.php con mensaje de éxito
```

### Ejemplo: Renovar membresía

```
1. frontend/renovacion.php → busca cliente por DNI o nombre
2. POST → backend/procesar_renovacion.php
   - Verifica que cliente pertenece al gym_id de la sesión
   - Calcula fecha_vencimiento = hoy + duracion_dias del plan
   - Guarda datos en $_SESSION['renovacion_pendiente']
3. Preview → backend/confirmar_renovacion.php
   - INSERT en pagos
   - Limpia sesión temporal
```

---

## 9. PENDIENTES Y DEUDA TÉCNICA CONOCIDA

- [ ] `js/api/autch.js` tiene un typo — debería ser `auth.js`
- [ ] No existe sistema de login implementado todavía (o está en progreso)
- [ ] Falta tabla `sesiones` o manejo de expiración de sesión
- [ ] Los patches SQL sugieren que el multi-gym fue agregado después — revisar consistencia
- [ ] No hay validación de tamaño/tipo de imagen en el backend todavía
- [ ] Falta sistema de roles en las vistas (todo el mundo ve todo por ahora)

---

## 10. COMANDOS ÚTILES (DESARROLLO LOCAL)

```bash
# Iniciar XAMPP (Windows)
C:\xampp\xampp-control.exe

# Importar BD desde cero
mysql -u root -p < database/registrogym.sql

# Aplicar patches en orden
mysql -u root -p sistema_gimnasios < database/patches/2026-03-12_multigym.sql
mysql -u root -p sistema_gimnasios < database/patches/2026-03-12_security_renovacion.sql

# URL local
http://localhost/gym/
```

---

## 11. LO QUE NO DEBE HACER UN AGENTE EN ESTE PROYECTO

- ❌ No sugerir Laravel, Symfony, ni Composer (PHP puro)
- ❌ No usar jQuery (JS Vanilla únicamente)
- ❌ No cambiar la carpeta de Bootstrap ni usar CDN externo
- ❌ No crear nuevas conexiones PDO fuera de `reutilizable/funciones.php`
- ❌ No escribir queries con variables interpoladas directamente
- ❌ No omitir el filtro `gym_id` en ninguna query de datos operativos
- ❌ No guardar passwords en texto plano ni con MD5/SHA1

---

## 12. ARCHIVO: `reutilizable/funciones.php`

Este es el **núcleo del backend**. Todos los archivos PHP deben hacer `require_once` de este archivo.
Contiene la conexión PDO, verificación de sesión, y funciones utilitarias globales.

### Funciones disponibles

---

#### `conectar_db() → PDO`
Devuelve una instancia PDO lista para usar.

```php
$pdo = conectar_db();
```

> ⚠️ **PROBLEMA CONOCIDO — ACCIÓN REQUERIDA:**
> La función actualmente conecta a la base de datos `registrogym` (nombre viejo),
> pero el schema actual usa `sistema_gimnasios`.
> **Antes de continuar el desarrollo, corregir esta línea en `funciones.php`:**
> ```php
> // ❌ Actual (incorrecto)
> $db = 'registrogym';
>
> // ✅ Correcto
> $db = 'sistema_gimnasios';
> ```
> Además, las credenciales (`root` sin contraseña) son válidas solo para XAMPP local.
> En producción deben venir de variables de entorno o un archivo `.env` fuera del webroot.

---

#### `verificarSesion() → void`
Verifica que exista `$_SESSION['usuario']`. Si no, redirige a `frontend/index.php` y hace `exit`.

```php
require_once '../reutilizable/session.php'; // siempre primero
verificarSesion();                          // luego esto
```

> ⚠️ **LIMITACIÓN ACTUAL:** Solo verifica que el usuario esté logueado, **no verifica el rol**.
> Toda lógica de control por rol (`admin_general`, `admin_gym`, `empleado`) debe agregarse
> manualmente después de llamar a esta función:
> ```php
> verificarSesion();
> if ($_SESSION['rol'] !== 'admin_gym') {
>     header('Location: ../frontend/inicio.php');
>     exit;
> }
> ```

---

#### `verificarLogueo() → void`
Muestra y limpia el mensaje de error de login almacenado en `$_SESSION['error_login']`.
Usar solo en `frontend/index.php` (pantalla de login).

```php
verificarLogueo(); // dentro del HTML del formulario de login
```

---

#### `calcularNuevaFechaVencimiento(string $fecha_actual, int $dias) → string`
Calcula la nueva fecha de vencimiento de una membresía.

**Lógica:**
- Si el cliente tiene membresía vigente (no vencida) → suma los días **desde la fecha actual de vencimiento** (no pierde días pagados).
- Si está vencido o sin membresía → suma los días **desde hoy**.

```php
$nueva_fecha = calcularNuevaFechaVencimiento('2026-04-01', 30);
// → '2026-05-01' (si hoy es antes del 2026-04-01)

$nueva_fecha = calcularNuevaFechaVencimiento('2026-01-01', 30);
// → fecha de hoy + 30 días (porque enero ya venció)
```

> ✅ Esta función está correcta. Usarla siempre para renovaciones, nunca calcular fechas inline.

---

#### `obtenerPlanId(PDO $pdo, string $nombrePlan) → int`
Busca el `id` de un plan por nombre (case-insensitive). Lanza `Exception` si no existe.

```php
try {
    $id_plan = obtenerPlanId($pdo, 'Aparatos'); // → 2
} catch (Exception $e) {
    // Manejar plan no encontrado
}
```

> ⚠️ **PROBLEMA CONOCIDO:** Esta función busca la columna `id_plan` y `nombre_plan`,
> pero el schema real (`registrogym.sql`) define las columnas como `id` y `nombre`.
> **Corregir la query dentro de la función:**
> ```php
> // ❌ Actual (no coincide con el schema)
> SELECT id_plan FROM planes WHERE LOWER(nombre_plan) = LOWER(:plan)
>
> // ✅ Correcto (según registrogym.sql)
> SELECT id FROM planes WHERE LOWER(nombre) = LOWER(:plan)
> ```

---

#### `notificar_admin(string $usuario, string $ip) → void`
Envía un email (y SMS vía email-to-SMS) al administrador cuando se detecta un acceso bloqueado.

```php
notificar_admin('usuario_sospechoso', $_SERVER['REMOTE_ADDR']);
```

> ℹ️ Usa `@mail()` (suprime errores). Solo funciona si el servidor tiene `sendmail` configurado.
> En XAMPP local no enviará nada — es funcional solo en producción.
> Las variables `$admin_email` y `$admin_sms` están hardcodeadas en el archivo;
> moverlas a constantes o `.env` antes de producción.

---

### Deuda técnica detectada en `funciones.php`

| # | Problema | Severidad | Acción |
|---|---------|-----------|--------|
| 1 | `$db = 'registrogym'` debería ser `sistema_gimnasios` | 🔴 Alta | Corregir antes de cualquier prueba |
| 2 | `obtenerPlanId` usa `id_plan` / `nombre_plan` que no existen en el schema | 🔴 Alta | Cambiar a `id` / `nombre` |
| 3 | `verificarSesion()` no valida el rol del usuario | 🟡 Media | Agregar validación de rol en cada archivo que lo requiera |
| 4 | Credenciales de BD hardcodeadas en el código | 🟡 Media | Mover a `.env` o `config.php` fuera del webroot para producción |
| 5 | `$admin_email` y `$admin_sms` hardcodeados | 🟢 Baja | Mover a configuración antes de producción |