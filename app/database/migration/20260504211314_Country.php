<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504211314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Country';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('country');

        $table->addColumn('id',            'bigint',   ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $table->addColumn('codigo',        'string',   ['length' => 3,   'notnull' => true]);
        $table->addColumn('nome',          'string',   ['length' => 255, 'notnull' => true]);
        $table->addColumn('localizacao',   'string',   ['length' => 10,  'notnull' => false]);
        $table->addColumn('lingua',        'string',   ['length' => 50,  'notnull' => false]);
        $table->addColumn('moeda',         'string',   ['length' => 10,  'notnull' => false]);
        $table->addColumn('criado_em',     'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['codigo']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('country');
    }
}
