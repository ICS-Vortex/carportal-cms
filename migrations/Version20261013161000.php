<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Idempotent repair for station-scope / appointment columns (safe if Version20261013160000 partially applied).
 */
final class Version20261013161000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure user_service_stations, service_logs.estimated_cost_uah, vehicle registration columns exist';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS user_service_stations (
            user_id UUID NOT NULL,
            service_station_id UUID NOT NULL,
            PRIMARY KEY (user_id, service_station_id)
        )');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_user_service_stations_station ON user_service_stations (service_station_id)');

        $this->addSql(<<<'SQL'
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_user_service_stations_user') THEN
        ALTER TABLE user_service_stations ADD CONSTRAINT fk_user_service_stations_user
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE;
    END IF;
END $$;
SQL);

        $this->addSql(<<<'SQL'
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_user_service_stations_station') THEN
        ALTER TABLE user_service_stations ADD CONSTRAINT fk_user_service_stations_station
            FOREIGN KEY (service_station_id) REFERENCES service_stations (id) ON DELETE CASCADE NOT DEFERRABLE;
    END IF;
END $$;
SQL);

        $this->addSql('ALTER TABLE service_logs ADD COLUMN IF NOT EXISTS estimated_cost_uah INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS registered_by_user_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS registered_at_station_id UUID DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vehicles_registered_at_station ON vehicles (registered_at_station_id)');

        $this->addSql(<<<'SQL'
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_vehicles_registered_by') THEN
        ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_registered_by
            FOREIGN KEY (registered_by_user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE;
    END IF;
END $$;
SQL);

        $this->addSql(<<<'SQL'
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_vehicles_registered_at_station') THEN
        ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_registered_at_station
            FOREIGN KEY (registered_at_station_id) REFERENCES service_stations (id) ON DELETE SET NULL NOT DEFERRABLE;
    END IF;
END $$;
SQL);
    }

    public function down(Schema $schema): void
    {
        // Repair migration — no down.
    }
}
