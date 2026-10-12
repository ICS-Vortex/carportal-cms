<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow one active maintenance template per configuration per service station';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_maintenance_schedule_templates_unique_configuration_active');
        $this->addSql('
            CREATE UNIQUE INDEX idx_mst_unique_global_configuration_active
            ON maintenance_schedule_templates (configuration_id)
            WHERE deleted = false AND station_id IS NULL AND configuration_id IS NOT NULL
        ');
        $this->addSql('
            CREATE UNIQUE INDEX idx_mst_unique_station_configuration_active
            ON maintenance_schedule_templates (configuration_id, station_id)
            WHERE deleted = false AND station_id IS NOT NULL AND configuration_id IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_mst_unique_station_configuration_active');
        $this->addSql('DROP INDEX IF EXISTS idx_mst_unique_global_configuration_active');
        $this->addSql('
            CREATE UNIQUE INDEX idx_maintenance_schedule_templates_unique_configuration_active
            ON maintenance_schedule_templates (configuration_id)
            WHERE deleted = false
        ');
    }
}
