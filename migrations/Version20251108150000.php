<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251108150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des entités Joueur, Equipe et PokemonEquipe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE joueur (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudo VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD5F3725E7927C74 ON joueur (email)');
        $this->addSql('CREATE TABLE equipe (id SERIAL NOT NULL, joueur_id INT NOT NULL, nom VARCHAR(100) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN equipe.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_2449BA1527C1D44 ON equipe (joueur_id)');
        $this->addSql('CREATE TABLE pokemon_equipe (id SERIAL NOT NULL, equipe_id INT NOT NULL, pokemon_id INT NOT NULL, pokemon_name VARCHAR(100) NOT NULL, surnom VARCHAR(100) DEFAULT NULL, sprite VARCHAR(255) DEFAULT NULL, hp INT NOT NULL, attack INT NOT NULL, defense INT NOT NULL, speed INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C2C6A0948A12D2B6 ON pokemon_equipe (equipe_id)');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA1527C1D44 FOREIGN KEY (joueur_id) REFERENCES joueur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pokemon_equipe ADD CONSTRAINT FK_C2C6A0948A12D2B6 FOREIGN KEY (equipe_id) REFERENCES equipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon_equipe DROP CONSTRAINT FK_C2C6A0948A12D2B6');
        $this->addSql('ALTER TABLE equipe DROP CONSTRAINT FK_2449BA1527C1D44');
        $this->addSql('DROP TABLE pokemon_equipe');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE joueur');
    }
}
