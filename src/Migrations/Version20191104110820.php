<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191104110820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team__team DROP FOREIGN KEY FK_C4E0A61F98260155');
        $this->addSql('DROP INDEX idx_c4e0a61f98260155 ON team__team');
        $this->addSql('CREATE INDEX IDX_5EC39F4498260155 ON team__team (region_id)');
        $this->addSql('ALTER TABLE team__team ADD CONSTRAINT FK_C4E0A61F98260155 FOREIGN KEY (region_id) REFERENCES region__region (id)');
        $this->addSql('DROP INDEX UNIQ_1483A5E9A0D96FBF ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E992FC23A8 ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E9C05FB297 ON users');
        $this->addSql('ALTER TABLE users ADD uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD discord_id VARCHAR(255) DEFAULT NULL, ADD twitch_id VARCHAR(255) DEFAULT NULL, ADD twitter_token VARCHAR(255) DEFAULT NULL, ADD twitter_secret VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP username_canonical, DROP email_canonical, DROP enabled, DROP last_login, DROP confirmation_token, DROP password_requested_at, CHANGE username username VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE team__team DROP FOREIGN KEY FK_5EC39F4498260155');
        $this->addSql('DROP INDEX idx_5ec39f4498260155 ON team__team');
        $this->addSql('CREATE INDEX IDX_C4E0A61F98260155 ON team__team (region_id)');
        $this->addSql('ALTER TABLE team__team ADD CONSTRAINT FK_5EC39F4498260155 FOREIGN KEY (region_id) REFERENCES region__region (id)');
        $this->addSql('DROP INDEX UNIQ_1483A5E9F85E0677 ON users');
        $this->addSql('ALTER TABLE users ADD username_canonical VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, ADD email_canonical VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, ADD enabled TINYINT(1) NOT NULL, ADD last_login DATETIME DEFAULT NULL, ADD confirmation_token VARCHAR(180) DEFAULT NULL COLLATE utf8_unicode_ci, ADD password_requested_at DATETIME DEFAULT NULL, DROP uuid, DROP discord_id, DROP twitch_id, DROP twitter_token, DROP twitter_secret, DROP created_at, DROP updated_at, CHANGE username username VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, CHANGE email email VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, CHANGE password password VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9A0D96FBF ON users (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E992FC23A8 ON users (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9C05FB297 ON users (confirmation_token)');
    }
}
