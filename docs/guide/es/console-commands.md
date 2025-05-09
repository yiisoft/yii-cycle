# Comandos de la consola

## General

- `cycle:schema` - Obtener información sobre el esquema en uso
- `cycle:schema:php [nombre_del_archivo]` - Exporta el esquema en uso a un archivo PHP
- `cycle:schema:clear` - Borrar la caché del esquema

## Migraciones

- `migrate:list` - Imprime la lista de migraciones
- `migrar:crear <nombre>` - Crear el fichero `<nombre>` con la migración vacía
- `migrate:generate` - Generar un archivo de migración basado en la diferencia entre el esquema de anotaciones de la entidad y la estructura de la base de datos.
- `migrate:up` - Aplicar todas las migraciones no aplicadas.
- `migrate:down` - Retroceder la última migración.
