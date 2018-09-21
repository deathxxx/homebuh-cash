<?php
require_once('lib/init.php');
?>
<!doctype html>
<html>
  <head>
    <meta charset=utf-8>
    <title><?php echo $settings['site_name'];?></title>
    <link rel="shortcut icon" href="<?php echo $settings['static'];?>/favicon.png" />
    <link href="<?php echo $settings['static'];?>/style.css?<?php echo $settings['version'];?>" media="all" rel="stylesheet" type="text/css" />
    <link href="<?php echo $settings['extjs'];?>/resources/css/ext-all.css" media="all" rel="stylesheet" type="text/css" />
    <link href="<?php echo $settings['extjs']?>/examples/ux/grid/css/GridFilters.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $settings['extjs']?>/examples/ux/grid/css/RangeMenu.css" rel="stylesheet" type="text/css" />
    <script src="<?php echo $settings['extjs'];?>/ext-all.js" 	type="text/javascript"></script>
    <script src="//maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
    <script language="javascript">
      var settings = <?php unset($settings['ocr']); echo json_encode($settings);?>;
      var translate = <?php echo json_encode($lng->get(null));?>;
    </script>
    <script src="<?php echo $settings['static'];?>/js/script.js?<?php echo $settings['version'];?>" charset="UTF-8" type="text/javascript"></script>
    <script src="<?php echo $settings['extjs'];?>/locale/ext-lang-<?php if( $lng->slang == "us" ) echo "en"; else echo $lng->slang; ?>.js" charset="UTF-8" type="text/javascript"></script>
  </head>
  <body>
    <div id="logout"></div>
    <div id="main"></div>
    <?php echo $settings['add'];?>
  </body>
</html>
