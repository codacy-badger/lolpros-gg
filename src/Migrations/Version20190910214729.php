<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190910214729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player__player (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, country VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(75) NOT NULL, position VARCHAR(255) DEFAULT NULL, score INT DEFAULT 0, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player_region (player_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_906267A899E6F5DF (player_id), INDEX IDX_906267A898260155 (region_id), PRIMARY KEY(player_id, region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player__league__ranking (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, best TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, queue_type VARCHAR(255) NOT NULL, tier VARCHAR(255) NOT NULL, rank INT DEFAULT 0 NOT NULL, league_points INT DEFAULT 0 NOT NULL, wins INT DEFAULT 0 NOT NULL, losses INT DEFAULT 0 NOT NULL, score INT DEFAULT 0 NOT NULL, season VARCHAR(255) NOT NULL, INDEX IDX_4F4755697E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player__league__summoner_name (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, previous_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, current TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, changed_at DATETIME NOT NULL, INDEX IDX_32F031DA7E3C61F9 (owner_id), UNIQUE INDEX UNIQ_32F031DA2DE62210 (previous_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player__league__riot_account (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', riot_id VARCHAR(255) NOT NULL, account_id VARCHAR(255) NOT NULL, encrypted_puuid VARCHAR(255) NOT NULL, encrypted_riot_id VARCHAR(255) NOT NULL, encrypted_account_id VARCHAR(255) NOT NULL, profile_icon_id VARCHAR(255) DEFAULT NULL, smurf TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, score INT DEFAULT 0 NOT NULL, summoner_level INT DEFAULT 1 NOT NULL, INDEX IDX_E71F099F99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, entity_uuid VARCHAR(255) NOT NULL, entity_name VARCHAR(255) NOT NULL, linked_uuid VARCHAR(255) DEFAULT NULL, linked_name VARCHAR(255) DEFAULT NULL, INDEX IDX_F9383BB0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE add_request (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, position VARCHAR(255) NOT NULL, twitter VARCHAR(255) DEFAULT NULL, twitch VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, summoner_name VARCHAR(255) DEFAULT NULL, summoner_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region__region (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, shorthand VARCHAR(255) NOT NULL, countries LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team__members (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, team_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', join_date DATETIME DEFAULT NULL, leave_date DATETIME DEFAULT NULL, role VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_89AAEFFC99E6F5DF (player_id), INDEX IDX_89AAEFFC296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team__social_media (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, twitter VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, leaguepedia VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_F8552A67E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team__team (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, creation_date DATETIME DEFAULT NULL, disband_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5EC39F4498260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player__social_media (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, twitter VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, twitch VARCHAR(255) DEFAULT NULL, discord VARCHAR(255) DEFAULT NULL, leaguepedia VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_AF070B837E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, region_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', public_id VARCHAR(255) NOT NULL, version VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, type VARCHAR(75) NOT NULL, INDEX IDX_D8698A76296CD8AE (team_id), INDEX IDX_D8698A7698260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', discord_id VARCHAR(255) DEFAULT NULL, twitch_id VARCHAR(255) DEFAULT NULL, twitter_token VARCHAR(255) DEFAULT NULL, twitter_secret VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9D17F50A6 (uuid), UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_region ADD CONSTRAINT FK_906267A899E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_region ADD CONSTRAINT FK_906267A898260155 FOREIGN KEY (region_id) REFERENCES region__region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player__league__ranking ADD CONSTRAINT FK_4F4755697E3C61F9 FOREIGN KEY (owner_id) REFERENCES player__league__riot_account (id)');
        $this->addSql('ALTER TABLE player__league__summoner_name ADD CONSTRAINT FK_32F031DA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES player__league__riot_account (id)');
        $this->addSql('ALTER TABLE player__league__summoner_name ADD CONSTRAINT FK_32F031DA2DE62210 FOREIGN KEY (previous_id) REFERENCES player__league__summoner_name (id)');
        $this->addSql('ALTER TABLE player__league__riot_account ADD CONSTRAINT FK_E71F099F99E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id)');
        $this->addSql('ALTER TABLE admin_log ADD CONSTRAINT FK_F9383BB0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE team__members ADD CONSTRAINT FK_89AAEFFC99E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id)');
        $this->addSql('ALTER TABLE team__members ADD CONSTRAINT FK_89AAEFFC296CD8AE FOREIGN KEY (team_id) REFERENCES team__team (id)');
        $this->addSql('ALTER TABLE team__social_media ADD CONSTRAINT FK_F8552A67E3C61F9 FOREIGN KEY (owner_id) REFERENCES team__team (id)');
        $this->addSql('ALTER TABLE team__team ADD CONSTRAINT FK_5EC39F4498260155 FOREIGN KEY (region_id) REFERENCES region__region (id)');
        $this->addSql('ALTER TABLE player__social_media ADD CONSTRAINT FK_AF070B837E3C61F9 FOREIGN KEY (owner_id) REFERENCES player__player (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76296CD8AE FOREIGN KEY (team_id) REFERENCES team__team (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7698260155 FOREIGN KEY (region_id) REFERENCES region__region (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player_region DROP FOREIGN KEY FK_906267A899E6F5DF');
        $this->addSql('ALTER TABLE player__league__riot_account DROP FOREIGN KEY FK_E71F099F99E6F5DF');
        $this->addSql('ALTER TABLE team__members DROP FOREIGN KEY FK_89AAEFFC99E6F5DF');
        $this->addSql('ALTER TABLE player__social_media DROP FOREIGN KEY FK_AF070B837E3C61F9');
        $this->addSql('ALTER TABLE player__league__summoner_name DROP FOREIGN KEY FK_32F031DA2DE62210');
        $this->addSql('ALTER TABLE player__league__ranking DROP FOREIGN KEY FK_4F4755697E3C61F9');
        $this->addSql('ALTER TABLE player__league__summoner_name DROP FOREIGN KEY FK_32F031DA7E3C61F9');
        $this->addSql('ALTER TABLE player_region DROP FOREIGN KEY FK_906267A898260155');
        $this->addSql('ALTER TABLE team__team DROP FOREIGN KEY FK_5EC39F4498260155');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7698260155');
        $this->addSql('ALTER TABLE team__members DROP FOREIGN KEY FK_89AAEFFC296CD8AE');
        $this->addSql('ALTER TABLE team__social_media DROP FOREIGN KEY FK_F8552A67E3C61F9');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76296CD8AE');
        $this->addSql('ALTER TABLE admin_log DROP FOREIGN KEY FK_F9383BB0A76ED395');
        $this->addSql('DROP TABLE player__player');
        $this->addSql('DROP TABLE player_region');
        $this->addSql('DROP TABLE player__league__ranking');
        $this->addSql('DROP TABLE player__league__summoner_name');
        $this->addSql('DROP TABLE player__league__riot_account');
        $this->addSql('DROP TABLE admin_log');
        $this->addSql('DROP TABLE add_request');
        $this->addSql('DROP TABLE region__region');
        $this->addSql('DROP TABLE team__members');
        $this->addSql('DROP TABLE team__social_media');
        $this->addSql('DROP TABLE team__team');
        $this->addSql('DROP TABLE player__social_media');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
