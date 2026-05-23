<?php

declare(strict_types=1);

namespace app\database\migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504211414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'City';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('city');

        $table->addColumn('id',            'bigint',   ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $table->addColumn('id_uf',         'bigint',   ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('codigo',        'string',   ['length' => 10,  'notnull' => true]);
        $table->addColumn('nome',          'string',   ['length' => 255, 'notnull' => true]);
        $table->addColumn('criado_em',     'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('atualizado_em', 'datetime', ['notnull' => true, 'default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['codigo']);
        $table->addIndex(['id_uf']);

        $table->addForeignKeyConstraint(
            'federative_unit',
            ['id_uf'],
            ['id'],
            ['onDelete' => 'RESTRICT', 'onUpdate' => 'CASCADE'],
            'fk_city_federative_unit'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('city');
    }
}
