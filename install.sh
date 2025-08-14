#!/bin/bash

echo "🚀 Instalando API para Control de Medicamentos..."
echo "================================================"

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP no está instalado. Por favor, instala PHP 7.4 o superior."
    exit 1
fi

# Verificar versión de PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)

if [ "$PHP_MAJOR" -lt 7 ] || ([ "$PHP_MAJOR" -eq 7 ] && [ "$PHP_MINOR" -lt 4 ]); then
    echo "❌ Error: Se requiere PHP 7.4 o superior. Versión actual: $PHP_VERSION"
    exit 1
fi

echo "✅ PHP $PHP_VERSION detectado"

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "❌ Error: Composer no está instalado. Por favor, instala Composer."
    exit 1
fi

echo "✅ Composer detectado"

# Verificar si MySQL está instalado
if ! command -v mysql &> /dev/null; then
    echo "⚠️  Advertencia: MySQL no está instalado. Por favor, instala MySQL 5.7 o superior."
    echo "   Puedes continuar con la instalación y configurar la base de datos más tarde."
fi

# Crear archivo .env si no existe
if [ ! -f .env ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
    echo "✅ Archivo .env creado. Por favor, edítalo con tu configuración de base de datos."
else
    echo "✅ Archivo .env ya existe"
fi

# Instalar dependencias
echo "📦 Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo "✅ Dependencias instaladas correctamente"
else
    echo "❌ Error al instalar dependencias"
    exit 1
fi

# Crear directorios necesarios
echo "📁 Creando directorios necesarios..."
mkdir -p logs
mkdir -p cache
chmod 755 logs cache

# Verificar permisos de escritura
if [ -w logs ] && [ -w cache ]; then
    echo "✅ Permisos de directorios configurados"
else
    echo "⚠️  Advertencia: No se pudieron configurar los permisos de escritura"
fi

# Crear archivo de configuración de Apache si no existe
if [ ! -f public/.htaccess ]; then
    echo "✅ Archivo .htaccess ya existe"
fi

echo ""
echo "🎉 ¡Instalación completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Edita el archivo .env con tu configuración de base de datos"
echo "2. Crea la base de datos ejecutando: mysql -u root -p < database/schema.sql"
echo "3. Inicia el servidor: composer start"
echo "4. La API estará disponible en: http://localhost:8000"
echo ""
echo "📚 Documentación disponible en: README.md"
echo "🧪 Ejemplos de uso en: examples/api-examples.http"
echo ""
echo "¡Gracias por usar la API para Control de Medicamentos! 🏥💊"