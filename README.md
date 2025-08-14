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
