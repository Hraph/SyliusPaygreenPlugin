<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211028132559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update shop and transfer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ED0BDCBEBFDFB4D8 ON sylius_payment_provider_shop (internal_id)');
        $this->addSql('ALTER TABLE sylius_payment_provider_shop CHANGE internal_id internal_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_payment_provider_transfer CHANGE internal_id internal_id VARCHAR(255) DEFAULT NULL, CHANGE bank_id bank_id VARCHAR(255) DEFAULT NULL, CHANGE shop_id shop_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_payment_provider_transfer ADD shop_internal_id VARCHAR(255) DEFAULT NULL, ADD details JSON NOT NULL');
        $this->addSql('ALTER TABLE sylius_payment_provider_transfer CHANGE status status VARCHAR(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_ED0BDCBEBFDFB4D8 ON sylius_payment_provider_shop');
        $this->addSql('ALTER TABLE sylius_payment_provider_shop CHANGE internal_id internal_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE sylius_payment_provider_transfer CHANGE internal_id internal_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE bank_id bank_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE shop_id shop_id VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE sylius_payment_provider_transfer DROP shop_internal_id, DROP details');
        $this->addSql('ALTER TABLE sylius_payment_provider_transfer CHANGE status status VARCHAR(10) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
