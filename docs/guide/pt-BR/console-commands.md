# Comandos do console

## Comum

- `cycle/schema` - Lista informações sobre o esquema usado
- `cycle/schema/php [arquivo]` - Exporta o esquema atual como arquivo PHP
- `cycle/schema/clear` - Limpa o cache do esquema atual

## Migrações

- `migrate/list` - Mostra a lista de migrações
- `migrate/create <name>` - Crie o arquivo `<name>` de migração vazia
- `migrate/generate` - Gera arquivo de migração com base na diferença entre o esquema baseado em notação de entidade e a estrutura do banco de dados
- `migrate/up` - Aplicar todas as migrações ainda não aplicadas
- `migrate/down` - Reverter a última migração
