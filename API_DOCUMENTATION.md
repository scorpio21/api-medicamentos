# API de Control de Medicamentos

## Descripción
API REST completa para el control de medicamentos en farmacias, incluyendo gestión de inventario, usuarios, categorías y transacciones.

## Características
- ✅ Autenticación JWT
- ✅ Control de acceso basado en roles
- ✅ Gestión completa de medicamentos
- ✅ Control de inventario con transacciones
- ✅ Categorización de medicamentos
- ✅ Gestión de usuarios
- ✅ Validación de datos
- ✅ Logging y manejo de errores
- ✅ Paginación y búsqueda
- ✅ CORS habilitado

## Requisitos
- PHP 8.0+
- MySQL 5.7+
- Composer
- Extensiones PHP: PDO, PDO_MySQL, OpenSSL

## Instalación

### 1. Clonar el proyecto
```bash
git clone <repository-url>
cd medication-control-api
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar base de datos
```bash
# Crear base de datos
mysql -u root -p < database/schema.sql

# O ejecutar manualmente el contenido de database/schema.sql
```

### 4. Configurar variables de entorno
```bash
cp .env.example .env
# Editar .env con tus configuraciones
```

### 5. Configurar servidor web
```bash
# Para desarrollo
composer start

# Para producción, configurar Apache/Nginx apuntando a public/
```

## Estructura del Proyecto
```
medication-control-api/
├── src/
│   ├── Auth/           # Autenticación JWT
│   ├── Controllers/    # Controladores de la API
│   ├── Database/       # Conexión a base de datos
│   └── Models/         # Modelos de datos
├── database/           # Esquemas de base de datos
├── logs/              # Archivos de log
├── public/            # Punto de entrada de la API
└── vendor/            # Dependencias de Composer
```

## Autenticación

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "username": "admin",
    "password": "password"
}
```

**Respuesta:**
```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "message": "Autenticación exitosa"
}
```

### Uso del Token
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

## Endpoints

### Autenticación
| Método | Endpoint | Descripción | Autenticación |
|--------|----------|-------------|---------------|
| POST | `/auth/login` | Iniciar sesión | No |
| POST | `/auth/refresh` | Renovar token | Sí |
| GET | `/auth/profile` | Obtener perfil | Sí |
| POST | `/auth/change-password` | Cambiar contraseña | Sí |

### Medicamentos
| Método | Endpoint | Descripción | Autenticación | Rol |
|--------|----------|-------------|---------------|-----|
| GET | `/medications` | Listar medicamentos | Sí | Cualquiera |
| GET | `/medications/{id}` | Obtener medicamento | Sí | Cualquiera |
| POST | `/medications` | Crear medicamento | Sí | Pharmacist+ |
| PUT | `/medications/{id}` | Actualizar medicamento | Sí | Pharmacist+ |
| DELETE | `/medications/{id}` | Eliminar medicamento | Sí | Pharmacist+ |
| GET | `/medications/search?q={query}` | Buscar medicamentos | Sí | Cualquiera |
| GET | `/medications/category/{id}` | Por categoría | Sí | Cualquiera |

### Inventario
| Método | Endpoint | Descripción | Autenticación | Rol |
|--------|----------|-------------|---------------|-----|
| GET | `/inventory` | Listar inventario | Sí | Cualquiera |
| GET | `/inventory/{id}` | Obtener inventario | Sí | Cualquiera |
| POST | `/inventory` | Crear inventario | Sí | Pharmacist+ |
| PUT | `/inventory/{id}` | Actualizar inventario | Sí | Pharmacist+ |
| DELETE | `/inventory/{id}` | Eliminar inventario | Sí | Pharmacist+ |
| POST | `/inventory/{id}/add-stock` | Agregar stock | Sí | Pharmacist+ |
| POST | `/inventory/{id}/remove-stock` | Remover stock | Sí | Pharmacist+ |
| GET | `/inventory/low-stock` | Stock bajo | Sí | Cualquiera |
| GET | `/inventory/expiring-soon` | Próximo a vencer | Sí | Cualquiera |

### Categorías
| Método | Endpoint | Descripción | Autenticación | Rol |
|--------|----------|-------------|---------------|-----|
| GET | `/categories` | Listar categorías | Sí | Cualquiera |
| GET | `/categories/{id}` | Obtener categoría | Sí | Cualquiera |
| POST | `/categories` | Crear categoría | Sí | Pharmacist+ |
| PUT | `/categories/{id}` | Actualizar categoría | Sí | Pharmacist+ |
| DELETE | `/categories/{id}` | Eliminar categoría | Sí | Pharmacist+ |

