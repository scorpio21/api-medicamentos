# API Control de Medicamentos

API REST desarrollada en PHP para el control y gestión de medicamentos en farmacias, hospitales o centros de salud.

## Características

- ✅ CRUD completo de medicamentos
- ✅ Búsqueda por nombre
- ✅ Validación de datos
- ✅ Control de stock
- ✅ Respuestas JSON estructuradas
- ✅ Manejo de errores
- ✅ Arquitectura MVC
- ✅ Base de datos MySQL/MariaDB

## Requisitos

- PHP >= 8.0
- MySQL/MariaDB
- Composer
- Servidor web (Apache/Nginx) o PHP built-in server

## Instalación

1. **Clonar el repositorio**
```bash
git clone <tu-repo>
cd medicamentos-api
```

2. **Instalar dependencias**

# API para Control de Medicamentos

Una API RESTful completa desarrollada en PHP para la gestión y control de inventario de medicamentos.

## 🚀 Características

- **CRUD completo** para medicamentos
- **Búsqueda avanzada** por nombre, ingrediente activo, fabricante
- **Control de stock** con alertas de inventario bajo
- **Control de fechas de vencimiento** con alertas
- **Validación de datos** robusta
- **Manejo de errores** consistente
- **Documentación completa** de la API
- **Base de datos MySQL** optimizada
- **Arquitectura MVC** limpia y mantenible

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer
- Extensión PDO para PHP

## 🛠️ Instalación

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd medication-control-api
```

### 2. Instalar dependencias

```bash
composer install
```
3. **Configurar base de datos**
```bash
# Crear base de datos
mysql -u root -p
CREATE DATABASE medicamentos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
# Editar .env con tus credenciales de BD
```

5. **Iniciar servidor**
```bash
# Opción 1: Servidor PHP integrado
php -S localhost:8000 -t public

# Opción 2: Apache/Nginx
# Configurar DocumentRoot hacia la carpeta public/
```

## Estructura del Proyecto

```
medicamentos-api/
├── public/
│   └── index.php          # Punto de entrada
├── src/
│   ├── Controllers/
│   │   └── MedicamentoController.php
│   ├── Models/
│   │   └── Medicamento.php
│   ├── Database.php       # Conexión BD
│   └── Router.php         # Enrutador
├── config.php             # Configuración
├── .env.example           # Variables de entorno
├── .htaccess             # Configuración Apache
├── composer.json         # Dependencias
└── README.md             # Documentación
```

## Endpoints de la API

### Base URL
```
http://localhost:8000/api
```

### 1. Información de la API
```http
GET /api
```

### 2. Listar todos los medicamentos
```http
GET /api/medicamentos
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nombre": "Paracetamol",
            "descripcion": "Analgésico y antipirético",
            "presentacion": "Tabletas 500mg",
            "dosis_recomendada": "1 tableta cada 8 horas",
            "stock": 100,
            "created_at": "2024-01-15 10:30:00",
            "updated_at": "2024-01-15 10:30:00"
        }
    ],
    "count": 1
}
```

### 3. Obtener medicamento por ID
```http
GET /api/medicamentos/{id}
```

### 4. Crear nuevo medicamento
```http
POST /api/medicamentos
Content-Type: application/json

{
    "nombre": "Ibuprofeno",
    "descripcion": "Antiinflamatorio no esteroideo",
    "presentacion": "Tabletas 400mg",
    "dosis_recomendada": "1 tableta cada 12 horas",
    "stock": 75
}
```

**Campos:**
- `nombre` (requerido): Nombre único del medicamento
- `descripcion` (opcional): Descripción del medicamento
- `presentacion` (requerido): Forma farmacéutica y concentración
- `dosis_recomendada` (requerido): Dosis sugerida
- `stock` (opcional): Cantidad en inventario (default: 0)

### 5. Actualizar medicamento
```http
PUT /api/medicamentos/{id}
Content-Type: application/json

{
    "stock": 150,
    "dosis_recomendada": "1 tableta cada 6 horas"
}
```

### 6. Eliminar medicamento
```http
DELETE /api/medicamentos/{id}
```

### 7. Buscar medicamentos
```http
GET /api/medicamentos/search?nombre=paracetamol
```

## Ejemplos de Uso

### Crear un medicamento
```bash
curl -X POST http://localhost:8000/api/medicamentos \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Amoxicilina",
    "descripcion": "Antibiótico de amplio espectro",
    "presentacion": "Cápsulas 500mg",
    "dosis_recomendada": "1 cápsula cada 8 horas",
    "stock": 200
  }'
