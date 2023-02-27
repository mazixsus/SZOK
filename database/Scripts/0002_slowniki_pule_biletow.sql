-- -----------------------------------------------------
-- Data for table `SZOK`.`KategorieWiekowe`
-- -----------------------------------------------------
INSERT INTO `kategoriewiekowe` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'N/A', NULL),
(2, '7+', NULL),
(3, '12+', NULL),
(4, '15+', NULL),
(5, '18+', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`RodzajeFilmow`
-- -----------------------------------------------------
INSERT INTO `rodzajefilmow` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Film akcji', NULL),
(2, 'Przygodowy', NULL),
(3, 'Sci-Fi', NULL),
(4, 'Fantasy', NULL),
(5, 'Komedia', NULL),
(6, 'Romans', NULL),
(7, 'Horror', NULL),
(8, 'Thriller', NULL),
(9, 'Dramat', NULL),
(10, 'Film animowany', NULL),
(11, 'Film biograficzny', NULL),
(12, 'Film historyczny', NULL),
(13, 'Western', NULL),
(14, 'Musical', NULL),
(15, 'Film dokumentalny', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`WydarzeniaSpecjalne`
-- -----------------------------------------------------
INSERT INTO `wydarzeniaspecjalne` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Maraton', NULL),
(2, 'Lejdis Night', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`TypySeansow`
-- -----------------------------------------------------
INSERT INTO `typyseansow` (`id`, `nazwa`, `usunieto`) VALUES
(1, '2D Napisy', NULL),
(2, '3D Napisy', NULL),
(3, '2D Dubbing', NULL),
(4, '3D Dubbing', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`RodzajeBiletow`
-- -----------------------------------------------------
INSERT INTO `rodzajebiletow` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Normalny', NULL),
(2, 'Ulgowy', NULL),
(3, 'Studencki', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`Role`
-- -----------------------------------------------------
INSERT INTO `role` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Administrator', NULL),
(2, 'Kierownik', NULL),
(3, 'Pracownik', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`TypyRzedow`
-- -----------------------------------------------------
INSERT INTO `typyrzedow` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Do rezerwacji', NULL),
(2, 'Tylko do zakupu', NULL);


-- -----------------------------------------------------
-- Data for table `SZOK`.`RodzajePlatnosci`
-- -----------------------------------------------------
INSERT INTO `rodzajeplatnosci` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Karta płatnicza', NULL),
(2, 'Gotówka', NULL),
(3, 'Internet', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`PuleBiletow`
-- -----------------------------------------------------
INSERT INTO `pulebiletow` (`id`, `nazwa`, `usunieto`) VALUES
(1, 'Zwykłe 2d', NULL),
(2, 'Zwykłe 3d', NULL),
(3, 'Weekend 2d', NULL),
(4, 'Weekend 3d', NULL);

-- -----------------------------------------------------
-- Data for table `SZOK`.`PulaBiletow_ma_RodzajeBiletow`
-- -----------------------------------------------------
INSERT INTO `pulabiletow_ma_rodzajebiletow` (`id`, `PuleBiletow_id`, `RodzajeBiletow_id`, `cena`) VALUES
(1, 1, 1, '20.00'),
(2, 1, 2, '17.00'),
(3, 1, 3, '15.00'),
(4, 2, 1, '30.00'),
(5, 2, 2, '25.00'),
(6, 2, 3, '20.00'),
(7, 3, 1, '25.00'),
(8, 3, 2, '22.00'),
(9, 3, 3, '19.00'),
(10, 4, 1, '35.00'),
(11, 4, 2, '30.00'),
(12, 4, 3, '27.00');

COMMIT;