<?php

class MySQLi_DB extends FileCacheDB {

	private $affected_rows;

	public function try_connect( $srv, $login, $pasw, $db ) {
		return new mysqli( $srv, $login, $pasw, $db );
	}

	public function after_connect() {
		$this->raiseError();
		$this->_con->set_charset( $this->encode );
		$this->_con->autocommit( $this->autocommit );
	}

	public function raiseError() {
		if ( ! $this->_con ) {
			return;
		}
		if ( intval( $this->_con->errno ) < 1 && intval( $this->_con->connect_errno ) < 1 ) {
			return;
		}

		if ( empty( $this->_con->errno ) ) {
			$error_msg = "[" . $this->_con->connect_errno . "] " . $this->_con->connect_error;
		} else {
			$error_msg = "[" . $this->_con->errno . "] " . $this->_con->error;
		}
		//$this->_con->close();
		throw new CashError( $error_msg );
	}

	public function start_tran() {
		//
	}

	public function commit() {
		$this->_con->commit();
	}

	public function rollback() {
		$this->_con->rollback();
	}

	public function last_id() {
		return $this->_con->insert_id;
	}

	public function affect() {
		if ( $this->_stmt->affected_rows > 1 ) {
			$this->affected_rows = $this->_stmt->affected_rows;
		}

		return $this->affected_rows;
	}

	protected function _exec( $sql, $args ) {
		unset( $this->_stmt );
		unset( $this->_con->results );
		if ( ! $this->_con ) {
			$this->raiseError();
		}
		$this->_stmt = $this->_con->prepare( $sql );
		if ( ! $this->_stmt ) {
			$this->raiseError();
		}
		//биндим параменты
		//tnx http://www.php.net/manual/en/mysqli-stmt.bind-param.php#100879
		if ( is_array( @$args[1] ) ) {
			$args = $args[1];
		}
		$params = $args;
		$refs   = array();
		array_unshift( $params, str_repeat( 's', count( $args ) ) );
		if ( strnatcmp( phpversion(), '5.3' ) >= 0 ) {
			foreach ( $params as $key => $value ) {
				$refs[ $key ] = &$params[ $key ];
			}
		} else {
			$refs = $params;
		}
		call_user_func_array( array( $this->_stmt, 'bind_param' ), $refs );

		if ( ! $this->_stmt->execute() ) {
			$this->raiseError();
		}


		//получаем результаты
		$meta = $this->_stmt->result_metadata();
		$ret  = null;
		if ( $meta !== false ) {
			$ret  = array();
			$cols = array();
			//tnx http://www.php.net/manual/en/mysqli-stmt.fetch.php#72720
			$fields = $meta->fetch_fields();
			foreach ( $fields as $field ) {
				$cols[] = &$this->_con->results[ $field->name ];
			}
			call_user_func_array( array( $this->_stmt, 'bind_result' ), $cols );
			$key = 0;
			while ( $this->_stmt->fetch() != null ) {
				$num_col = 0;
				foreach ( $this->_con->results as $result ) {
					$ret[ $key ][ $fields[ $num_col ]->name ] = $result;
					$num_col ++;
				}
				$key ++;
			}
		}

		$this->affected_rows = $this->_stmt->affected_rows;
		$this->_stmt->close();

		return $ret;
	}

	public function escape( $s ) {
		return $this->_con->real_escape_string( $s );
	}

	public function getRealSql( $sql, $args ) {
		$sql = str_replace( "?", "%s", $sql );
		$sql = vsprintf( $sql, $args );

		return $sql;
	}

	public function getUpperFnc() {
		return "UPPER";
	}

	public function getDateFnc() {
		return "NOW()";
	}

	public function getDateFormatFnc( $format, $col ) {
		return "DATE_FORMAT(" . $col . ", '" . $format . "')";
	}

	public function getDateAddFnc( $col, $interval ) {
		return "DATE_ADD(" . $col . ", INTERVAL " . $interval . ")";
	}

	function __destruct() {
		if ( is_resource( $this->_con ) ) {
			$this->_con->close;
		}
	}
}

?>