```

### Buscar medicamentos
```bash
curl "http://localhost:8000/api/medicamentos/search?nombre=amox"
```

### Actualizar stock
```bash
curl -X PUT http://localhost:8000/api/medicamentos/1 \
  -H "Content-Type: application/json" \
  -d '{"stock": 50}'
```

## Códigos de Estado HTTP

- `200` - OK
- `201` - Creado exitosamente
- `400` - Datos inválidos
- `404` - Recurso no encontrado
- `409` - Conflicto (nombre duplicado)
- `500` - Error interno del servidor

## Estructura de Respuestas

### Respuesta exitosa
```json
{
    "success": true,
    "data": {...},
    "message": "Operación exitosa"
}
```

### Respuesta de error
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": ["Lista de errores específicos"]
}
```

## Validaciones

- **Nombre**: Requerido, único, 2-255 caracteres
- **Presentación**: Requerida
- **Dosis recomendada**: Requerida
- **Stock**: Número entero >= 0

## Base de Datos

### Tabla `medicamentos`
```sql
CREATE TABLE medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    descripcion TEXT,
    presentacion VARCHAR(255) NOT NULL,
    dosis_recomendada VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Configuración de Apache

Si usas Apache, asegúrate de que tu VirtualHost apunte a la carpeta `public/`:

```apache
<VirtualHost *:80>
    DocumentRoot /ruta/a/medicamentos-api/public
    ServerName medicamentos-api.local
    
    <Directory /ruta/a/medicamentos-api/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Próximas Mejoras

- [ ] Autenticación JWT
- [ ] Paginación de resultados
- [ ] Filtros avanzados
- [ ] Historial de movimientos
- [ ] Alertas de stock bajo
- [ ] API de proveedores
- [ ] Documentación Swagger/OpenAPI

## Licencia

MIT License

## Soporte

Para reportar problemas o sugerencias, crea un issue en el repositorio.

=======
### 3. Configurar variables de entorno
```bash
cp .env.example .env
```

Editar el archivo `.env` con tu configuración:
```env
DB_HOST=localhost
DB_NAME=medication_control
DB_USER=tu_usuario
DB_PASS=tu_password
DB_CHARSET=utf8mb4
API_SECRET=tu_clave_secreta_aqui
```

### 4. Crear la base de datos
```bash
mysql -u root -p < database/schema.sql
```

### 5. Iniciar el servidor
```bash
composer start
```

La API estará disponible en: `http://localhost:8000`

## 📚 Documentación de la API

### Base URL
```
http://localhost:8000/api/v1
```

### Endpoints

#### Medicamentos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/medications` | Obtener todos los medicamentos (con paginación) |
| GET | `/medications/{id}` | Obtener medicamento por ID |
| POST | `/medications` | Crear nuevo medicamento |
| PUT | `/medications/{id}` | Actualizar medicamento completo |
| PATCH | `/medications/{id}/quantity` | Actualizar solo la cantidad |
| DELETE | `/medications/{id}` | Eliminar medicamento |

#### Búsqueda y Reportes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/medications/search?q={term}` | Buscar medicamentos |
| GET | `/medications/expiring-soon?days={30}` | Medicamentos próximos a vencer |
| GET | `/medications/low-stock?threshold={10}` | Medicamentos con stock bajo |

### Parámetros de Consulta

#### Paginación
- `limit`: Número de resultados por página (default: 100)
- `offset`: Número de resultados a omitir (default: 0)

#### Búsqueda
- `q`: Término de búsqueda (requerido)

#### Reportes
- `days`: Días para medicamentos próximos a vencer (default: 30)
- `threshold`: Umbral para stock bajo (default: 10)

### Estructura de Datos

#### Medicamento
```json
{
  "id": 1,
  "name": "Paracetamol 500mg",
  "description": "Analgésico y antipirético",
  "active_ingredient": "Paracetamol",
  "dosage_form": "Tableta",
  "strength": "500mg",
  "manufacturer": "Genérico",
  "batch_number": "BATCH001",
  "expiry_date": "2025-12-31",
  "quantity": 150,
  "storage_conditions": "Almacenar en lugar seco y fresco",
  "created_at": "2024-01-15 10:30:00",
  "updated_at": "2024-01-15 10:30:00"
}
```

