<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240112145419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Keep the comments, because this table is defined by the messenger component
        $this->addSql('
            CREATE TABLE messenger_messages (
                id BIGINT AUTO_INCREMENT NOT NULL,
                body LONGTEXT NOT NULL,
                headers LONGTEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX IDX_75EA56E0FB7336F0 (queue_name),
                INDEX IDX_75EA56E0E3BD61CE (available_at),
                INDEX IDX_75EA56E016BA31DB (delivered_at),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql(
            '
            CREATE TABLE customer (
                id VARCHAR(255) NOT NULL,
                email VARCHAR(320) NOT NULL,
                name VARCHAR(255) NOT NULL,
                description LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX email (email),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );

        $this->addSql(
            '
            CREATE TABLE subscription (
                id VARCHAR(255) NOT NULL,
                customer_id VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                starts_at DATETIME NOT NULL,
                cancel_at DATETIME DEFAULT NULL,
                cancel_at_period_end TINYINT(1) NOT NULL,
                canceled_at DATETIME DEFAULT NULL,
                description LONGTEXT NOT NULL,
                INDEX customer_id (customer_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );

        $this->addSql(
            '
            CREATE TABLE product (
                id VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                default_price_id VARCHAR(255),
                active TINYINT(1) NOT NULL,
                type VARCHAR(255) NOT NULL,
                description LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX active (active),
                INDEX type (type),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );
    }
}
