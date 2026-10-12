<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Maintenance plan file imports (upload log + async processing)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE maintenance_plan_imports (
                id UUID NOT NULL,
                plan_request_id UUID NOT NULL,
                vehicle_id UUID NOT NULL,
                uploaded_by_id UUID DEFAULT NULL,
                original_filename TEXT NOT NULL,
                stored_path TEXT NOT NULL,
                mime_type TEXT DEFAULT NULL,
                status TEXT NOT NULL DEFAULT \'pending\',
                rows_total INT DEFAULT NULL,
                rows_imported INT DEFAULT NULL,
                error_message TEXT DEFAULT NULL,
                log_text TEXT DEFAULT NULL,
                started_at TIMESTAMPTZ DEFAULT NULL,
                finished_at TIMESTAMPTZ DEFAULT NULL,
                created_at TIMESTAMPTZ NOT NULL,
                updated_at TIMESTAMPTZ NOT NULL,
                PRIMARY KEY (id))
        ');
        $this->addSql('CREATE INDEX idx_maintenance_plan_imports_plan_request ON maintenance_plan_imports (plan_request_id)');
        $this->addSql('CREATE INDEX idx_maintenance_plan_imports_vehicle ON maintenance_plan_imports (vehicle_id)');
        $this->addSql('CREATE INDEX idx_maintenance_plan_imports_status_created ON maintenance_plan_imports (status, created_at)');
        $this->addSql('ALTER TABLE maintenance_plan_imports ADD CONSTRAINT fk_mpi_plan_request FOREIGN KEY (plan_request_id) REFERENCES maintenance_plan_requests (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_plan_imports ADD CONSTRAINT fk_mpi_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE maintenance_plan_imports ADD CONSTRAINT fk_mpi_uploaded_by FOREIGN KEY (uploaded_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE maintenance_plan_imports DROP CONSTRAINT fk_mpi_uploaded_by');
        $this->addSql('ALTER TABLE maintenance_plan_imports DROP CONSTRAINT fk_mpi_vehicle');
        $this->addSql('ALTER TABLE maintenance_plan_imports DROP CONSTRAINT fk_mpi_plan_request');
        $this->addSql('DROP TABLE maintenance_plan_imports');
    }
}
