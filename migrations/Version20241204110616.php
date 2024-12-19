<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204110616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lobby_team_blue (lobby_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(lobby_id, user_id))');
        $this->addSql('CREATE INDEX IDX_C439D26FB6612FD9 ON lobby_team_blue (lobby_id)');
        $this->addSql('CREATE INDEX IDX_C439D26FA76ED395 ON lobby_team_blue (user_id)');
        $this->addSql('ALTER TABLE lobby_team_blue ADD CONSTRAINT FK_C439D26FB6612FD9 FOREIGN KEY (lobby_id) REFERENCES lobby (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lobby_team_blue ADD CONSTRAINT FK_C439D26FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lobby_team_blue DROP CONSTRAINT FK_C439D26FB6612FD9');
        $this->addSql('ALTER TABLE lobby_team_blue DROP CONSTRAINT FK_C439D26FA76ED395');
        $this->addSql('DROP TABLE lobby_team_blue');
    }
}
