<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203170640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lobby ADD chief_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lobby ADD is_public BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE lobby ADD CONSTRAINT FK_CCE455F77A7B68E1 FOREIGN KEY (chief_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CCE455F77A7B68E1 ON lobby (chief_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lobby DROP CONSTRAINT FK_CCE455F77A7B68E1');
        $this->addSql('DROP INDEX UNIQ_CCE455F77A7B68E1');
        $this->addSql('ALTER TABLE lobby DROP chief_id');
        $this->addSql('ALTER TABLE lobby DROP is_public');
    }
}
