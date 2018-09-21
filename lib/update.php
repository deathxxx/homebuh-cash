<?php
class DbUpdate {
  private $db, $lng, $usr;
  
  function __construct($_db, $_lng, $_usr) {
    $this->db = $_db;
    $this->lng = $_lng;
    $this->usr = $_usr;
  }

  public function exec($sql) {
    try {
      return $this->db->exec($sql);
    }
    catch(Throwable $t) {
      return false;
    }
    catch(Exception $e) {
      return false;
    }
    return false;
  }
  
  public function select($sql) {
    try {
      return $this->db->element($sql);
    }
    catch(Throwable $t) {
      return false;
    }
    catch(Exception $e) {
      return false;
    }
    return false;
  }
  
  public function updateData($file_ver, $db_ver) {
    $this->db->start_tran();
    
    if($file_ver >= 1.05 && $db_ver < 1.05) $this->updateData_v1_050();
    if($file_ver >= 1.055 && $db_ver < 1.055) $this->updateData_v1_055();
    if($file_ver >= 1.061 && $db_ver < 1.061) $this->updateData_v1_061();
    if($file_ver >= 1.081 && $db_ver < 1.081) $this->updateData_v1_081();
    
    if($db_ver == 0) {
      $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "version", $this->lng->get(221), $file_ver );
    } else {    
      $this->db->exec("UPDATE cashes_setting SET value = ? WHERE name = ?", $file_ver, "version" );
    }
    
    $this->db->commit();
  } //updateData
  
  public function updateData_v1_081() {
    $this->exec("INSERT INTO cashes_setting(name, descr, value) VALUES('fpd', '".$this->lng->get(242)."', 'http://skahin.ru/api/cash/')");
    $this->exec("ALTER TABLE cashes ADD fpd INTEGER");
    $this->db->exec('CREATE INDEX "XIF_CASHES_FPD" on cashes (fpd ASC)');
  } //updateData_v1_081
    
  public function updateData_v1_050() {
    $this->exec("INSERT INTO cashes_setting(name, descr, value) VALUES('ocr', '".$this->lng->get(226)."', '')");
    $this->exec("ALTER TABLE cashes ADD geo_pos VARCHAR(64)");
  } //updateData_v1_050
  
  public function updateData_v1_055() {
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "proc_analiz",  $this->lng->get(229), "1.5" );
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "secure_user",  $this->lng->get(230), "0" );
  } //updateData_v1_050
  
  public function updateData_v1_061() {
    $this->exec('DROP TABLE IF EXISTS "cashes_goal"');
    $this->db->exec('CREATE TABLE "cashes_goal" (
        "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        "nmcl_id" INTEGER NOT NULL,
        "db_id" INTEGER NOT NULL,
        "usr_id" INTEGER NOT NULL,
        "plan_date" DATE,
        "order_id" INTEGER,
        "plan" REAL,
        "qnt" REAL,
        "fact_date" DATE
    )');
    $this->db->exec('CREATE UNIQUE INDEX "XPK_CASHES_GOAL" on cashes_goal (id ASC)');
    $this->db->exec('CREATE INDEX "XIF_CASHES_GOAL_DU" on cashes_goal (db_id ASC, usr_id ASC)');
  } //updateData_v1_061
  
  public function createData($pasw) {  
    $login = "admin";
    if(empty($pasw)) $pasw = $login;
  
    $this->db->start_tran();
    
    $this->db->exec("INSERT INTO db(name) VALUES(?)", $this->lng->get(203) );
    $db_id = $this->db->last_id();
    
    $this->db->exec(
          "INSERT INTO `users` (id, bd_id, login, pasw, `read`, `write`, analiz, setting, oper_date)
		      VALUES( NULL, ?, ?, ?, ?, ?, ?, ?, ". $this->db->getDateFnc() ." )", 
          $db_id , $login, $this->usr->hash_pasw($pasw), 1, 1, 1, 1);
    $usr_id = $this->db->last_id();
    
    $this->db->exec("INSERT INTO cashes_type(name) VALUES(?)", $this->lng->get(204));
    $this->db->exec("INSERT INTO cashes_type(name) VALUES(?)", $this->lng->get(205));
    $this->db->exec("INSERT INTO cashes_type(name) VALUES(?)", $this->lng->get(206));
    
    global $settings;
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "site_name",    $this->lng->get(207), $this->lng->get(208));
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "mail",         $this->lng->get(209), "");
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "version",      $this->lng->get(221), $settings['version'] );
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "round",        $this->lng->get(223), 0 );
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "ocr",          $this->lng->get(226), "" );
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "proc_analiz",  $this->lng->get(229), "1.5" );
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", "secure_user",  $this->lng->get(230), "0" );
    $this->db->exec("INSERT INTO cashes_setting(name, descr, value) VALUES(?, ?, ?)", 'fpd',          $this->lng->get(242), 'http://skahin.ru/api/cash/');
    
    $this->db->exec("INSERT INTO cashes_group(name) VALUES(?)", $this->lng->get(210)); 
    $this->db->exec("INSERT INTO cashes_group(name) VALUES(?)", $this->lng->get(211));
    $this->db->exec("INSERT INTO cashes_group(name) VALUES(?)", $this->lng->get(212));
    
    foreach($this->lng->getCurrencys() as $cs ) {
      $this->db->exec("INSERT INTO currency(name,rate,sign,short_name) VALUES(?,?,?,?)", $cs[0], $cs[1], $cs[2], $cs[3] );
    }
    
    $this->db->commit();
  } //createData
  
  public function clean() {
    $this->db->start_tran();
    $this->db->exec('delete from cashes');
    $this->db->exec('delete from cashes_nom');
    $this->db->exec('delete from cashes_group_plan');
    $this->db->exec('delete from cashes_group');
    $this->db->exec('delete from cashes_goal');
    $this->db->exec('delete from cashes_org');
    $this->db->exec('delete from cashes_setting');
    $this->db->exec('delete from cashes_type');
    $this->db->exec('delete from currency');
    $this->db->exec('delete from users');
    $this->db->exec('delete from db');
    $this->db->commit();
  } //clean
  
  public function difChange() {
    //
  } //difChange
  
} //DbUpdate


class Update {
  private $db, $lng, $usr;
  
  private $db_upd;
  
  public $file_ver, $db_ver;

  function __construct($_db, $_lng, $_usr) {
    $this->db = $_db;
    $this->lng = $_lng;
    $this->usr = $_usr;
    
    $this->db_upd = new DbUpdate($this->db, $this->lng, $this->usr);
  }
  
  public function setup($pasw) {
    $this->db_upd->createData($pasw);
    return true;
  }
  
  public function update() {
    $this->db_upd->updateData($this->file_ver, $this->db_ver);
    return true;
  }
  
  public function needSetup() {
    $exst = $this->db_upd->select("SELECT 1 as exst FROM db");
    return ( 0 == intval($exst) );
  } //needSetup
  
  public function needUpdate() {
    global $settings;
    $this->file_ver = doubleval( $settings['version'] );
    $this->db_ver = doubleval( $this->db->element("SELECT value FROM cashes_setting WHERE name = 'version'") );
    
    return ( $this->file_ver > $this->db_ver ); //TODO
  } //needUpdate
  
  
} //Update
