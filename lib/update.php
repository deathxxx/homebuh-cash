<?php
require_once ('DbUpdate.php');

class Update {
	private $db, $lng, $usr;

	private $db_upd;

	public $file_ver, $db_ver;

	function __construct( $_db, $_lng, $_usr ) {
		$this->db  = $_db;
		$this->lng = $_lng;
		$this->usr = $_usr;

		$this->db_upd = new DbUpdate( $this->db, $this->lng, $this->usr );
	}

	public function setup( $pasw ) {

		return 	$this->db_upd->createData( $pasw );

	}

	public function update() {
		$this->db_upd->updateData( $this->file_ver, $this->db_ver );

		return true;
	}

	public function needSetup() {
		$exst = $this->db_upd->select( "SELECT 1 as exst FROM db" );

		return ( 0 == intval( $exst ) );
	} //needSetup

	public function needUpdate() {
		global $settings;
		$this->file_ver = doubleval( $settings['version'] );
		$this->db_ver   = doubleval( $this->db->element( "SELECT value FROM cashes_setting WHERE name = 'version'" ) );

		return ( $this->file_ver > $this->db_ver ); //TODO
	} //needUpdate


} //Update
