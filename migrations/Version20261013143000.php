<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Service log appointments: time slots, record kind, station scheduling settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_stations ADD appointment_slot_minutes INT NOT NULL DEFAULT 60');
        $this->addSql('ALTER TABLE service_stations ADD appointment_day_start TIME(0) WITHOUT TIME ZONE NOT NULL DEFAULT \'09:00:00\'');
        $this->addSql('ALTER TABLE service_stations ADD appointment_day_end TIME(0) WITHOUT TIME ZONE NOT NULL DEFAULT \'18:00:00\'');
        $this->addSql('ALTER TABLE service_stations ADD appointment_slot_capacity INT NOT NULL DEFAULT 2');

        $this->addSql('ALTER TABLE service_logs ADD service_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql("ALTER TABLE service_logs ADD record_kind VARCHAR(20) NOT NULL DEFAULT 'visit'");
        $this->addSql("ALTER TABLE service_logs ADD CONSTRAINT chk_service_logs_record_kind CHECK (record_kind IN ('visit', 'appointment'))");
        $this->addSql('CREATE INDEX idx_service_logs_station_appointment ON service_logs (service_station_id, service_date, service_time) WHERE record_kind = \'appointment\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_service_logs_station_appointment');
        $this->addSql('ALTER TABLE service_logs DROP CONSTRAINT chk_service_logs_record_kind');
        $this->addSql('ALTER TABLE service_logs DROP COLUMN record_kind');
        $this->addSql('ALTER TABLE service_logs DROP COLUMN service_time');

        $this->addSql('ALTER TABLE service_stations DROP COLUMN appointment_slot_capacity');
        $this->addSql('ALTER TABLE service_stations DROP COLUMN appointment_day_end');
        $this->addSql('ALTER TABLE service_stations DROP COLUMN appointment_day_start');
        $this->addSql('ALTER TABLE service_stations DROP COLUMN appointment_slot_minutes');
    }
}
