<?php
require_once("../lib/init.php");
echo json_encode($usr->auth($_POST));