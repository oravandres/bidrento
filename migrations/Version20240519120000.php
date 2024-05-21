<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240520120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create property and property_relation tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE property (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            type TINYINT NOT NULL,
            created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            status TINYINT DEFAULT 1 NOT NULL,
            INDEX IDX_NAME (name),
            INDEX IDX_TYPE (type),
            INDEX IDX_STATUS (status),
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE TABLE property_relation (
            property_id INT NOT NULL,
            parent_id INT NOT NULL,
            created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            status TINYINT DEFAULT 1 NOT NULL,
            INDEX IDX_STATUS (status),
            PRIMARY KEY(property_id, parent_id)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS property_relation');
        $this->addSql('DROP TABLE IF EXISTS property');
    }
}