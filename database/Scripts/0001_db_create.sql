-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema SZOK
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema SZOK
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `SZOK` DEFAULT CHARACTER SET utf8 ;
USE `SZOK` ;

-- -----------------------------------------------------
-- Table `SZOK`.`KategorieWiekowe`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`KategorieWiekowe` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`KategorieWiekowe` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(3) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idKategorieWiekowe_UNIQUE` (`id` ASC),
  UNIQUE INDEX `nazwa_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Filmy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Filmy` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Filmy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tytul` VARCHAR(127) NOT NULL,
  `opis` VARCHAR(512) NULL,
  `dataPremiery` DATE NOT NULL,
  `czasTrwania` INT NOT NULL,
  `czasReklam` INT NOT NULL,
  `plakat` VARCHAR(255) NULL,
  `zwiastun` VARCHAR(255) NULL,
  `KategorieWiekowe_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idFilmy_UNIQUE` (`id` ASC),
  INDEX `fk_Filmy_KategorieWiekowe1_idx` (`KategorieWiekowe_id` ASC),
  CONSTRAINT `fk_Filmy_KategorieWiekowe1`
  FOREIGN KEY (`KategorieWiekowe_id`)
  REFERENCES `SZOK`.`KategorieWiekowe` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB
  COMMENT = '\n\n';


