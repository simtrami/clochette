<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227012142 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, pseudo VARCHAR(30) NOT NULL, balance NUMERIC(8, 2) NOT NULL, year INT NOT NULL, staff_name VARCHAR(30) DEFAULT NULL, is_inducted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_7D3656A486CC499D (pseudo), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE details_transactions (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, transaction_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_C7C18FEB7294869C (article_id), INDEX IDX_C7C18FEB2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, parameters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_E545A0C55E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock_market_data (article_id_id INT NOT NULL, stock_value NUMERIC(8, 2) DEFAULT NULL, values_history LONGTEXT DEFAULT NULL, variation NUMERIC(9, 6) DEFAULT NULL, demand_coefficient NUMERIC(7, 6) DEFAULT NULL, PRIMARY KEY(article_id_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stocks (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, name VARCHAR(40) NOT NULL, selling_price NUMERIC(8, 2) NOT NULL, cost NUMERIC(8, 2) NOT NULL, quantity INT NOT NULL, volume NUMERIC(8, 2) DEFAULT NULL, is_for_sale TINYINT(1) NOT NULL, INDEX IDX_56F79805C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, staff_id INT DEFAULT NULL, zreport_id INT DEFAULT NULL, timestamp DATETIME NOT NULL, amount NUMERIC(8, 2) NOT NULL, method VARCHAR(7) NOT NULL, type SMALLINT NOT NULL, INDEX IDX_EAA81A4C9B6B5FBA (account_id), INDEX IDX_EAA81A4CD4D57CD (staff_id), INDEX IDX_EAA81A4CAF3ACD49 (zreport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE treasury (id INT AUTO_INCREMENT NOT NULL, zreport_id INT DEFAULT NULL, cash_register NUMERIC(8, 2) NOT NULL, safe NUMERIC(8, 2) NOT NULL, UNIQUE INDEX UNIQ_A099863C573FB7A9 (zreport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_stocks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_7520F33D5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, username VARCHAR(25) NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zreport (id INT AUTO_INCREMENT NOT NULL, staff_id INT NOT NULL, total_command NUMERIC(8, 2) NOT NULL, total_refund NUMERIC(8, 2) NOT NULL, total_refill NUMERIC(8, 2) NOT NULL, total NUMERIC(8, 2) NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_A099863CD4D57CD (staff_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE details_transactions ADD CONSTRAINT FK_C7C18FEB7294869C FOREIGN KEY (article_id) REFERENCES stocks (id)');
        $this->addSql('ALTER TABLE details_transactions ADD CONSTRAINT FK_C7C18FEB2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id)');
        $this->addSql('ALTER TABLE stock_market_data ADD CONSTRAINT FK_3D31ACE78F3EC46 FOREIGN KEY (article_id_id) REFERENCES stocks (id)');
        $this->addSql('ALTER TABLE stocks ADD CONSTRAINT FK_56F79805C54C8C93 FOREIGN KEY (type_id) REFERENCES type_stocks (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CD4D57CD FOREIGN KEY (staff_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CAF3ACD49 FOREIGN KEY (zreport_id) REFERENCES zreport (id)');
        $this->addSql('ALTER TABLE treasury ADD CONSTRAINT FK_A099863C573FB7A9 FOREIGN KEY (zreport_id) REFERENCES zreport (id)');
        $this->addSql('ALTER TABLE zreport ADD CONSTRAINT FK_A099863CD4D57CD FOREIGN KEY (staff_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C9B6B5FBA');
        $this->addSql('ALTER TABLE details_transactions DROP FOREIGN KEY FK_C7C18FEB7294869C');
        $this->addSql('ALTER TABLE stock_market_data DROP FOREIGN KEY FK_3D31ACE78F3EC46');
        $this->addSql('ALTER TABLE details_transactions DROP FOREIGN KEY FK_C7C18FEB2FC0CB0F');
        $this->addSql('ALTER TABLE treasury DROP FOREIGN KEY FK_A099863C573FB7A9');
        $this->addSql('ALTER TABLE stocks DROP FOREIGN KEY FK_56F79805C54C8C93');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CD4D57CD');
        $this->addSql('ALTER TABLE zreport DROP FOREIGN KEY FK_A099863CD4D57CD');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CAF3ACD49');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE details_transactions');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE stock_market_data');
        $this->addSql('DROP TABLE stocks');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE treasury');
        $this->addSql('DROP TABLE type_stocks');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE zreport');
    }
}
