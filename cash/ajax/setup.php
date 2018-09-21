<?php

require_once( '../lib/init.php' );

$ret = array( 'success' => false, 'msg' => $lng->get( 218 ) );

if ( $upd->needSetup() ) {

	//
	if ( empty( $_POST['password'] ) ) {
		$ret = array( 'success' => false, 'msg' => $lng->get( 181 ) );
	}

	//setup
	try {
		$r = $upd->setup( $_POST['password'] );
		$ret = array( 'success' => true, 'msg' => $lng->get( 217 ) );
	} catch (Exception $ex) {
		$str = $ex->getTraceAsString() . ' <br/> ' .
		       $ex->getMessage() . ' <br/> ' .
		       $lng->get( 218 );
		throw new CashError( $str );
		$ret = array( 'success' => false, 'msg' => $str );
	}

} else {
	$ret = array( 'success' => true, 'msg' => $lng->get( 217 ) );
} //needSetup
echo json_encode( $ret );
?>
