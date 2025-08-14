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