### Respuestas

#### Éxito
```json
{
  "success": true,
  "data": [...],
  "message": "Operación exitosa"
}
```

#### Error
```json
{
  "success": false,
  "error": "Mensaje de error",
  "status": 400
}
```

## 🔧 Ejemplos de Uso

### Crear un medicamento
```bash
curl -X POST http://localhost:8000/api/v1/medications \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Aspirina 100mg",
    "description": "Analgésico y antiagregante plaquetario",
    "active_ingredient": "Ácido acetilsalicílico",
    "dosage_form": "Tableta",
    "strength": "100mg",
    "manufacturer": "Genérico",
    "batch_number": "BATCH011",
    "expiry_date": "2026-12-31",
    "quantity": 200,
    "storage_conditions": "Almacenar en lugar seco y fresco"
  }'
```

### Obtener medicamentos
```bash
curl http://localhost:8000/api/v1/medications?limit=10&offset=0
```

### Buscar medicamentos
```bash
curl "http://localhost:8000/api/v1/medications/search?q=paracetamol"
```

### Obtener medicamentos próximos a vencer
```bash
curl "http://localhost:8000/api/v1/medications/expiring-soon?days=60"
```

### Actualizar cantidad
```bash
curl -X PATCH http://localhost:8000/api/v1/medications/1/quantity \
  -H "Content-Type: application/json" \
  -d '{"quantity": 125}'
```

## 🗄️ Estructura de la Base de Datos

### Tabla `medications`
- `id`: Identificador único (AUTO_INCREMENT)
- `name`: Nombre del medicamento
- `description`: Descripción del medicamento
- `active_ingredient`: Ingrediente activo
- `dosage_form`: Forma de dosificación
- `strength`: Concentración
- `manufacturer`: Fabricante
- `batch_number`: Número de lote (único)
- `expiry_date`: Fecha de vencimiento
- `quantity`: Cantidad en stock
- `storage_conditions`: Condiciones de almacenamiento
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

### Vistas
- `medications_expiring_soon`: Medicamentos próximos a vencer
- `medications_low_stock`: Medicamentos con stock bajo


# API de Control de Medicamentos (PHP + Slim 4 + SQLite)

API REST simple para gestionar medicamentos: crear, listar, obtener, actualizar y eliminar.

## Requisitos (opción 1: Docker)
- Docker y Docker Compose

### Ejecutar con Docker
```bash
docker compose up --build -d
```
Disponibles en `http://localhost:8080`.

## Requisitos (opción 2: Local)
- PHP >= 8.1 con extensiones `pdo_sqlite`
- Composer

### Ejecutar en local
```bash
composer install
composer start
# Servirá en http://localhost:8080
```

## Endpoints
- GET `/` → info de la API
- GET `/medicamentos` → lista con paginación y búsqueda (`search`, `page`, `limit`)
- GET `/medicamentos/{id}` → detalle
- POST `/medicamentos` → crear
- PUT `/medicamentos/{id}` → actualizar
- DELETE `/medicamentos/{id}` → eliminar

### Ejemplos con curl
- Crear:
```bash
curl -X POST http://localhost:8080/medicamentos \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "Paracetamol 500mg",
    "description": "Analgésico/antipirético",
    "dosage": "1 tableta c/8h",
    "stock": 100,
    "expiration_date": "2026-05-01",
    "lot_number": "L-ABC-123"
  }'
```

- Listar:
```bash
curl 'http://localhost:8080/medicamentos?search=paracetamol&page=1&limit=10'
```

- Actualizar:
```bash
curl -X PUT http://localhost:8080/medicamentos/1 \
  -H 'Content-Type: application/json' \
  -d '{
    "stock": 85
  }'
```

- Eliminar:
```bash
curl -X DELETE http://localhost:8080/medicamentos/1 -i
```

## Configuración
- Ruta de la base de datos SQLite: variable de entorno `DB_PATH` (por defecto `var/database.sqlite`).

