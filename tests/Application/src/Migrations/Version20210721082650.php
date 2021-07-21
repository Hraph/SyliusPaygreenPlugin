<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210721082650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_payment_provider_shop (id VARCHAR(255) NOT NULL, private_key VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, available_mode LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', business_identifier VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, company_type VARCHAR(255) DEFAULT NULL, paiement_type VARCHAR(255) DEFAULT NULL, activate TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, validated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_payment_provider_transfer (id VARCHAR(255) NOT NULL, status VARCHAR(10) NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, bank_id VARCHAR(255) NOT NULL, shop_id VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, scheduled_at DATETIME DEFAULT NULL, executed_at DATETIME DEFAULT NULL, PRIMARY KEY(id, status, amount, currency, bank_id, shop_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_adjustment CHANGE details details LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sylius_payment_provider_shop');
        $this->addSql('DROP TABLE sylius_payment_provider_transfer');
        $this->addSql('ALTER TABLE sylius_adjustment CHANGE details details LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
