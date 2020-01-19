<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200106182752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player__league__ranking DROP FOREIGN KEY FK_4F4755697E3C61F9');
        $this->addSql('ALTER TABLE player__league__summoner_name DROP FOREIGN KEY FK_32F031DA7E3C61F9');
        $this->addSql('ALTER TABLE player__league__summoner_name DROP FOREIGN KEY FK_32F031DA2DE62210');
        $this->addSql('ALTER TABLE player__league__riot_account DROP FOREIGN KEY FK_E71F099F99E6F5DF');
        $this->addSql('ALTER TABLE player__social_media DROP FOREIGN KEY FK_AF070B837E3C61F9');
        $this->addSql('ALTER TABLE player_region DROP FOREIGN KEY FK_906267A899E6F5DF');
        $this->addSql('ALTER TABLE team__members DROP FOREIGN KEY FK_89AAEFFC99E6F5DF');
        $this->addSql('CREATE TABLE league__player (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, score INT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_D8838497CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE league__ranking (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, best TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, queue_type VARCHAR(255) NOT NULL, tier VARCHAR(255) NOT NULL, ranking INT DEFAULT 0 NOT NULL, league_points INT DEFAULT 0 NOT NULL, wins INT DEFAULT 0 NOT NULL, losses INT DEFAULT 0 NOT NULL, score INT DEFAULT 0 NOT NULL, season VARCHAR(255) NOT NULL, INDEX IDX_D34B301E7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE league__riot_account (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', riot_id VARCHAR(255) NOT NULL, account_id VARCHAR(255) NOT NULL, encrypted_puuid VARCHAR(255) NOT NULL, encrypted_riot_id VARCHAR(255) NOT NULL, encrypted_account_id VARCHAR(255) NOT NULL, profile_icon_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, score INT DEFAULT 0 NOT NULL, summoner_level INT DEFAULT 1 NOT NULL, INDEX IDX_74FB0E0399E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE league__summoner_name (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, previous_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, current TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, changed_at DATETIME NOT NULL, INDEX IDX_CBDA0AB27E3C61F9 (owner_id), UNIQUE INDEX UNIQ_CBDA0AB22DE62210 (previous_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile__profile (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, country VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region__profile (profile_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_86D84F80CCFA12B8 (profile_id), INDEX IDX_86D84F8098260155 (region_id), PRIMARY KEY(profile_id, region_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile__social_media (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, twitter VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, twitch VARCHAR(255) DEFAULT NULL, discord VARCHAR(255) DEFAULT NULL, leaguepedia VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_54E4D2F77E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile__staff (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_A07C7869CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('INSERT INTO profile__profile (id, uuid, name, slug, country, updated_at, created_at) SELECT player.id, player.uuid, player.name, player.slug, player.country, player.updated_at, player.created_at FROM player__player as player');
        $this->addSql('INSERT INTO profile__social_media (id, owner_id, created_at, updated_at, twitter, facebook, twitch, discord, leaguepedia) SELECT id, owner_id, created_at, updated_at, twitter, facebook, twitch, discord, leaguepedia FROM player__social_media');
        $this->addSql('INSERT INTO region__profile (profile_id, region_id) SELECT player_id, region_id FROM player_region');
        $this->addSql('INSERT INTO league__player (id, profile_id, position, score) SELECT id, id, position, score FROM player__player');
        $this->addSql('INSERT INTO league__riot_account (id, player_id, uuid, riot_id, account_id, encrypted_puuid, encrypted_riot_id, encrypted_account_id, profile_icon_id, created_at, updated_at, score, summoner_level) SELECT id, player_id, uuid, riot_id, account_id, encrypted_puuid, encrypted_riot_id, encrypted_account_id, profile_icon_id, created_at, updated_at, score, summoner_level FROM player__league__riot_account');
        $this->addSql('INSERT INTO league__ranking SELECT * FROM player__league__ranking');
        $this->addSql('INSERT INTO league__summoner_name (id, owner_id, name, current, created_at, changed_at, previous_id) SELECT * FROM player__league__summoner_name');

        $this->addSql('ALTER TABLE league__player ADD CONSTRAINT FK_D8838497CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile__profile (id)');
        $this->addSql('ALTER TABLE league__ranking ADD CONSTRAINT FK_D34B301E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES league__riot_account (id)');
        $this->addSql('ALTER TABLE league__riot_account ADD CONSTRAINT FK_74FB0E0399E6F5DF FOREIGN KEY (player_id) REFERENCES league__player (id)');
        $this->addSql('ALTER TABLE league__summoner_name ADD CONSTRAINT FK_CBDA0AB27E3C61F9 FOREIGN KEY (owner_id) REFERENCES league__riot_account (id)');
        $this->addSql('ALTER TABLE league__summoner_name ADD CONSTRAINT FK_CBDA0AB22DE62210 FOREIGN KEY (previous_id) REFERENCES league__summoner_name (id)');
        $this->addSql('ALTER TABLE region__profile ADD CONSTRAINT FK_86D84F80CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile__profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE region__profile ADD CONSTRAINT FK_86D84F8098260155 FOREIGN KEY (region_id) REFERENCES region__region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile__social_media ADD CONSTRAINT FK_54E4D2F77E3C61F9 FOREIGN KEY (owner_id) REFERENCES profile__profile (id)');
        $this->addSql('ALTER TABLE profile__staff ADD CONSTRAINT FK_A07C7869CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile__profile (id)');
        $this->addSql('DROP TABLE player__league__ranking');
        $this->addSql('DROP TABLE player__league__riot_account');
        $this->addSql('DROP TABLE player__league__summoner_name');
        $this->addSql('DROP TABLE player__player');
        $this->addSql('DROP TABLE player__social_media');
        $this->addSql('DROP TABLE player_region');
        $this->addSql('DROP INDEX IDX_89AAEFFC99E6F5DF ON team__members');
        $this->addSql('ALTER TABLE team__members CHANGE player_id profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team__members ADD CONSTRAINT FK_89AAEFFCCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile__profile (id)');
        $this->addSql('CREATE INDEX IDX_89AAEFFCCCFA12B8 ON team__members (profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE league__riot_account DROP FOREIGN KEY FK_74FB0E0399E6F5DF');
        $this->addSql('ALTER TABLE league__ranking DROP FOREIGN KEY FK_D34B301E7E3C61F9');
        $this->addSql('ALTER TABLE league__summoner_name DROP FOREIGN KEY FK_CBDA0AB27E3C61F9');
        $this->addSql('ALTER TABLE league__summoner_name DROP FOREIGN KEY FK_CBDA0AB22DE62210');
        $this->addSql('ALTER TABLE league__player DROP FOREIGN KEY FK_D8838497CCFA12B8');
        $this->addSql('ALTER TABLE region__profile DROP FOREIGN KEY FK_86D84F80CCFA12B8');
        $this->addSql('ALTER TABLE profile__social_media DROP FOREIGN KEY FK_54E4D2F77E3C61F9');
        $this->addSql('ALTER TABLE profile__staff DROP FOREIGN KEY FK_A07C7869CCFA12B8');
        $this->addSql('ALTER TABLE team__members DROP FOREIGN KEY FK_89AAEFFCCCFA12B8');
        $this->addSql('CREATE TABLE player__league__ranking (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, best TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, queue_type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, tier VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, ranking INT DEFAULT 0 NOT NULL, league_points INT DEFAULT 0 NOT NULL, wins INT DEFAULT 0 NOT NULL, losses INT DEFAULT 0 NOT NULL, score INT DEFAULT 0 NOT NULL, season VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_4F4755697E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player__league__riot_account (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', riot_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, account_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, profile_icon_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, score INT DEFAULT 0 NOT NULL, summoner_level INT DEFAULT 1 NOT NULL, encrypted_puuid VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, encrypted_riot_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, encrypted_account_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_E71F099F99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player__league__summoner_name (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, previous_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, current TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, changed_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_32F031DA2DE62210 (previous_id), INDEX IDX_32F031DA7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player__player (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, country VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(75) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, position VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, score INT DEFAULT 0, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player__social_media (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, twitter VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, facebook VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, twitch VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, discord VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, leaguepedia VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, UNIQUE INDEX UNIQ_AF070B837E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE player_region (player_id INT NOT NULL, region_id INT NOT NULL, INDEX IDX_906267A898260155 (region_id), INDEX IDX_906267A899E6F5DF (player_id), PRIMARY KEY(player_id, region_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE player__league__ranking ADD CONSTRAINT FK_4F4755697E3C61F9 FOREIGN KEY (owner_id) REFERENCES player__league__riot_account (id)');
        $this->addSql('ALTER TABLE player__league__riot_account ADD CONSTRAINT FK_E71F099F99E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id)');
        $this->addSql('ALTER TABLE player__league__summoner_name ADD CONSTRAINT FK_32F031DA2DE62210 FOREIGN KEY (previous_id) REFERENCES player__league__summoner_name (id)');
        $this->addSql('ALTER TABLE player__league__summoner_name ADD CONSTRAINT FK_32F031DA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES player__league__riot_account (id)');
        $this->addSql('ALTER TABLE player__social_media ADD CONSTRAINT FK_AF070B837E3C61F9 FOREIGN KEY (owner_id) REFERENCES player__player (id)');
        $this->addSql('ALTER TABLE player_region ADD CONSTRAINT FK_906267A898260155 FOREIGN KEY (region_id) REFERENCES region__region (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_region ADD CONSTRAINT FK_906267A899E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE league__player');
        $this->addSql('DROP TABLE league__ranking');
        $this->addSql('DROP TABLE league__riot_account');
        $this->addSql('DROP TABLE league__summoner_name');
        $this->addSql('DROP TABLE profile__profile');
        $this->addSql('DROP TABLE region__profile');
        $this->addSql('DROP TABLE profile__social_media');
        $this->addSql('DROP TABLE profile__staff');
        $this->addSql('DROP INDEX IDX_89AAEFFCCCFA12B8 ON team__members');
        $this->addSql('ALTER TABLE team__members CHANGE profile_id player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team__members ADD CONSTRAINT FK_89AAEFFC99E6F5DF FOREIGN KEY (player_id) REFERENCES player__player (id)');
        $this->addSql('CREATE INDEX IDX_89AAEFFC99E6F5DF ON team__members (player_id)');
    }
}
