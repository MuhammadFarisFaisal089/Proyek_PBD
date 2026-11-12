CREATE DATABASE  IF NOT EXISTS `deklarasi_stok` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `deklarasi_stok`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: deklarasi_stok
-- ------------------------------------------------------
-- Server version	26.7.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '55b183f9-9c86-11f1-af40-e89744c1450e:1-625';

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `idbarang` int NOT NULL AUTO_INCREMENT,
  `jenis` char(1) NOT NULL,
  `nama` varchar(45) NOT NULL,
  `idsatuan` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `harga` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`idbarang`),
  KEY `fk_barang_satuan` (`idsatuan`),
  CONSTRAINT `fk_barang_satuan` FOREIGN KEY (`idsatuan`) REFERENCES `satuan` (`idsatuan`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (1,'A','Beras Premium 5kg',3,1,65000),(2,'B','Minyak Goreng 1L',1,1,14000),(3,'A','Mie Instan Dus',2,1,110000),(4,'B','Gula Merah',3,0,8000);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penerimaan`
--

DROP TABLE IF EXISTS `detail_penerimaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penerimaan` (
  `iddetail_penerimaan` bigint NOT NULL AUTO_INCREMENT,
  `idpenerimaan` bigint NOT NULL,
  `barang_idbarang` int NOT NULL,
  `jumlah_terima` int NOT NULL,
  `harga_satuan_terima` int NOT NULL,
  `sub_total_terima` int NOT NULL,
  PRIMARY KEY (`iddetail_penerimaan`),
  KEY `fk_detail_penerimaan_penerimaan` (`idpenerimaan`),
  KEY `fk_detail_penerimaan_barang` (`barang_idbarang`),
  CONSTRAINT `fk_detail_penerimaan_barang` FOREIGN KEY (`barang_idbarang`) REFERENCES `barang` (`idbarang`),
  CONSTRAINT `fk_detail_penerimaan_penerimaan` FOREIGN KEY (`idpenerimaan`) REFERENCES `penerimaan` (`idpenerimaan`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penerimaan`
--

LOCK TABLES `detail_penerimaan` WRITE;
/*!40000 ALTER TABLE `detail_penerimaan` DISABLE KEYS */;
INSERT INTO `detail_penerimaan` VALUES (4,4,3,40,110000,4400000),(5,5,1,10,65000,650000),(6,6,2,100,20,2000),(7,7,3,10,110000,1100000),(8,8,1,10,65000,650000),(9,9,3,10,110000,1100000),(10,10,3,50,80000,4000000),(11,11,3,5,110000,550000),(12,12,3,5,55000,275000);
/*!40000 ALTER TABLE `detail_penerimaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_pengadaan`
--

DROP TABLE IF EXISTS `detail_pengadaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_pengadaan` (
  `iddetail_pengadaan` bigint NOT NULL AUTO_INCREMENT,
  `harga_satuan` int NOT NULL,
  `jumlah` int NOT NULL,
  `sub_total` int NOT NULL,
  `idbarang` int NOT NULL,
  `idpengadaan` bigint NOT NULL,
  PRIMARY KEY (`iddetail_pengadaan`),
  KEY `fk_detail_pengadaan_barang` (`idbarang`),
  KEY `fk_detail_pengadaan_pengadaan` (`idpengadaan`),
  CONSTRAINT `fk_detail_pengadaan_barang` FOREIGN KEY (`idbarang`) REFERENCES `barang` (`idbarang`),
  CONSTRAINT `fk_detail_pengadaan_pengadaan` FOREIGN KEY (`idpengadaan`) REFERENCES `pengadaan` (`idpengadaan`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_pengadaan`
--

LOCK TABLES `detail_pengadaan` WRITE;
/*!40000 ALTER TABLE `detail_pengadaan` DISABLE KEYS */;
INSERT INTO `detail_pengadaan` VALUES (3,20,100,2000,2,3),(4,80000,50,4000000,3,4),(5,110000,50,5500000,3,5),(6,65000,10,650000,1,6),(7,65000,10,650000,1,7),(8,110000,10,1100000,3,8),(9,110000,10,1100000,3,9),(10,55000,10,550000,3,10),(11,100000,10,1000000,3,11);
/*!40000 ALTER TABLE `detail_pengadaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_penjualan`
--

DROP TABLE IF EXISTS `detail_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penjualan` (
  `iddetail_penjualan` bigint NOT NULL AUTO_INCREMENT,
  `harga_satuan` int NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` int NOT NULL,
  `penjualan_idpenjualan` int NOT NULL,
  `idbarang` int NOT NULL,
  PRIMARY KEY (`iddetail_penjualan`),
  KEY `fk_detail_penjualan_penjualan` (`penjualan_idpenjualan`),
  KEY `fk_detail_penjualan_barang` (`idbarang`),
  CONSTRAINT `fk_detail_penjualan_barang` FOREIGN KEY (`idbarang`) REFERENCES `barang` (`idbarang`),
  CONSTRAINT `fk_detail_penjualan_penjualan` FOREIGN KEY (`penjualan_idpenjualan`) REFERENCES `penjualan` (`idpenjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_penjualan`
--

LOCK TABLES `detail_penjualan` WRITE;
/*!40000 ALTER TABLE `detail_penjualan` DISABLE KEYS */;
INSERT INTO `detail_penjualan` VALUES (4,126500,10,1265000,3,3),(5,16100,15,241500,4,2),(6,126500,5,632500,5,3),(7,126500,105,13282500,6,3);
/*!40000 ALTER TABLE `detail_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_retur_barang`
--

DROP TABLE IF EXISTS `detail_retur_barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_retur_barang` (
  `iddetail_retur` int NOT NULL AUTO_INCREMENT,
  `jumlah` int NOT NULL,
  `alasan` varchar(200) NOT NULL,
  `idretur` bigint NOT NULL,
  `iddetail_penerimaan` bigint NOT NULL,
  PRIMARY KEY (`iddetail_retur`),
  KEY `fk_detail_retur_barang_retur_barang` (`idretur`),
  KEY `fk_detail_retur_barang_detail_penerimaan` (`iddetail_penerimaan`),
  CONSTRAINT `fk_detail_retur_barang_detail_penerimaan` FOREIGN KEY (`iddetail_penerimaan`) REFERENCES `detail_penerimaan` (`iddetail_penerimaan`),
  CONSTRAINT `fk_detail_retur_barang_retur_barang` FOREIGN KEY (`idretur`) REFERENCES `retur_barang` (`idretur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_retur_barang`
--

LOCK TABLES `detail_retur_barang` WRITE;
/*!40000 ALTER TABLE `detail_retur_barang` DISABLE KEYS */;
/*!40000 ALTER TABLE `detail_retur_barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kartu_stok`
--

DROP TABLE IF EXISTS `kartu_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kartu_stok` (
  `idkartu_stok` bigint NOT NULL AUTO_INCREMENT,
  `jenis_transaksi` char(1) NOT NULL,
  `masuk` int NOT NULL DEFAULT '0',
  `keluar` int NOT NULL DEFAULT '0',
  `stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idtransaksi` bigint NOT NULL,
  `idbarang` int NOT NULL,
  PRIMARY KEY (`idkartu_stok`),
  KEY `fk_kartu_stok_barang` (`idbarang`),
  CONSTRAINT `fk_kartu_stok_barang` FOREIGN KEY (`idbarang`) REFERENCES `barang` (`idbarang`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kartu_stok`
--

LOCK TABLES `kartu_stok` WRITE;
/*!40000 ALTER TABLE `kartu_stok` DISABLE KEYS */;
INSERT INTO `kartu_stok` VALUES (6,'M',40,0,40,'2026-11-12 03:55:45',4,3),(7,'K',0,10,30,'2026-11-12 03:57:35',3,3),(8,'M',10,0,10,'2026-11-12 04:21:27',5,1),(9,'M',100,0,100,'2026-11-12 04:39:08',6,2),(10,'M',10,0,40,'2026-11-12 04:39:21',7,3),(11,'M',10,0,20,'2026-11-12 04:39:36',8,1),(12,'M',10,0,50,'2026-11-12 04:39:52',9,3),(13,'M',50,0,100,'2026-11-12 04:40:06',10,3),(14,'K',0,15,85,'2026-11-12 04:40:54',4,2),(15,'M',5,0,105,'2026-11-12 06:30:58',11,3),(16,'K',0,5,100,'2026-11-12 06:32:33',5,3),(17,'M',5,0,105,'2026-11-12 13:48:31',12,3),(18,'K',0,105,0,'2026-11-12 13:50:52',6,3);
/*!40000 ALTER TABLE `kartu_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `margin_penjualan`
--

DROP TABLE IF EXISTS `margin_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `margin_penjualan` (
  `idmargin_penjualan` int NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `persen` double NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `iduser` int NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idmargin_penjualan`),
  KEY `fk_margin_user` (`iduser`),
  CONSTRAINT `fk_margin_user` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `margin_penjualan`
--

LOCK TABLES `margin_penjualan` WRITE;
/*!40000 ALTER TABLE `margin_penjualan` DISABLE KEYS */;
INSERT INTO `margin_penjualan` VALUES (1,'2026-11-12 03:28:37',10,1,1,'2026-11-12 03:28:37'),(2,'2026-11-12 03:28:37',15,1,1,'2026-11-12 03:28:37');
/*!40000 ALTER TABLE `margin_penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penerimaan`
--

DROP TABLE IF EXISTS `penerimaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penerimaan` (
  `idpenerimaan` bigint NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` char(1) NOT NULL,
  `idpengadaan` bigint NOT NULL,
  `iduser` int NOT NULL,
  PRIMARY KEY (`idpenerimaan`),
  KEY `fk_penerimaan_pengadaan` (`idpengadaan`),
  KEY `fk_penerimaan_user` (`iduser`),
  CONSTRAINT `fk_penerimaan_pengadaan` FOREIGN KEY (`idpengadaan`) REFERENCES `pengadaan` (`idpengadaan`),
  CONSTRAINT `fk_penerimaan_user` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan`
--

LOCK TABLES `penerimaan` WRITE;
/*!40000 ALTER TABLE `penerimaan` DISABLE KEYS */;
INSERT INTO `penerimaan` VALUES (4,'2026-11-12 03:55:45','P',5,3),(5,'2026-11-12 04:21:27','S',7,3),(6,'2026-11-12 04:39:08','S',3,3),(7,'2026-11-12 04:39:21','S',8,3),(8,'2026-11-12 04:39:36','S',6,3),(9,'2026-11-12 04:39:52','S',5,3),(10,'2026-11-12 04:40:06','S',4,3),(11,'2026-11-12 06:30:58','P',9,3),(12,'2026-11-12 13:48:31','P',10,3);
/*!40000 ALTER TABLE `penerimaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengadaan`
--

DROP TABLE IF EXISTS `pengadaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengadaan` (
  `idpengadaan` bigint NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_iduser` int NOT NULL,
  `status` char(1) NOT NULL,
  `vendor_idvendor` int NOT NULL,
  `subtotal_nilai` int NOT NULL DEFAULT '0',
  `ppn` int NOT NULL DEFAULT '0',
  `total_nilai` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`idpengadaan`),
  KEY `fk_pengadaan_user` (`user_iduser`),
  KEY `fk_pengadaan_vendor` (`vendor_idvendor`),
  CONSTRAINT `fk_pengadaan_user` FOREIGN KEY (`user_iduser`) REFERENCES `user` (`iduser`),
  CONSTRAINT `fk_pengadaan_vendor` FOREIGN KEY (`vendor_idvendor`) REFERENCES `vendor` (`idvendor`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengadaan`
--

LOCK TABLES `pengadaan` WRITE;
/*!40000 ALTER TABLE `pengadaan` DISABLE KEYS */;
INSERT INTO `pengadaan` VALUES (3,'2026-11-12 03:52:26',1,'S',3,2000,0,2000),(4,'2026-11-12 03:53:47',1,'S',2,4000000,0,4000000),(5,'2026-11-12 03:55:12',3,'S',3,5500000,0,5500000),(6,'2026-11-12 04:16:26',1,'S',3,650000,0,650000),(7,'2026-11-12 04:20:39',3,'S',3,650000,0,650000),(8,'2026-11-12 04:38:15',1,'S',1,1100000,0,1100000),(9,'2026-11-12 06:30:09',3,'P',1,1100000,0,1100000),(10,'2026-11-12 06:37:05',1,'P',1,550000,0,550000),(11,'2026-11-12 06:39:41',3,'P',3,1000000,0,1000000),(12,'2026-11-12 13:44:18',1,'P',3,0,0,0);
/*!40000 ALTER TABLE `pengadaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan` (
  `idpenjualan` int NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal_nilai` int NOT NULL DEFAULT '0',
  `ppn` int NOT NULL DEFAULT '0',
  `total_nilai` int NOT NULL DEFAULT '0',
  `iduser` int NOT NULL,
  `idmargin_penjualan` int NOT NULL,
  PRIMARY KEY (`idpenjualan`),
  KEY `fk_penjualan_user` (`iduser`),
  KEY `fk_penjualan_margin` (`idmargin_penjualan`),
  CONSTRAINT `fk_penjualan_margin` FOREIGN KEY (`idmargin_penjualan`) REFERENCES `margin_penjualan` (`idmargin_penjualan`),
  CONSTRAINT `fk_penjualan_user` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (3,'2026-11-12 03:57:35',1265000,0,1265000,2,2),(4,'2026-11-12 04:40:54',241500,0,241500,2,2),(5,'2026-11-12 06:32:33',632500,0,632500,2,2),(6,'2026-11-12 13:50:52',13282500,0,13282500,2,2);
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retur_barang`
--

DROP TABLE IF EXISTS `retur_barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `retur_barang` (
  `idretur` bigint NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idpenerimaan` bigint NOT NULL,
  `iduser` int NOT NULL,
  PRIMARY KEY (`idretur`),
  KEY `fk_retur_barang_penerimaan` (`idpenerimaan`),
  KEY `fk_retur_barang_user` (`iduser`),
  CONSTRAINT `fk_retur_barang_penerimaan` FOREIGN KEY (`idpenerimaan`) REFERENCES `penerimaan` (`idpenerimaan`),
  CONSTRAINT `fk_retur_barang_user` FOREIGN KEY (`iduser`) REFERENCES `user` (`iduser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retur_barang`
--

LOCK TABLES `retur_barang` WRITE;
/*!40000 ALTER TABLE `retur_barang` DISABLE KEYS */;
/*!40000 ALTER TABLE `retur_barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `idrole` int NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(100) NOT NULL,
  PRIMARY KEY (`idrole`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'Administrator'),(2,'Kasir'),(3,'Staf Gudang');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satuan`
--

DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `satuan` (
  `idsatuan` int NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(45) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`idsatuan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuan`
--

LOCK TABLES `satuan` WRITE;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES (1,'Pcs',1),(2,'Box',1),(3,'Kg',1),(5,'Meter',1);
/*!40000 ALTER TABLE `satuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `iduser` int NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(100) NOT NULL,
  `idrole` int NOT NULL,
  PRIMARY KEY (`iduser`),
  KEY `fk_user_role` (`idrole`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`idrole`) REFERENCES `role` (`idrole`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'admin','$2y$12$cqe6/799.Eny6H3a4iJWQOTv75k4xpQpXtzn1pQ82cUKR0zw5WDUO',1),(2,'kasir1','$2y$12$.xvQVnvCRp/Vr//wCDlKkOs7aWj/H7MExhg0HSfCghQUIHoSLYbdu',2),(3,'gudang1','$2y$12$rNpU/JJ80KTgh.W4x.EBj.Ll7nYc0Kq/116zMosklSDVN5bkEDc6m',3);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor` (
  `idvendor` int NOT NULL AUTO_INCREMENT,
  `nama_vendor` varchar(100) NOT NULL,
  `badan_hukum` char(1) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`idvendor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor`
--

LOCK TABLES `vendor` WRITE;
/*!40000 ALTER TABLE `vendor` DISABLE KEYS */;
INSERT INTO `vendor` VALUES (1,'PT Sumber Makmur','Y','A'),(2,'CV Maju Bersama','Y','A'),(3,'Toko Kelontong Jaya','N','A');
/*!40000 ALTER TABLE `vendor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_katalog_barang`
--

DROP TABLE IF EXISTS `view_katalog_barang`;
/*!50001 DROP VIEW IF EXISTS `view_katalog_barang`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_katalog_barang` AS SELECT 
 1 AS `idbarang`,
 1 AS `nama`,
 1 AS `jenis`,
 1 AS `nama_satuan`,
 1 AS `harga`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_katalog_barang`
--

/*!50001 DROP VIEW IF EXISTS `view_katalog_barang`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_katalog_barang` AS select `b`.`idbarang` AS `idbarang`,`b`.`nama` AS `nama`,`b`.`jenis` AS `jenis`,`s`.`nama_satuan` AS `nama_satuan`,`b`.`harga` AS `harga`,`b`.`status` AS `status` from (`barang` `b` join `satuan` `s` on((`b`.`idsatuan` = `s`.`idsatuan`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12 22:05:31
