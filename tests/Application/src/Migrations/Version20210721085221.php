<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210721085221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sylius_payment_provider_transfer');
        $this->addSql('CREATE TABLE sylius_payment_provider_transfer (id INT AUTO_INCREMENT NOT NULL, internal_id VARCHAR(255) NOT NULL, status VARCHAR(10) NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, bank_id VARCHAR(255) NOT NULL, shop_id VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, scheduled_at DATETIME DEFAULT NULL, executed_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_DAD5D8F7BFDFB4D8 (internal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_payment_provider_shop ADD internal_id VARCHAR(255) NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ED0BDCBEBFDFB4D8 ON sylius_payment_provider_shop (internal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sylius_payment_provider_transfer');
        $this->addSql('CREATE TABLE sylius_payment_provider_transfer (id VARCHAR(255) NOT NULL, status VARCHAR(10) NOT NULL, amount INT NOT NULL, currency VARCHAR(3) NOT NULL, bank_id VARCHAR(255) NOT NULL, shop_id VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, scheduled_at DATETIME DEFAULT NULL, executed_at DATETIME DEFAULT NULL, PRIMARY KEY(id, status, amount, currency, bank_id, shop_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP INDEX UNIQ_ED0BDCBEBFDFB4D8 ON sylius_payment_provider_shop');
        $this->addSql('ALTER TABLE sylius_payment_provider_shop DROP internal_id, CHANGE id id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
