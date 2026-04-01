<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401171603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phoenix API Integration: Add token to users and external_id with source to photos.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD phoenix_api_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE photos ADD external_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE photos ADD source VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP phoenix_api_token');
        $this->addSql('ALTER TABLE photos DROP external_id');
        $this->addSql('ALTER TABLE photos DROP source');
    }
}