## Roles de Usuario
- **admin**: Acceso completo a todas las funcionalidades
- **pharmacist**: Puede gestionar medicamentos, inventario y categorías
- **assistant**: Solo puede consultar información

## Ejemplos de Uso

### Crear un medicamento
```http
POST /api/v1/medications
Authorization: Bearer <token>
Content-Type: application/json

{
    "name": "Paracetamol 500mg",
    "generic_name": "Acetaminofén",
    "category_id": 1,
    "active_ingredient": "Paracetamol",
    "dosage_form": "Tableta",
    "strength": "500mg",
    "manufacturer": "Genérico",
    "description": "Analgésico y antipirético",
    "requires_prescription": false
}
```

### Agregar stock al inventario
```http
POST /api/v1/inventory/1/add-stock
Authorization: Bearer <token>
Content-Type: application/json

{
    "quantity": 100,
    "reason": "Compra de proveedor"
}
```

### Buscar medicamentos
```http
GET /api/v1/medications/search?q=paracetamol
Authorization: Bearer <token>
```

### Obtener stock bajo
```http
GET /api/v1/inventory/low-stock?threshold=10
Authorization: Bearer <token>
```

## Respuestas de la API

### Formato de Respuesta Exitosa
```json
{
    "success": true,
    "data": {...},
    "message": "Operación exitosa"
}
```

### Formato de Respuesta con Error
```json
{
    "success": false,
    "error": "Descripción del error"
}
```

### Códigos de Estado HTTP
- `200` - OK
- `201` - Creado
- `400` - Bad Request
- `401` - No autorizado
- `403` - Prohibido
- `404` - No encontrado
- `405` - Método no permitido
- `500` - Error interno del servidor

## Paginación
Los endpoints que devuelven listas soportan paginación:

```http
GET /api/v1/medications?page=1&limit=20
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "data": [...],
        "pagination": {
            "page": 1,
            "limit": 20,
            "total": 150,
            "pages": 8
        }
    }
}
```

## Filtros y Búsqueda

### Medicamentos
- `search`: Búsqueda por nombre, nombre genérico o ingrediente activo
- `category_id`: Filtrar por categoría
- `page` y `limit`: Paginación

### Inventario
- `medication_id`: Filtrar por medicamento
- `threshold`: Umbral para stock bajo (por defecto 10)
- `days`: Días para medicamentos próximos a vencer (por defecto 30)

## Seguridad

### JWT
- Algoritmo: HS256
- Expiración configurable en `.env`
- Renovación automática disponible

### Validación
- Sanitización de entrada
- Validación de tipos de datos
- Prevención de SQL injection
- Control de acceso basado en roles

### Logs
- Todas las operaciones se registran
- Errores se loguean con detalles
- Transacciones de inventario se auditan

## Base de Datos

### Tablas Principales
- `users`: Usuarios del sistema
- `medication_categories`: Categorías de medicamentos
- `medications`: Información de medicamentos
- `inventory`: Control de inventario
- `inventory_transactions`: Historial de transacciones
- `prescriptions`: Prescripciones médicas
- `sales`: Ventas realizadas

### Relaciones
- Medicamentos pertenecen a categorías
- Inventario se asocia a medicamentos
- Transacciones registran cambios en inventario
- Usuarios realizan operaciones

## Desarrollo

### Ejecutar Tests
```bash
composer test
```

### Logs
Los logs se almacenan en `logs/`:
- `database.log`: Operaciones de base de datos
- `api.log`: Errores de la API

### Debug
Para desarrollo, configurar en `.env`:
```
APP_DEBUG=true
LOG_LEVEL=debug
```

## Despliegue en Producción

### Configuración del Servidor
1. Configurar Apache/Nginx apuntando a `public/`
2. Habilitar mod_rewrite (Apache)
3. Configurar variables de entorno
4. Deshabilitar debug mode

### Seguridad
1. Cambiar JWT_SECRET
2. Configurar HTTPS
3. Restringir acceso a directorios sensibles
4. Configurar firewall

### Monitoreo
1. Revisar logs regularmente
2. Monitorear uso de base de datos
3. Verificar tokens expirados
4. Backup de base de datos

## Soporte

Para reportar bugs o solicitar funcionalidades:
1. Crear un issue en el repositorio
2. Incluir detalles del error
3. Especificar versión de PHP y MySQL
4. Adjuntar logs relevantes

## Licencia
Este proyecto está bajo licencia MIT.