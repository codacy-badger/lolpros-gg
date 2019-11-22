<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191122103701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player__player ADD role VARCHAR(255) DEFAULT NULL, ADD role_name VARCHAR(255) DEFAULT NULL');
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
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D17F50A6 ON users (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player__player DROP role, DROP role_name');
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
        $this->addSql('DROP INDEX UNIQ_1483A5E9D17F50A6 ON users');
    }
}
