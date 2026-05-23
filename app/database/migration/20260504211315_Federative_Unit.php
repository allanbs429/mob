<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504211315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Federative_Unit';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('federative_unit');

        $table->addColumn('id',            'bigint', ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $table->addColumn('id_pais',       'bigint', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('codigo',        'string', ['length' => 10,  'notnull' => true]);
        $table->addColumn('nome',          'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('sigla',         'string', ['length' => 5,   'notnull' => true]);
        $table->addColumn('criado_em',     'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['codigo']);
        $table->addIndex(['id_pais']);

        $table->addForeignKeyConstraint(
            'country',
            ['id_pais'],
            ['id'],
            ['onDelete' => 'RESTRICT', 'onUpdate' => 'CASCADE'],
            'fk_federative_unit_country'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('federative_unit');
    }
}
