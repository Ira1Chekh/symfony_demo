<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241013153506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE symfony_demo_article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, summary VARCHAR(255) NOT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, author_id INTEGER NOT NULL, CONSTRAINT FK_C5E3D389F675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C5E3D389F675F31B ON symfony_demo_article (author_id)');
        $this->addSql('CREATE TABLE symfony_demo_article_tag (article_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(article_id, tag_id), CONSTRAINT FK_CF214AB17294869C FOREIGN KEY (article_id) REFERENCES symfony_demo_article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CF214AB1BAD26311 FOREIGN KEY (tag_id) REFERENCES symfony_demo_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CF214AB17294869C ON symfony_demo_article_tag (article_id)');
        $this->addSql('CREATE INDEX IDX_CF214AB1BAD26311 ON symfony_demo_article_tag (tag_id)');
        $this->addSql('CREATE TABLE symfony_demo_review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, article_id INTEGER NOT NULL, author_id INTEGER NOT NULL, CONSTRAINT FK_7736DABD7294869C FOREIGN KEY (article_id) REFERENCES symfony_demo_article (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7736DABDF675F31B FOREIGN KEY (author_id) REFERENCES symfony_demo_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7736DABD7294869C ON symfony_demo_review (article_id)');
        $this->addSql('CREATE INDEX IDX_7736DABDF675F31B ON symfony_demo_review (author_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__symfony_demo_user AS SELECT id, full_name, username, email, password, roles FROM symfony_demo_user');
        $this->addSql('DROP TABLE symfony_demo_user');
        $this->addSql('CREATE TABLE symfony_demo_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL)');
        $this->addSql('INSERT INTO symfony_demo_user (id, full_name, username, email, password, roles) SELECT id, full_name, username, email, password, roles FROM __temp__symfony_demo_user');
        $this->addSql('DROP TABLE __temp__symfony_demo_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FB094A1E7927C74 ON symfony_demo_user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FB094A1F85E0677 ON symfony_demo_user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE symfony_demo_article');
        $this->addSql('DROP TABLE symfony_demo_article_tag');
        $this->addSql('DROP TABLE symfony_demo_review');
        $this->addSql('CREATE TEMPORARY TABLE __temp__symfony_demo_user AS SELECT id, full_name, username, email, password, roles FROM symfony_demo_user');
        $this->addSql('DROP TABLE symfony_demo_user');
        $this->addSql('CREATE TABLE symfony_demo_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO symfony_demo_user (id, full_name, username, email, password, roles) SELECT id, full_name, username, email, password, roles FROM __temp__symfony_demo_user');
        $this->addSql('DROP TABLE __temp__symfony_demo_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FB094A1F85E0677 ON symfony_demo_user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8FB094A1E7927C74 ON symfony_demo_user (email)');
    }
}
