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