-- -----------------------------------------------------
-- Table `SZOK`.`RodzajeFilmow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`RodzajeFilmow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`RodzajeFilmow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idRodzajFilmu_UNIQUE` (`id` ASC),
  UNIQUE INDEX `nazwa_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`WydarzeniaSpecjalne`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`WydarzeniaSpecjalne` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`WydarzeniaSpecjalne` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idWydarzeniaSpecjalne_UNIQUE` (`id` ASC),
  UNIQUE INDEX `nazwa_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`TypySeansow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`TypySeansow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`TypySeansow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idTypySeansow_UNIQUE` (`id` ASC),
  UNIQUE INDEX `nazwa_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Sale`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Sale` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Sale` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `numerSali` VARCHAR(3) NOT NULL,
  `dlugoscSali` INT NOT NULL,
  `szerokoscSali` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idSale_UNIQUE` (`id` ASC),
  UNIQUE INDEX `numerSali_UNIQUE` (`numerSali` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`PuleBiletow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`PuleBiletow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`PuleBiletow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idPuleBiletow_UNIQUE` (`id` ASC),
  UNIQUE INDEX `NazwaPuli_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Seanse`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Seanse` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Seanse` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `poczatekSeansu` DATETIME NOT NULL,
  `czyOdwolany` TINYINT NULL,
  `TypySeansow_id` INT UNSIGNED NOT NULL,
  `Sale_id` INT UNSIGNED NOT NULL,
  `PuleBiletow_id` INT UNSIGNED NOT NULL,
  `WydarzeniaSpecjalne_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idSeanse_UNIQUE` (`id` ASC),
  INDEX `fk_Seanse_WydarzeniaSpecjalne1_idx` (`WydarzeniaSpecjalne_id` ASC),
  INDEX `fk_Seanse_TypySeansow1_idx` (`TypySeansow_id` ASC),
  INDEX `fk_Seanse_Sale1_idx` (`Sale_id` ASC),
  INDEX `fk_Seanse_PuleBiletow1_idx` (`PuleBiletow_id` ASC),
  CONSTRAINT `fk_Seanse_WydarzeniaSpecjalne1`
  FOREIGN KEY (`WydarzeniaSpecjalne_id`)
  REFERENCES `SZOK`.`WydarzeniaSpecjalne` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Seanse_TypySeansow1`
  FOREIGN KEY (`TypySeansow_id`)
  REFERENCES `SZOK`.`TypySeansow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Seanse_Sale1`
  FOREIGN KEY (`Sale_id`)
  REFERENCES `SZOK`.`Sale` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Seanse_PuleBiletow1`
  FOREIGN KEY (`PuleBiletow_id`)
  REFERENCES `SZOK`.`PuleBiletow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`RodzajeBiletow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`RodzajeBiletow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`RodzajeBiletow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idRodzajBiletow_UNIQUE` (`id` ASC),
  UNIQUE INDEX `NazwaBiletu_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Uzytkownicy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Uzytkownicy` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Uzytkownicy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(50) NOT NULL,
  `haslo` VARCHAR(64) NOT NULL,
  `imie` VARCHAR(45) NOT NULL,
  `nazwisko` VARCHAR(45) NOT NULL,
  `telefon` VARCHAR(9) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `dataRejestracji` DATE NOT NULL,
  `czyKobieta` TINYINT NOT NULL,
  `czyZablokowany` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idUzytkownicy_UNIQUE` (`id` ASC),
  UNIQUE INDEX `login_UNIQUE` (`login` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `telefon_UNIQUE` (`telefon` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Role` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Role` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idRole_UNIQUE` (`id` ASC),
  UNIQUE INDEX `nazwa_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Pracownicy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Pracownicy` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Pracownicy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(50) NOT NULL,
  `haslo` VARCHAR(64) NOT NULL,
  `imie` VARCHAR(45) NOT NULL,
  `nazwisko` VARCHAR(45) NOT NULL,
  `telefon` VARCHAR(9) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `Role_id` INT UNSIGNED NOT NULL,
  `czyAktywny` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idPracownicy_UNIQUE` (`id` ASC),
  UNIQUE INDEX `login_UNIQUE` (`login` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `telefon_UNIQUE` (`telefon` ASC),
  INDEX `fk_Pracownicy_Role1_idx` (`Role_id` ASC),
  CONSTRAINT `fk_Pracownicy_Role1`
  FOREIGN KEY (`Role_id`)
  REFERENCES `SZOK`.`Role` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;DROP TABLE IF EXISTS `SZOK`.`Pracownicy` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Pracownicy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(50) NOT NULL,
  `haslo` VARCHAR(64) NOT NULL,
  `imie` VARCHAR(45) NOT NULL,
  `nazwisko` VARCHAR(45) NOT NULL,
  `telefon` VARCHAR(9) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `Role_id` INT UNSIGNED NOT NULL,
  `czyAktywny` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idPracownicy_UNIQUE` (`id` ASC),
  UNIQUE INDEX `login_UNIQUE` (`login` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `telefon_UNIQUE` (`telefon` ASC),
  INDEX `fk_Pracownicy_Role1_idx` (`Role_id` ASC),
  CONSTRAINT `fk_Pracownicy_Role1`
  FOREIGN KEY (`Role_id`)
  REFERENCES `SZOK`.`Role` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`TypyRzedow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`TypyRzedow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`TypyRzedow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idTypyRzedu_UNIQUE` (`id` ASC),
  UNIQUE INDEX `nazwa_UNIQUE` (`nazwa` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Rzedy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Rzedy` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Rzedy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `numerRzedu` INT NOT NULL,
  `Sale_id` INT UNSIGNED NOT NULL,
  `TypyRzedow_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idRzedy_UNIQUE` (`id` ASC),
  INDEX `fk_Rzedy_Sale1_idx` (`Sale_id` ASC),
  INDEX `fk_Rzedy_TypyRzedow1_idx` (`TypyRzedow_id` ASC),
  CONSTRAINT `fk_Rzedy_Sale1`
  FOREIGN KEY (`Sale_id`)
  REFERENCES `SZOK`.`Sale` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Rzedy_TypyRzedow1`
  FOREIGN KEY (`TypyRzedow_id`)
  REFERENCES `SZOK`.`TypyRzedow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Miejsca`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Miejsca` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Miejsca` (
  `id` INT(5) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `pozycja` INT NOT NULL,
  `numerMiejsca` INT NOT NULL,
  `Rzedy_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idMiejsca_UNIQUE` (`id` ASC),
  INDEX `fk_Miejsca_Rzedy1_idx` (`Rzedy_id` ASC),
  CONSTRAINT `fk_Miejsca_Rzedy1`
  FOREIGN KEY (`Rzedy_id`)
  REFERENCES `SZOK`.`Rzedy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Promocje`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Promocje` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Promocje` (
  `id` INT UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `czyKwotowa` TINYINT NOT NULL,
  `wartosc` DECIMAL(5,2) NOT NULL,
  `poczatekPromocji` DATE NOT NULL,
  `koniecPromocji` DATE NOT NULL,
  `czyKobieta` TINYINT NULL,
  `staz` DATE NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idPromocje_UNIQUE` (`id` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Vouchery`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Vouchery` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Vouchery` (
  `id` INT(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `czyKwotowa` TINYINT NOT NULL,
  `wartosc` DECIMAL(5,2) NOT NULL,
  `poczatekPromocji` DATE NOT NULL,
  `koniecPromocji` DATE NOT NULL,
  `losoweCyfry` DECIMAL(3,0) NOT NULL,
  `cyfraKontrolna` DECIMAL(1,0) NOT NULL,
  `czasWygenerowania` DATETIME NOT NULL,
  `czyWykorzystany` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idVouchery_UNIQUE` (`id` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Rezerwacje`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Rezerwacje` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Rezerwacje` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `imie` VARCHAR(45) NOT NULL,
  `nazwisko` VARCHAR(45) NOT NULL,
  `telefon` VARCHAR(9) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `czyOdwiedzajacy` TINYINT NOT NULL,
  `sfinalizowana` TINYINT NOT NULL,
  `Seanse_id` INT UNSIGNED NOT NULL,
  `Uzytkownicy_id` INT UNSIGNED NULL,
  `Pracownicy_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idRezerwacje_UNIQUE` (`id` ASC),
  INDEX `fk_Rezerwacje_Uzytkownicy1_idx` (`Uzytkownicy_id` ASC),
  INDEX `fk_Rezerwacje_Pracownicy1_idx` (`Pracownicy_id` ASC),
  INDEX `fk_Rezerwacje_Seanse1_idx` (`Seanse_id` ASC),
  CONSTRAINT `fk_Rezerwacje_Uzytkownicy1`
  FOREIGN KEY (`Uzytkownicy_id`)
  REFERENCES `SZOK`.`Uzytkownicy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Rezerwacje_Pracownicy1`
  FOREIGN KEY (`Pracownicy_id`)
  REFERENCES `SZOK`.`Pracownicy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Rezerwacje_Seanse1`
  FOREIGN KEY (`Seanse_id`)
  REFERENCES `SZOK`.`Seanse` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`RodzajePlatnosci`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`RodzajePlatnosci` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`RodzajePlatnosci` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(45) NOT NULL,
  `usunieto` TINYINT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idRodzajePlatnosci_UNIQUE` (`id` ASC))
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Tranzakcje`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Tranzakcje` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Tranzakcje` (
  `id` INT(12) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `data` DATETIME NOT NULL,
  `czyOdwiedzajacy` TINYINT NOT NULL,
  `RodzajePlatnosci_id` INT UNSIGNED NOT NULL,
  `Seanse_id` INT UNSIGNED NOT NULL,
  `Uzytkownicy_id` INT UNSIGNED NULL,
  `Pracownicy_id` INT UNSIGNED NULL,
  `Promocje_id` INT UNSIGNED ZEROFILL NULL,
  UNIQUE INDEX `idTranzakcje_UNIQUE` (`id` ASC),
  PRIMARY KEY (`id`),
  INDEX `fk_Tranzakcje_RodzajePlatnosci1_idx` (`RodzajePlatnosci_id` ASC),
  INDEX `fk_Tranzakcje_Uzytkownicy1_idx` (`Uzytkownicy_id` ASC),
  INDEX `fk_Tranzakcje_Pracownicy1_idx` (`Pracownicy_id` ASC),
  INDEX `fk_Tranzakcje_Promocje1_idx` (`Promocje_id` ASC),
  INDEX `fk_Tranzakcje_Seanse1_idx` (`Seanse_id` ASC),
  CONSTRAINT `fk_Tranzakcje_RodzajePlatnosci1`
  FOREIGN KEY (`RodzajePlatnosci_id`)
  REFERENCES `SZOK`.`RodzajePlatnosci` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Tranzakcje_Uzytkownicy1`
  FOREIGN KEY (`Uzytkownicy_id`)
  REFERENCES `SZOK`.`Uzytkownicy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Tranzakcje_Pracownicy1`
  FOREIGN KEY (`Pracownicy_id`)
  REFERENCES `SZOK`.`Pracownicy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Tranzakcje_Promocje1`
  FOREIGN KEY (`Promocje_id`)
  REFERENCES `SZOK`.`Promocje` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Tranzakcje_Seanse1`
  FOREIGN KEY (`Seanse_id`)
  REFERENCES `SZOK`.`Seanse` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Film_ma_RodzajeFilmow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Film_ma_RodzajeFilmow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Film_ma_RodzajeFilmow` (
  `Filmy_id` INT UNSIGNED NOT NULL,
  `RodzajeFilmow_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`Filmy_id`, `RodzajeFilmow_id`),
  INDEX `fk_Filmy_has_RodzajeFilmow_RodzajeFilmow1_idx` (`RodzajeFilmow_id` ASC),
  INDEX `fk_Filmy_has_RodzajeFilmow_Filmy1_idx` (`Filmy_id` ASC),
  CONSTRAINT `fk_Filmy_has_RodzajeFilmow_Filmy1`
  FOREIGN KEY (`Filmy_id`)
  REFERENCES `SZOK`.`Filmy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Filmy_has_RodzajeFilmow_RodzajeFilmow1`
  FOREIGN KEY (`RodzajeFilmow_id`)
  REFERENCES `SZOK`.`RodzajeFilmow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Bilety`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Bilety` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Bilety` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cena` DECIMAL(5,2) NOT NULL,
  `losoweCyfry` DECIMAL(3,0) NOT NULL,
  `cyfraKontrolna` DECIMAL(1,0) NOT NULL,
  `Tranzakcje_id` INT(12) UNSIGNED ZEROFILL NOT NULL,
  `RodzajeBiletow_id` INT UNSIGNED NOT NULL,
  `Miejsca_id` INT(5) UNSIGNED ZEROFILL NOT NULL,
  `Vouchery_id` INT(10) UNSIGNED ZEROFILL NULL,
  `czyWykorzystany` TINYINT NULL,
  `czyAnulowany` TINYINT NULL,
  INDEX `fk_Tranzakcje_has_RodzajeBiletow_RodzajeBiletow1_idx` (`RodzajeBiletow_id` ASC),
  INDEX `fk_Tranzakcje_has_RodzajeBiletow_Tranzakcje1_idx` (`Tranzakcje_id` ASC),
  INDEX `fk_Tranzakcja_ma_Bilet_Miejsca1_idx` (`Miejsca_id` ASC),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `Biletcol_UNIQUE` (`id` ASC),
  INDEX `fk_Bilety_Vouchery1_idx` (`Vouchery_id` ASC),
  CONSTRAINT `fk_Tranzakcje_has_RodzajeBiletow_Tranzakcje1`
  FOREIGN KEY (`Tranzakcje_id`)
  REFERENCES `SZOK`.`Tranzakcje` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Tranzakcje_has_RodzajeBiletow_RodzajeBiletow1`
  FOREIGN KEY (`RodzajeBiletow_id`)
  REFERENCES `SZOK`.`RodzajeBiletow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Tranzakcja_ma_Bilet_Miejsca1`
  FOREIGN KEY (`Miejsca_id`)
  REFERENCES `SZOK`.`Miejsca` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Bilety_Vouchery1`
  FOREIGN KEY (`Vouchery_id`)
  REFERENCES `SZOK`.`Vouchery` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Rezerwacja_ma_Miejsca`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Rezerwacja_ma_Miejsca` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Rezerwacja_ma_Miejsca` (
  `Rezerwacje_id` INT UNSIGNED NOT NULL,
  `Miejsca_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`Rezerwacje_id`, `Miejsca_id`),
  INDEX `fk_Rezerwacje_has_Miejsca_Miejsca1_idx` (`Miejsca_id` ASC),
  INDEX `fk_Rezerwacje_has_Miejsca_Rezerwacje1_idx` (`Rezerwacje_id` ASC),
  CONSTRAINT `fk_Rezerwacje_has_Miejsca_Rezerwacje1`
  FOREIGN KEY (`Rezerwacje_id`)
  REFERENCES `SZOK`.`Rezerwacje` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Rezerwacje_has_Miejsca_Miejsca1`
  FOREIGN KEY (`Miejsca_id`)
  REFERENCES `SZOK`.`Miejsca` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Film_ma_TypySeansow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Film_ma_TypySeansow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Film_ma_TypySeansow` (
  `Filmy_id` INT UNSIGNED NOT NULL,
  `TypySeansow_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`Filmy_id`, `TypySeansow_id`),
  INDEX `fk_Filmy_has_TypySeansow_TypySeansow1_idx` (`TypySeansow_id` ASC),
  INDEX `fk_Filmy_has_TypySeansow_Filmy1_idx` (`Filmy_id` ASC),
  CONSTRAINT `fk_Filmy_has_TypySeansow_Filmy1`
  FOREIGN KEY (`Filmy_id`)
  REFERENCES `SZOK`.`Filmy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Filmy_has_TypySeansow_TypySeansow1`
  FOREIGN KEY (`TypySeansow_id`)
  REFERENCES `SZOK`.`TypySeansow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`PulaBiletow_ma_RodzajeBiletow`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`PulaBiletow_ma_RodzajeBiletow` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`PulaBiletow_ma_RodzajeBiletow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `PuleBiletow_id` INT UNSIGNED NOT NULL,
  `RodzajeBiletow_id` INT UNSIGNED NOT NULL,
  `cena` DECIMAL(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_PulaBiletow_ma_RodzajeBiletow_PuleBiletow1_idx` (`PuleBiletow_id` ASC),
  INDEX `fk_PulaBiletow_ma_RodzajeBiletow_RodzajeBiletow1_idx` (`RodzajeBiletow_id` ASC),
  CONSTRAINT `fk_PulaBiletow_ma_RodzajeBiletow_PuleBiletow1`
  FOREIGN KEY (`PuleBiletow_id`)
  REFERENCES `SZOK`.`PuleBiletow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_PulaBiletow_ma_RodzajeBiletow_RodzajeBiletow1`
  FOREIGN KEY (`RodzajeBiletow_id`)
  REFERENCES `SZOK`.`RodzajeBiletow` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SZOK`.`Seans_ma_Filmy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SZOK`.`Seans_ma_Filmy` ;

CREATE TABLE IF NOT EXISTS `SZOK`.`Seans_ma_Filmy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Seanse_id` INT UNSIGNED NOT NULL,
  `Filmy_id` INT UNSIGNED NOT NULL,
  `kolejnosc` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_Seans_ma_Filmy_Seanse1_idx` (`Seanse_id` ASC),
  INDEX `fk_Seans_ma_Filmy_Filmy1_idx` (`Filmy_id` ASC),
  CONSTRAINT `fk_Seans_ma_Filmy_Seanse1`
  FOREIGN KEY (`Seanse_id`)
  REFERENCES `SZOK`.`Seanse` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Seans_ma_Filmy_Filmy1`
  FOREIGN KEY (`Filmy_id`)
  REFERENCES `SZOK`.`Filmy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
