<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518204128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Product';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('products');

        $table->addColumn('id',                   'bigint',   ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $table->addColumn('nome',                 'string',   ['length' => 255, 'notnull' => true]);
        $table->addColumn('codigo_barra',         'string',   ['length' => 255, 'notnull' => false]);
        $table->addColumn('unidade',              'string',   ['length' => 18,  'notnull' => true]);
        $table->addColumn('preco_compra',         'decimal',   ['length' => 18, 'precision' => 18, 'scale' => 4, 'notnull' => true, 'default' => 0]);
        $table->addColumn('total_imposto',        'decimal',   ['length' => 18, 'precision' => 18, 'scale' => 4, 'notnull' => true, 'default' => 0]);
        $table->addColumn('margem_lucro',         'decimal',   ['length' => 18, 'precision' => 18, 'scale' => 4, 'notnull' => true, 'default' => 0]);
        $table->addColumn('custo_operacional',    'decimal',   ['length' => 18, 'precision' => 18, 'scale' => 4, 'notnull' => true, 'default' => 0]);
        $table->addColumn('valor_venda_sugerido', 'decimal',   ['length' => 18, 'precision' => 18, 'scale' => 4, 'notnull' => true, 'default' => 0]);
        $table->addColumn('preco_venda',          'decimal',   ['length' => 18, 'precision' => 18, 'scale' => 4, 'notnull' => true, 'default' => 0]);
        $table->addColumn('descricao',            'string',   ['length' => 255, 'notnull' => true]);
        $table->addColumn('ativo',                'boolean',  ['default' => true,  'notnull' => true]);
        $table->addColumn('excluido',             'boolean',  ['default' => false,  'notnull' => true]);
        $table->addColumn('criado_em',            'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em',        'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('products');
    }
}
