<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Service stations, station-scoped labor prices, optional station on maintenance templates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE service_stations (
                id UUID NOT NULL,
                slug TEXT NOT NULL,
                name_uk TEXT NOT NULL,
                city_uk TEXT DEFAULT NULL,
                address_uk TEXT DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT true,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('CREATE UNIQUE INDEX uniq_service_stations_slug ON service_stations (slug)');

        $this->addSql('ALTER TABLE maintenance_schedule_templates ADD station_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE maintenance_schedule_templates ADD CONSTRAINT FK_mst_station FOREIGN KEY (station_id) REFERENCES service_stations (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_maintenance_schedule_templates_station_id ON maintenance_schedule_templates (station_id)');

        $this->addSql('
            CREATE TABLE maintenance_station_labor_prices (
                id UUID NOT NULL,
                station_id UUID NOT NULL,
                procedure_id UUID DEFAULT NULL,
                schedule_item_id UUID DEFAULT NULL,
                labor_price_uah INT NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT FK_mslp_station FOREIGN KEY (station_id) REFERENCES service_stations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_mslp_procedure FOREIGN KEY (procedure_id) REFERENCES maintenance_procedures (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_mslp_schedule_item FOREIGN KEY (schedule_item_id) REFERENCES maintenance_schedule_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT chk_mslp_target CHECK (
                    (procedure_id IS NOT NULL AND schedule_item_id IS NULL)
                    OR (procedure_id IS NULL AND schedule_item_id IS NOT NULL)
                )
            )
        ');
        $this->addSql('CREATE UNIQUE INDEX uniq_mslp_station_procedure ON maintenance_station_labor_prices (station_id, procedure_id) WHERE schedule_item_id IS NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_mslp_station_schedule_item ON maintenance_station_labor_prices (station_id, schedule_item_id) WHERE schedule_item_id IS NOT NULL');

        $this->addSql('ALTER TABLE users ADD preferred_service_station_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_users_preferred_station FOREIGN KEY (preferred_service_station_id) REFERENCES service_stations (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE service_logs ADD service_station_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE service_logs ADD CONSTRAINT FK_service_logs_station FOREIGN KEY (service_station_id) REFERENCES service_stations (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_logs DROP CONSTRAINT FK_service_logs_station');
        $this->addSql('ALTER TABLE service_logs DROP COLUMN service_station_id');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_users_preferred_station');
        $this->addSql('ALTER TABLE users DROP COLUMN preferred_service_station_id');
        $this->addSql('DROP TABLE maintenance_station_labor_prices');
        $this->addSql('ALTER TABLE maintenance_schedule_templates DROP CONSTRAINT FK_mst_station');
        $this->addSql('DROP INDEX idx_maintenance_schedule_templates_station_id');
        $this->addSql('ALTER TABLE maintenance_schedule_templates DROP COLUMN station_id');
        $this->addSql('DROP TABLE service_stations');
    }
}
