-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Авг 03 2019 г., 12:37
-- Версия сервера: 5.6.35
-- Версия PHP: 7.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `srvc`
--

-- --------------------------------------------------------

--
-- Структура таблицы `main`
--

CREATE TABLE `main` (
  `id` int(11) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `battery.charge` int(11) NOT NULL,
  `battery.charge.low` int(11) NOT NULL,
  `battery.charge.warning` int(11) NOT NULL,
  `battery.date` date DEFAULT NULL,
  `battery.mfr.date` date DEFAULT NULL,
  `battery.runtime` int(11) NOT NULL,
  `battery.type` text NOT NULL,
  `battery.voltage` float NOT NULL,
  `device.mfr` text NOT NULL,
  `device.model` text NOT NULL,
  `device.serial` text NOT NULL,
  `device.type` text NOT NULL,
  `driver.name` text NOT NULL,
  `driver.parameter.bus` text NOT NULL,
  `driver.parameter.pollfreq` int(11) NOT NULL,
  `driver.parameter.pollinterval` int(11) NOT NULL,
  `driver.parameter.port` int(11) NOT NULL,
  `driver.parameter.productid` text NOT NULL,
  `driver.parameter.vendorid` text NOT NULL,
  `driver.version` text NOT NULL,
  `driver.version.data` text NOT NULL,
  `driver.version.internal` text NOT NULL,
  `input.frequency` int(11) DEFAULT NULL,
  `input.voltage` int(11) NOT NULL,
  `input.voltage.nominal` int(11) NOT NULL,
  `output.frequency` int(11) NOT NULL,
  `output.voltage` int(11) NOT NULL,
  `output.voltage.nominal` int(11) NOT NULL,
  `ups.beeper.status` text NOT NULL,
  `ups.date` date NOT NULL,
  `ups.delay.shutdown` int(11) NOT NULL,
  `ups.load` int(11) NOT NULL,
  `ups.mfr` text NOT NULL,
  `ups.model` text NOT NULL,
  `ups.productid` text NOT NULL,
  `ups.serial` text NOT NULL,
  `ups.status` text NOT NULL,
  `ups.test.result` text NOT NULL,
  `ups.timer.shutdown` int(11) NOT NULL,
  `ups.vendorid` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `main`
--
ALTER TABLE `main`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `main`
--
ALTER TABLE `main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
