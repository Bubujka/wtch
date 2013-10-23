<?php
# edit me >>>>
define('DB_HOST', 'localhost');
define('DB_NAME', 'my_another_blog');
define('DB_USER', 'root');
define('DB_PASS', 'p4ssw0rd');
define('DB_TABLE', 'buwatch');
define('COOKIE_NAME', '__watch_user_id');
# <<<< edit me

function show_img_and_die(){
  header( 'Content-type: image/gif' );
  echo chr(71).chr(73).chr(70).chr(56).chr(57).chr(97).
       chr(1).chr(0).chr(1).chr(0).chr(128).chr(0).
       chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).
       chr(33).chr(249).chr(4).chr(1).chr(0).chr(0).
       chr(0).chr(0).chr(44).chr(0).chr(0).chr(0).chr(0).
       chr(1).chr(0).chr(1).chr(0).chr(0).chr(2).chr(2).
       chr(68).chr(1).chr(0).chr(59);
  exit;
}

function db(){
  static $db;
  if(!$db){
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $db->query('set names utf8');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  return $db;
}

function q($str){
  return db()->quote($str);
}

function one($q){
  return db()->query($q)->fetch();
}

function unique_user_id(){
  $t = md5(microtime(true)." ".rand(1, 100000).time()."Лол");
  if(one('select id from `'.DB_TABLE.'` where user_id = '.q($t).' limit 1'))
    return unique_user_id();
  return $t;
}

if(!isset($_COOKIE[COOKIE_NAME]) or !preg_match('/^[a-z0-9]+$/', $_COOKIE[COOKIE_NAME])){
  $user_id = unique_user_id();
  setcookie(COOKIE_NAME, $user_id, time() + (3600 * 24 * 365 * 2), '/');
}else{
  $user_id = $_COOKIE[COOKIE_NAME];
}
if(!isset($_GET['href']))
  show_img_and_die();
if(!isset($_GET['referrer']))
  show_img_and_die();


$q = 'insert into `'.DB_TABLE.'`
      (user_id, href, referrer)
      values
      ('.q($user_id).', '.q($_GET['href']).', '.q($_GET['referrer']).')';

try{
  db()->query($q);
}catch(Exception $e){ // Возможно просто таблицы нет =_=
  db()->query('
    CREATE TABLE IF NOT EXISTS `'.DB_TABLE.'` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `user_id` varchar(255) DEFAULT NULL,
      `href` varchar(2048) DEFAULT NULL,
      `referrer` varchar(2048) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `time` (`user_id`,`time`)
    ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;');
  db()->query($q);
}

show_img_and_die();
