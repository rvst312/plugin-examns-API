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
- **Token de acceso**: `Bearer 123456789`
- **Timeout**: `30` segundos
- **Tamaño de página**: `12` elementos

### Personalización de la configuración

Para cambiar la configuración de la API, puedes modificar las constantes en el archivo `includes/config.php`:

```php
// Configuración de la API
define('EXAMENS_API_BASE_URL', 'https://tu-nueva-api.com');
define('EXAMENS_API_ENDPOINT_SEARCH', '/buscar');
define('EXAMENS_API_ENDPOINT_DETAILS', '/detalles');
define('EXAMENS_API_ACCESS_TOKEN', 'Bearer tu-nuevo-token');
define('EXAMENS_API_TIMEOUT', 45);
define('EXAMENS_API_PAGE_SIZE', 20);
```

### Funciones auxiliares disponibles

El plugin proporciona funciones auxiliares para acceder a la configuración:

- `get_examens_api_url($endpoint_type)`: Obtiene la URL completa para un endpoint específico
- `get_examens_api_config()`: Obtiene toda la configuración de la API como array

### Migración desde versiones anteriores

Si actualizas desde una versión anterior que tenía URLs hardcodeadas, no necesitas hacer cambios adicionales. El plugin mantiene compatibilidad con las funciones legacy mientras migra automáticamente a la nueva configuración centralizada.