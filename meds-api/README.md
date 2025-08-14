# API de Control de Medicamentos (PHP + SQLite)

API REST mínima en PHP sin dependencias externas. Usa SQLite como almacenamiento embebido y el servidor embebido de PHP.

## Ejecutar

1. Requisitos: PHP 8.1+
2. Arrancar el servidor:

```bash
php -S 0.0.0.0:8000 -t public /workspace/meds-api/public/index.php
```

La base de datos se crea automáticamente en `data/database.sqlite` al primer arranque.

## Endpoints

- `GET /medications` — Lista medicamentos
- `POST /medications` — Crea medicamento
- `GET /medications/{id}` — Detalle
- `PUT /medications/{id}` — Actualiza
- `DELETE /medications/{id}` — Elimina
- `POST /medications/{id}/adjust_stock` — Ajusta stock con `{ "delta": +/-N }`

Cabeceras: `Content-Type: application/json`

## Ejemplos

Crear:

```bash
curl -X POST http://localhost:8000/medications \
  -H 'Content-Type: application/json' \
  -d '{"name":"Paracetamol","dosage":"500 mg","stock":100,"notes":"Tomar con agua"}'
```

Listar:

```bash
curl http://localhost:8000/medications
```

Actualizar:

```bash
curl -X PUT http://localhost:8000/medications/1 \
  -H 'Content-Type: application/json' \
  -d '{"stock":120}'
```

Ajustar stock (-5):

```bash
curl -X POST http://localhost:8000/medications/1/adjust_stock \
  -H 'Content-Type: application/json' \
  -d '{"delta":-5}'
```

Eliminar:

```bash
curl -X DELETE http://localhost:8000/medications/1
```