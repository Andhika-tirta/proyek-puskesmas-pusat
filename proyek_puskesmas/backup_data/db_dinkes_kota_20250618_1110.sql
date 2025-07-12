-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_dinkes_kota
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dokter`
--

DROP TABLE IF EXISTS `dokter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dokter` (
  `id_dokter` int(11) NOT NULL AUTO_INCREMENT,
  `nama_dokter` varchar(100) NOT NULL,
  `spesialisasi` varchar(100) DEFAULT NULL,
  `puskesmas_bertugas` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_dokter`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dokter`
--

LOCK TABLES `dokter` WRITE;
/*!40000 ALTER TABLE `dokter` DISABLE KEYS */;
INSERT INTO `dokter` VALUES (1,'Dr. De Yusep Purnama Satria','Jiwa','Sukamaju'),(2,'Dr. Andhika Tirtaprana Ardi','Kepala Dinas Kesehatan','Sukabumi'),(3,'Dr. Fadillah Afillasaens','Tulang Rusuk ','Mekarsari');
/*!40000 ALTER TABLE `dokter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rekap_kunjungan_harian`
--

DROP TABLE IF EXISTS `rekap_kunjungan_harian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekap_kunjungan_harian` (
  `id_rekap` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal_rekap` date NOT NULL,
  `asal_puskesmas` varchar(100) NOT NULL,
  `jumlah_pasien_baru` int(11) DEFAULT 0,
  `jumlah_kunjungan` int(11) DEFAULT 0,
  `diagnosa_terbanyak` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_rekap`),
  UNIQUE KEY `tanggal_rekap` (`tanggal_rekap`,`asal_puskesmas`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rekap_kunjungan_harian`
--

LOCK TABLES `rekap_kunjungan_harian` WRITE;
/*!40000 ALTER TABLE `rekap_kunjungan_harian` DISABLE KEYS */;
INSERT INTO `rekap_kunjungan_harian` VALUES (1,'2025-09-11','Sukamaju',1,1,'DBD'),(2,'2025-06-17','Sukamaju',1,1,'dpd'),(3,'2025-06-12','Sukamaju',2,2,'wiwqiod'),(4,'2025-06-09','Sukamaju',1,1,'tuur leklok'),(5,'2025-06-07','Sukamaju',19,19,'Insomnia'),(6,'2025-06-17','Mekarsari',1,1,'Amebiasis'),(7,'2025-06-12','Mekarsari',2,2,'Demam'),(8,'2025-06-09','Mekarsari',7,7,'Gastritis (Maag)'),(9,'2025-06-07','Mekarsari',4,5,'Diare');
/*!40000 ALTER TABLE `rekap_kunjungan_harian` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-18 11:10:27
