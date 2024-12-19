<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203220240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE team_request (id SERIAL NOT NULL, user_sender_id INT DEFAULT NULL, user_receiver_id INT DEFAULT NULL, date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C4EEFEA8F6C43E79 ON team_request (user_sender_id)');
        $this->addSql('CREATE INDEX IDX_C4EEFEA864482423 ON team_request (user_receiver_id)');
        $this->addSql('ALTER TABLE team_request ADD CONSTRAINT FK_C4EEFEA8F6C43E79 FOREIGN KEY (user_sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_request ADD CONSTRAINT FK_C4EEFEA864482423 FOREIGN KEY (user_receiver_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE team_request DROP CONSTRAINT FK_C4EEFEA8F6C43E79');
        $this->addSql('ALTER TABLE team_request DROP CONSTRAINT FK_C4EEFEA864482423');
        $this->addSql('DROP TABLE team_request');
    }
}
