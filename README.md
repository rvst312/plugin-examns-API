# Exercises & Exams API

Guía sencilla (no técnica) para instalar y usar el plugin en una web WordPress.

## Qué hace este plugin
- Muestra un buscador de ejercicios y exámenes de Selectividad con filtros por asignatura, año, convocatoria y temáticas.
- Permite abrir cada ejercicio/examen y su solución en páginas dedicadas.
- Añade mejoras de SEO básicas para las páginas de listado y detalle.

## Requisitos
- WordPress 5.8 o superior.
- PHP 7.4 o superior.
- Enlaces permanentes activos (Ajustes > Enlaces permanentes).

## Instalación rápida
1. En tu WordPress, ve a `Plugins > Añadir nuevo > Subir plugin`.
2. Sube el ZIP del plugin o la carpeta completa `plugin-examnss` comprimida en ZIP.
3. Pulsa `Instalar` y luego `Activar`.
4. (Por si acaso) Ve a `Ajustes > Enlaces permanentes` y pulsa `Guardar cambios` para refrescar las reglas.

## Configuración básica
Este plugin usa «shortcodes» que se añaden dentro de páginas. Debes crear las páginas con estos slugs (URL) y pegar el shortcode indicado:

1) Páginas principales del buscador
- Página `exercicis-selectivitat` → contenido:
  - `[mostrar_datos_api tipus_cerca="pregunta"]`
- Página `examens-de-selectivitat` → contenido:
  - `[mostrar_datos_api tipus_cerca="examen"]`

2) Páginas de detalle (ver ejercicio/examen y su solución)
- Página `exercici` → contenido:
  - `[item_view tipus="pregunta"]`
- Página `solucio` → contenido:
  - `[item_view tipus="pregunta"]`
- Página `examenes` → contenido:
  - `[item_view tipus="examen"]`

3) Páginas de listados SEO por asignatura/año
- Para ejercicios:
  - Página `assignatura` → `[listing_view tipus_cerca="pregunta"]`
  - Página `any` → `[listing_view tipus_cerca="pregunta"]`
- Para exámenes:
  - Página `asignatura` → `[listing_view tipus_cerca="examen"]`
  - Página `year` → `[listing_view tipus_cerca="examen"]`

Importante:
- Los slugs (las URLs de las páginas) deben coincidir exactamente con los nombres anteriores.
- Tras crear las páginas, si alguna URL da 404, vuelve a `Ajustes > Enlaces permanentes` y pulsa `Guardar cambios`.

## Archivo de configuración de asignaturas
Para que los filtros muestren asignaturas y temáticas, el plugin lee un archivo JSON de configuración.

1. Crea la carpeta `wp-content/uploads/examens/config/` en tu servidor.
2. Sube dentro un archivo llamado `configuracio_assignatures.json`.
3. Ejemplo mínimo de contenido:

```
[
  {
    "tipus_prova": "Selectivitat",
    "comunitats": [
      {
        "comunitat": "Catalunya",
        "assignatures": [
          {
            "assignatura": "Química",
            "tematiques": ["Taula periòdica", "Enllaç químic"]
          }
        ]
      }
    ]
  }
]
```

Si el archivo no existe o está vacío, los filtros mostrarán opciones por defecto o aparecerá un mensaje de error.


## Desactivar o desinstalar
- Para desactivar: `Plugins > Desactivar`.
- Para desinstalar: `Plugins > Borrar` (se eliminarán las reglas, pero tus páginas y el archivo JSON seguirán en tu sitio si no los borras manualmente).

## Configuración de la API

El plugin utiliza una configuración centralizada para la API que permite cambiar fácilmente la URL base y endpoints sin modificar cada archivo individualmente.

### Configuración por defecto

El plugin viene preconfigurado con los siguientes valores:

- **URL Base**: `https://formaciomiro-cercador-api-ne-prd-dbebg8bnemhte3f9.northeurope-01.azurewebsites.net`
- **Endpoint de búsqueda**: `/cerca`
- **Endpoint de detalles**: `/detalls`
- **Timeout**: `30` segundos
- **Tamaño de página**: `12` elementos

### Configuración JSON remota

El plugin utiliza la función `load_remote_json_config()` para cargar la configuración de asignaturas desde una URL remota. Esta función incluye:

- **Caché automático**: Los datos se almacenan en caché durante 1 hora para mejorar el rendimiento
- **Gestión de errores**: Manejo robusto de errores de red y JSON inválido
- **Validación de datos**: Verificación de que los datos recibidos son válidos

La URL de configuración por defecto es:
```
https://formaciomiro-cercador-api-ne-prd-dbebg8bnemhte3f9.northeurope-01.azurewebsites.net/configuracio_assignatures.json
```

### Funciones auxiliares disponibles

El plugin proporciona las siguientes funciones auxiliares:

#### `load_remote_json_config()`
Carga la configuración JSON remota con las siguientes características:
- **Caché inteligente**: Utiliza `get_transient()` y `set_transient()` para almacenar datos durante 1 hora
- **Gestión de errores HTTP**: Maneja errores de conexión y códigos de respuesta HTTP
- **Validación JSON**: Verifica que los datos recibidos sean JSON válido
- **Fallback de errores**: Retorna mensajes de error descriptivos en caso de fallo

#### `send_data_to_api($data, $base_url, $endpoint)`
Envía datos a la API y maneja la respuesta:
- Realiza peticiones POST con datos JSON
- Incluye headers de autenticación y content-type
- Maneja errores de red y respuestas HTTP
- Decodifica automáticamente las respuestas JSON

#### `get_exams_data()`
Función específica para obtener datos de exámenes con parámetros de filtrado:
- Acepta múltiples parámetros de filtrado (asignatura, año, comunidad, etc.)
- Utiliza `send_data_to_api()` internamente
- Formatea automáticamente los datos para la API

### Personalización de la configuración

Para cambiar la URL de configuración JSON, puedes modificar la función `load_remote_json_config()` en el archivo `utils/helpers.php`:

```php
function load_remote_json_config() {
    $config_url = 'https://tu-nueva-url.com/configuracion.json';
    // ... resto de la función
}
```

### Migración desde versiones anteriores

Si actualizas desde una versión anterior que utilizaba archivos JSON locales, el plugin ahora carga automáticamente la configuración desde una URL remota. No necesitas hacer cambios adicionales en tu instalación.

## Changelog

### Versión 2.0.0 (Actual)

#### Nuevas características:
- **Configuración JSON remota**: Implementación de `load_remote_json_config()` para cargar configuración desde URL remota
- **Sistema de caché mejorado**: Caché automático de 1 hora para mejorar el rendimiento
- **Gestión de errores robusta**: Manejo completo de errores HTTP y JSON inválido
- **Código más limpio**: Refactorización de variables con nombres más descriptivos en inglés

#### Mejoras técnicas:
- Centralización de funciones auxiliares en `utils/helpers.php`
- Eliminación de dependencias de archivos JSON locales
- Mejor separación de responsabilidades entre componentes
- Documentación actualizada y más completa

#### Archivos modificados:
- `utils/helpers.php`: Nueva función `load_remote_json_config()`
- `views/button-seo.php`: Actualizado para usar la nueva función helper
- `views/filters.php`: Actualizado para usar la nueva función helper
- `examns-api.php`: Inclusión del archivo de helpers

### Migración automática

El plugin mantiene compatibilidad total con instalaciones existentes. La migración de archivos JSON locales a configuración remota es automática y transparente.