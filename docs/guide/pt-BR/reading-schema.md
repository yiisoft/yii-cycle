# Lendo o esquema do banco de dados

O Cycle ORM depende do esquema do banco de dados - objeto, que implementa a interface `\Cycle\ORM\SchemaInterface`.

Como um esquema é construído a partir de um array de uma determinada estrutura, podemos armazená-lo em cache ou em um arquivo de texto.

Você pode exibir o esquema usado atualmente executando o comando `cycle:schema`.

No `yii-cycle` o esquema do pacote pode ser construído a partir de múltiplas fontes representadas por múltiplos provedores implementando
`Cycle\Schema\Provider\SchemaProviderInterface`.

Para usar vários provedores de esquema por sua vez, provedor `Cycle\Schema\Provider\Support\SchemaProviderPipeline`
é usado para agrupar. Você pode configurar este provedor na seção `schema-providers` de um arquivo `config/params.php`.
Organize os provedores de esquema em tal ordem que os provedores de cache fiquem no topo da lista,
e provedores de esquema de origem no final.

## Esquema baseado em atributos de entidade

Por padrão, o esquema é criado com base nos atributos que estão nas entidades do seu projeto.

Ao construir um esquema, os geradores são executados sequencialmente. A sequência é determinada em uma instância de
`SchemaConveyorInterface`. Você pode inserir seus próprios geradores neste transportador definindo-os na
opção `entity-paths` do arquivo `config/params.php`.

Para obter um esquema do transportador `FromConveyorSchemaProvider` é usado.

O processo de construção de esquema a partir de atributos é relativamente pesado em termos de desempenho. Portanto, no caso de
usar atributos, é uma boa ideia usar o cache de esquema.

## Cache de esquema

A leitura e gravação de um esquema de e para o cache acontece em `Cycle\Schema\Provider\SimpleCacheSchemaProvider`.

Coloque-o no início da lista de provedores para tornar o processo de obtenção de um esquema significativamente mais rápido.

## Esquema baseado em arquivo

Se quiser evitar atributos, você pode descrever um esquema em um arquivo PHP.
Use `Cycle\Schema\Provider\FromFilesSchemaProvider` para carregar um esquema:

```php
# config/common.php
use Cycle\Schema\Provider\FromFilesSchemaProvider;

return [
    // ...
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            FromFilesSchemaProvider::class => FromFilesSchemaProvider::config(fiels: ['@runtime/schema.php']),
        ],
    ]
];
```

```php
# runtime/schema.php
use Cycle\ORM\Schema;
return [
   'user' => [
        Schema::MAPPER      => \Cycle\ORM\Mapper\Mapper::class,
        Schema::ENTITY      => \App\Entity\User::class,
        Schema::DATABASE    => 'default',
        Schema::TABLE       => 'users',
        Schema::PRIMARY_KEY => 'id',
        Schema::COLUMNS     => [
           'id'   => 'id',
           'name' => 'name'
        ],
        Schema::TYPECAST    => [
           'id' => 'int'
        ],
        Schema::RELATIONS   => []
    ]
];
```

Observe que:

1. `FromFilesSchemaProvider` carrega um esquema de arquivos PHP via `include`. Isso requer precauções de segurança.
    Certifique-se de armazenar o arquivo de esquema em um caminho seguro e restrito aos usuários.
2. Você pode especificar vários arquivos de esquema, que serão mesclados em um esquema.
Uma exceção será lançada em caso de colisão de funções.

3. Graças ao cache interno, carregar o esquema de um arquivo PHP é tão rápido que você pode pular um cache externo.
Mas no caso de carregar vários arquivos, pode levar mais tempo para mesclá-los.
4. Você não pode gerar migrações com base no esquema de arquivo PHP. [Veja a edição #25](https://github.com/yiisoft/yii-cycle/issues/25)
5. O provedor apenas lê o esquema. Ele não pode atualizar o arquivo após a migração ser aplicada, como faz `SimpleCacheSchemaProvider`.

## Construindo esquema de banco de dados de diferentes provedores

Para mesclar partes do esquema obtidas de diferentes provedores, use `Cycle\Schema\Provider\MergeSchemaProvider`.

```php
# runtime/schema.php
return [
    // ...
    'yiisoft/yii-cycle' => [
        // ...
        'schema-providers' => [
            \Cycle\Schema\Provider\MergeSchemaProvider::class => [
                // You can specify the provider class as the key and the configuration as the value.
                // To generate a configuration array, you can use the static method `config()` of the
                // provider class. In this case, autocomplete will be available.
                \Cycle\Schema\Provider\FromFilesSchemaProvider::class => ['files' => ['@src/schema.php']],
                // If you need to use multiple identically named schema providers,
                // the provider and its configuration can be passed as an array of two elements.
                [\Cycle\Schema\Provider\SimpleCacheSchemaProvider::class, ['key' => 'cycle-schema']],
                // When defining the dependency as a string, make sure the container provides
                // the already configured provider.
                \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider::class,
            ]
        ],
    ]
];
```

## Mudando de atributos para arquivo

### Comando do console

Para exportar o esquema como arquivo PHP, o comando `cycle:schema:php` pode ser usado.
Especifique um nome de arquivo como argumento e o esquema será gravado nele:

```shell
cycle:schema:php @runtime/schema.php
```

O alias `@runtime` é substituído automaticamente. O esquema será exportado para o arquivo `schema.php`.

Certifique-se de que o esquema exportado esteja correto e passe a usá-lo por meio de `FromFilesSchemaProvider`.

Você pode combinar as duas maneiras para descrever um esquema. Durante o desenvolvimento do projeto é útil usar anotações. Você pode gerar
migrações baseadas neles. Para uso em produção, o esquema pode ser movido para um arquivo.

### Provedor `PhpFileSchemaProvider`

Ao contrário de `FromFilesSchemaProvider`, o `Cycle\Schema\Provider\PhpFileSchemaProvider` funciona com apenas um arquivo. Mas,
`PhpFileSchemaProvider` pode não apenas ler o esquema, mas também salvá-lo.

No modo de leitura e gravação de um arquivo de esquema, o provedor `PhpFileSchemaProvider` funciona de forma semelhante ao cache, com
a única diferença é que o resultado salvo (arquivo de esquema) pode ser salvo na base de código.
