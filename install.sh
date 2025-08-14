#!/bin/bash


echo "🚀 Instalando API para Control de Medicamentos..."

# Script de instalación para la API de Control de Medicamentos
# Autor: Tu Nombre
# Fecha: $(date)

echo "🏥 Instalando API de Control de Medicamentos..."

echo "================================================"

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then

    echo "❌ Error: PHP no está instalado. Por favor, instala PHP 7.4 o superior."

    echo "❌ PHP no está instalado. Por favor instala PHP 8.0+ primero."

    exit 1
fi

# Verificar versión de PHP
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)


if [ "$PHP_MAJOR" -lt 7 ] || ([ "$PHP_MAJOR" -eq 7 ] && [ "$PHP_MINOR" -lt 4 ]); then
    echo "❌ Error: Se requiere PHP 7.4 o superior. Versión actual: $PHP_VERSION"

if [ "$PHP_MAJOR" -lt 8 ]; then
    echo "❌ Se requiere PHP 8.0 o superior. Versión actual: $PHP_VERSION"

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


    echo "❌ Composer no está instalado. Instalando..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    echo "✅ Composer instalado"
else
    echo "✅ Composer detectado"
fi

# Verificar si MySQL está instalado
if ! command -v mysql &> /dev/null; then
    echo "⚠️  MySQL no está instalado. Por favor instala MySQL 5.7+ antes de continuar."
    echo "   Puedes continuar con la instalación, pero necesitarás MySQL para usar la API."
    read -p "¿Deseas continuar? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo "✅ MySQL detectado"
fi

# Crear directorio de logs
echo "📁 Creando directorio de logs..."
mkdir -p logs
chmod 755 logs


# Instalar dependencias
echo "📦 Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

if [ $? -eq 0 ]; then
    echo "✅ Dependencias instaladas correctamente"
else

if [ $? -ne 0 ]; then

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

echo "✅ Dependencias instaladas"

# Configurar archivo .env
if [ ! -f .env ]; then
    echo "⚙️  Configurando archivo .env..."
    cp .env.example .env
    
    echo ""
    echo "🔧 Configuración de la base de datos:"
    read -p "Host de MySQL (default: localhost): " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "Nombre de la base de datos (default: medication_control): " DB_NAME
    DB_NAME=${DB_NAME:-medication_control}
    
    read -p "Usuario de MySQL (default: root): " DB_USER
    DB_USER=${DB_USER:-root}
    
    read -s -p "Contraseña de MySQL: " DB_PASS
    echo ""
    
    read -p "Clave secreta JWT (default: $(openssl rand -hex 32)): " JWT_SECRET
    JWT_SECRET=${JWT_SECRET:-$(openssl rand -hex 32)}
    
    # Actualizar .env
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/DB_NAME=.*/DB_NAME=$DB_NAME/" .env
    sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" .env
    sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
    sed -i "s/JWT_SECRET=.*/JWT_SECRET=$JWT_SECRET/" .env
    
    echo "✅ Archivo .env configurado"
else
    echo "✅ Archivo .env ya existe"
fi

# Crear base de datos si MySQL está disponible
if command -v mysql &> /dev/null; then
    echo "🗄️  Configurando base de datos..."
    
    # Obtener credenciales del .env
    source .env
    
    # Crear base de datos
    mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✅ Base de datos '$DB_NAME' creada/verificada"
        
        # Importar esquema
        echo "📋 Importando esquema de base de datos..."
        mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql
        
        if [ $? -eq 0 ]; then
            echo "✅ Esquema importado correctamente"
        else
            echo "⚠️  Error al importar esquema. Puedes importarlo manualmente ejecutando:"
            echo "   mysql -u$DB_USER -p$DB_NAME < database/schema.sql"
        fi
    else
        echo "⚠️  Error al crear base de datos. Verifica las credenciales en .env"
        echo "   Puedes crear la base de datos manualmente y luego ejecutar:"
        echo "   mysql -u$DB_USER -p$DB_NAME < database/schema.sql"
    fi
fi

# Configurar permisos
echo "🔐 Configurando permisos..."
chmod 755 public/
chmod 644 public/.htaccess
chmod 644 public/index.php

# Crear script de inicio
echo "🚀 Creando script de inicio..."
cat > start.sh << 'EOF'
#!/bin/bash
echo "🏥 Iniciando API de Control de Medicamentos..."
echo "API disponible en: http://localhost:8000"
echo "Presiona Ctrl+C para detener"
composer start
EOF

chmod +x start.sh

# Verificar instalación
echo ""
echo "🔍 Verificando instalación..."

# Verificar archivos críticos
if [ -f "public/index.php" ] && [ -f "src/Database/Database.php" ] && [ -f ".env" ]; then
    echo "✅ Archivos críticos verificados"
else
    echo "❌ Faltan archivos críticos"
    exit 1
fi

# Verificar dependencias
if [ -d "vendor" ]; then
    echo "✅ Dependencias verificadas"
else
    echo "❌ Dependencias no encontradas"
    exit 1
fi

echo ""
echo "🎉 ¡Instalación completada exitosamente!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Verifica la configuración en .env"
echo "2. Si no se creó la BD, ejecuta: mysql -u[usuario] -p[nombre_bd] < database/schema.sql"
echo "3. Inicia la API: ./start.sh"
echo "4. Accede a la documentación: API_DOCUMENTATION.md"
echo ""
echo "🔑 Credenciales por defecto:"
echo "   Usuario: admin"
echo "   Contraseña: password"
echo ""
echo "📚 Documentación completa: API_DOCUMENTATION.md"
echo ""
echo "¡Gracias por usar la API de Control de Medicamentos! 🏥💊"