## Estructura
- `public/index.php`: arranque de Slim y rutas
- `src/Database.php`: conexión e inicialización de SQLite
- `src/Controllers/MedicationController.php`: lógica CRUD
- `src/Validation.php`: validaciones básicas
- `var/`: base de datos

# 🏥 API de Control de Medicamentos

Una API REST completa y robusta para el control de medicamentos en farmacias, desarrollada en PHP 8+ con arquitectura MVC y autenticación JWT.

## ✨ Características Principales

- 🔐 **Autenticación JWT** con control de acceso basado en roles
- 💊 **Gestión completa de medicamentos** con categorización
- 📦 **Control de inventario** con transacciones auditables
- 👥 **Sistema de usuarios** con roles diferenciados
- 🔍 **Búsqueda avanzada** y filtros
- 📄 **Paginación** para grandes volúmenes de datos
- 📝 **Logging completo** de todas las operaciones
- 🛡️ **Validación robusta** y prevención de SQL injection
- 🌐 **CORS habilitado** para integración frontend
- 📊 **Reportes de stock** bajo y próximos a vencer

## 🚀 Instalación Rápida

### Prerrequisitos
- PHP 8.0 o superior
- MySQL 5.7 o superior
- Composer
- Extensiones PHP: PDO, PDO_MySQL, OpenSSL

### 1. Clonar e instalar
```bash
git clone <repository-url>
cd medication-control-api
composer install
```

### 2. Configurar base de datos
```bash
# Crear base de datos y tablas
mysql -u root -p < database/schema.sql
```

### 3. Configurar variables de entorno
```bash
cp .env.example .env
# Editar .env con tus configuraciones de BD
```

### 4. Ejecutar en desarrollo
```bash
composer start
# La API estará disponible en http://localhost:8000
```

## 📚 Documentación Completa

Consulta la [documentación completa de la API](API_DOCUMENTATION.md) para:
- Todos los endpoints disponibles
- Ejemplos de uso
- Códigos de respuesta
- Guías de seguridad
- Despliegue en producción


## 🏗️ Arquitectura del Proyecto

```
src/

├── Controllers/          # Controladores de la API
├── Models/              # Modelos de datos
├── Repositories/        # Acceso a datos
├── Middleware/          # Middleware personalizado
├── Routes/              # Definición de rutas
└── Database/            # Conexión a base de datos

public/                  # Punto de entrada de la API
database/                # Esquemas y datos de ejemplo
```

├── Auth/           # Autenticación JWT
├── Controllers/    # Controladores de la API
├── Database/       # Conexión y gestión de BD
└── Models/         # Modelos de datos

database/           # Esquemas de BD
public/             # Punto de entrada de la API
logs/               # Archivos de log
```

## 🔑 Endpoints Principales

### Autenticación
- `POST /api/v1/auth/login` - Iniciar sesión
- `GET /api/v1/auth/profile` - Obtener perfil

### Medicamentos
- `GET /api/v1/medications` - Listar medicamentos
- `POST /api/v1/medications` - Crear medicamento
- `GET /api/v1/medications/search` - Buscar medicamentos

### Inventario
- `GET /api/v1/inventory` - Listar inventario
- `POST /api/v1/inventory/{id}/add-stock` - Agregar stock
- `GET /api/v1/inventory/low-stock` - Stock bajo

## 👥 Roles de Usuario

- **Admin**: Acceso completo
- **Pharmacist**: Gestión de medicamentos e inventario
- **Assistant**: Solo consultas

## 🧪 Testing

```bash
composer test
```


## 📝 Licencia

Este proyecto está bajo la Licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un issue o pull request.

## 📞 Soporte

Si tienes alguna pregunta o problema, por favor abre un issue en el repositorio.

## 📝 Logs

Los logs se almacenan en `logs/`:
- `database.log` - Operaciones de BD
- `api.log` - Errores de la API

## 🚀 Despliegue en Producción

1. Configurar servidor web (Apache/Nginx)
2. Configurar variables de entorno
3. Deshabilitar debug mode
4. Configurar HTTPS
5. Configurar firewall

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🆘 Soporte

- 📖 [Documentación](API_DOCUMENTATION.md)
- 🐛 [Reportar Bugs](issues)
- 💡 [Solicitar Features](issues)
- 📧 Contacto: [tu-email@ejemplo.com]

---
**Desarrollado con ❤️ para el control eficiente de medicamentos en farmacias**