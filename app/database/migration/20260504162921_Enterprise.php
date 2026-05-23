<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504162921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enterprise';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('enterprise');

        $table->addColumn('id',            'bigint',  ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $table->addColumn('fantasia',      'string',  ['length' => 255, 'notnull' => true]);
        $table->addColumn('razao_social',  'string',  ['length' => 255, 'notnull' => false]);
        $table->addColumn('cnpj',          'string',  ['length' => 18,  'notnull' => false]);
        $table->addColumn('ie',            'string',  ['length' => 30,  'notnull' => false]);
        $table->addColumn('ativo',         'boolean', ['default' => true,  'notnull' => true]);
        $table->addColumn('excluido',      'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('criado_em',     'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['cnpj']);
        $table->addIndex(['ativo']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('enterprise');
    }
}
