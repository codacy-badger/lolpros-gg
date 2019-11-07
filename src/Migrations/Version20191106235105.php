<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191106235105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audit__log_details DROP FOREIGN KEY FK_941E99193C8F0C57');
        $this->addSql('DROP TABLE audit__changelog');
        $this->addSql('DROP TABLE audit__log_details');
        $this->addSql('DROP TABLE match__league__match');
        $this->addSql('DROP TABLE report__reports');
        $this->addSql('DROP TABLE report__requests');
        $this->addSql('DROP TABLE team__structure');
        $this->addSql('ALTER TABLE player_region DROP FOREIGN KEY FK_906267A899E6F5DF');
        $this->addSql('DROP INDEX IDX_906267A899E6F5DF ON player_region');
        $this->addSql('ALTER TABLE player_region DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE player_region CHANGE player_id identity_id INT NOT NULL');
        $this->addSql('ALTER TABLE player_region ADD CONSTRAINT FK_906267A8FF3ED4A8 FOREIGN KEY (identity_id) REFERENCES player__player (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_906267A8FF3ED4A8 ON player_region (identity_id)');
        $this->addSql('ALTER TABLE player_region ADD PRIMARY KEY (identity_id, region_id)');
        $this->addSql('ALTER TABLE team__members DROP FOREIGN KEY FK_89AAEFFC99E6F5DF');
        $this->addSql('DROP INDEX IDX_89AAEFFC99E6F5DF ON team__members');
        $this->addSql('ALTER TABLE team__members CHANGE player_id identity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team__members ADD CONSTRAINT FK_89AAEFFCFF3ED4A8 FOREIGN KEY (identity_id) REFERENCES player__player (id)');
        $this->addSql('CREATE INDEX IDX_89AAEFFCFF3ED4A8 ON team__members (identity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE audit__changelog (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', version VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, title VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, date DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE audit__log_details (id INT AUTO_INCREMENT NOT NULL, changelog_id INT DEFAULT NULL, description LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(75) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_941E99193C8F0C57 (changelog_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE match__league__match (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', game_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, queue VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, season INT NOT NULL, timestamp VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, game_duration INT NOT NULL, UNIQUE INDEX match_game (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE report__reports (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', message VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, active TINYINT(1) NOT NULL, type VARCHAR(75) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_8537DF4599E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE report__requests (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', content LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, done TINYINT(1) DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE team__structure (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, logo VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, tag VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE audit__log_details ADD CONSTRAINT FK_941E99193C8F0C57 FOREIGN KEY (changelog_id) REFERENCES audit__changelog (id)');
        $this->addSql('ALTER TABLE report__reports ADD CONSTRAINT FK_8537DF4599E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id)');
        $this->addSql('ALTER TABLE player_region DROP FOREIGN KEY FK_906267A8FF3ED4A8');
        $this->addSql('DROP INDEX IDX_906267A8FF3ED4A8 ON player_region');
        $this->addSql('ALTER TABLE player_region DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE player_region CHANGE identity_id player_id INT NOT NULL');
        $this->addSql('ALTER TABLE player_region ADD CONSTRAINT FK_906267A899E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_906267A899E6F5DF ON player_region (player_id)');
        $this->addSql('ALTER TABLE player_region ADD PRIMARY KEY (player_id, region_id)');
        $this->addSql('ALTER TABLE team__members DROP FOREIGN KEY FK_89AAEFFCFF3ED4A8');
        $this->addSql('DROP INDEX IDX_89AAEFFCFF3ED4A8 ON team__members');
        $this->addSql('ALTER TABLE team__members CHANGE identity_id player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team__members ADD CONSTRAINT FK_89AAEFFC99E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id)');
        $this->addSql('CREATE INDEX IDX_89AAEFFC99E6F5DF ON team__members (player_id)');
    }
}
