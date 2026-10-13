<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schedule blocks (holidays / closed hours) and appointment email reminder timestamp';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE service_station_schedule_blocks (
                id UUID NOT NULL,
                station_id UUID DEFAULT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                start_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL,
                end_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL,
                label_uk TEXT NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )
        ');
        $this->addSql('CREATE INDEX idx_schedule_blocks_station_dates ON service_station_schedule_blocks (station_id, start_date, end_date)');
        $this->addSql('ALTER TABLE service_station_schedule_blocks ADD CONSTRAINT FK_schedule_blocks_station FOREIGN KEY (station_id) REFERENCES service_stations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE service_logs ADD appointment_reminder_sent_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_logs DROP COLUMN appointment_reminder_sent_at');
        $this->addSql('DROP TABLE service_station_schedule_blocks');
    }
}
