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
