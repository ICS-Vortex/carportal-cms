<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013162000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Scope maintenance procedures to CMS author and service station';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE maintenance_procedures ADD created_by_user_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE maintenance_procedures ADD service_station_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE maintenance_procedures ADD CONSTRAINT fk_maintenance_procedures_created_by FOREIGN KEY (created_by_user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_procedures ADD CONSTRAINT fk_maintenance_procedures_station FOREIGN KEY (service_station_id) REFERENCES service_stations (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX idx_maintenance_procedures_station ON maintenance_procedures (service_station_id)');
        $this->addSql('CREATE INDEX idx_maintenance_procedures_created_by ON maintenance_procedures (created_by_user_id)');
        $this->addSql('DROP INDEX IF EXISTS uniq_b1dd7ecc77153098');
        $this->addSql('CREATE UNIQUE INDEX uniq_maintenance_procedures_code_global ON maintenance_procedures (code) WHERE service_station_id IS NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_maintenance_procedures_station_code ON maintenance_procedures (service_station_id, code) WHERE service_station_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS uniq_maintenance_procedures_station_code');
        $this->addSql('DROP INDEX IF EXISTS uniq_maintenance_procedures_code_global');
        $this->addSql('CREATE UNIQUE INDEX uniq_b1dd7ecc77153098 ON maintenance_procedures (code)');
        $this->addSql('ALTER TABLE maintenance_procedures DROP CONSTRAINT fk_maintenance_procedures_station');
        $this->addSql('ALTER TABLE maintenance_procedures DROP CONSTRAINT fk_maintenance_procedures_created_by');
        $this->addSql('DROP INDEX idx_maintenance_procedures_created_by');
        $this->addSql('DROP INDEX idx_maintenance_procedures_station');
        $this->addSql('ALTER TABLE maintenance_procedures DROP service_station_id');
        $this->addSql('ALTER TABLE maintenance_procedures DROP created_by_user_id');
    }
}
