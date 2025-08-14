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
=======
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

