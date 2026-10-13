<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CMS admin station scope, appointment estimate, vehicle registration metadata';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_service_stations (
            user_id UUID NOT NULL,
            service_station_id UUID NOT NULL,
            PRIMARY KEY (user_id, service_station_id)
        )');
        $this->addSql('CREATE INDEX idx_user_service_stations_station ON user_service_stations (service_station_id)');
        $this->addSql('ALTER TABLE user_service_stations ADD CONSTRAINT fk_user_service_stations_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_service_stations ADD CONSTRAINT fk_user_service_stations_station FOREIGN KEY (service_station_id) REFERENCES service_stations (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('ALTER TABLE service_logs ADD estimated_cost_uah INT DEFAULT NULL');

        $this->addSql('ALTER TABLE vehicles ADD registered_by_user_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicles ADD registered_at_station_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_registered_by FOREIGN KEY (registered_by_user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_registered_at_station FOREIGN KEY (registered_at_station_id) REFERENCES service_stations (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX idx_vehicles_registered_at_station ON vehicles (registered_at_station_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT fk_vehicles_registered_at_station');
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT fk_vehicles_registered_by');
        $this->addSql('ALTER TABLE vehicles DROP registered_at_station_id');
        $this->addSql('ALTER TABLE vehicles DROP registered_by_user_id');
        $this->addSql('ALTER TABLE service_logs DROP estimated_cost_uah');
        $this->addSql('DROP TABLE user_service_stations');
    }
}
