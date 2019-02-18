<?php

/**
 * Script Name: Uniliving Stock and Prices Import
 * Description: Imports CSV file which is exported by Uniliving and places its values directly into ACF fields at a Wordpress/Woocommerce database
 * Author: Converzo / Erik Molenaar
 * Version: 1.2
**/

// LIVE settings
$hostname = "myhostname";
$database_server = "localhost";
$database_name = "mydatabase";
$database_user = "myusername";
$database_pass = "mysupersecretpassword";
$filedir = "/home/vpsuser/httpdocs/wp-content/uploads/misc/";
$filename = "artikelen-voorraad-prijzen-website.csv";

// DEVELOPMENT settings
$database_server_dev = "mariadb";
$database_name_dev = "myfolder";
$database_user_dev = "root";
$database_pass_dev = "root";
$filedir_dev = "/var/www/html/mysite/wp-content/uploads/wpallimport/files/"; // Starts and ends with slash
$filename_dev = "artikelen-voorraad-prijzen-website.csv";


// Marking start time of script
$time_start_script = date ( "d-m-Y G:i:s" );
$timestamp_start_script = strtotime ( $time_start_script );

// Function for echo and logging what the script is doing
function echo_and_log ( $message ) {

	echo $message . PHP_EOL;
	$file_log = '/var/log/subsku-data-import.log';
	$fp_log = fopen ( $file_log , "a" ) or die ( 'Cannot open log file: ' . $file_log );
	fwrite ( $fp_log, date ( "Y-m-d G:i:s" ) . ' - ' . $message . "\n" );
	fclose ( $fp_log );

}

// Actual script code below
echo '' . PHP_EOL;
echo 'Uniliving Stock and Prices Import' . PHP_EOL;
echo '---------------------------------' . PHP_EOL;
echo '' . PHP_EOL;
echo_and_log ( 'Script started at: ' . $time_start_script );

if ( gethostname() != $hostname ) {

	echo_and_log ( "DEVELOPMENT environment DETECTED! Note: datetime in SubSKU description is set to datetime of UPDATE query" );
	
	$database_server = $database_server_dev;
	$database_name = $database_name_dev;
	$database_user = $database_user_dev;
	$database_pass = $database_pass_dev;
	$filedir = $filedir_dev;
	$filename = $filename_dev;
	$dev_mode = "true";

} else {

	$dev_mode = "false";
	echo_and_log ( "LIVE environment DETECTED! Please note: update time in SubSKU description is set to datetime of CSV file" );

}

echo_and_log ( "Checking if the script is already running..." );
$running_dir = str_replace("/","\/", (__FILE__) );
$running_processes = exec ( 'ps aux | grep -e "[^]]php ' . basename(__FILE__) . '" -e "[^]]php ' . $running_dir .  '" | wc -l' );

if ( $running_processes > 2 ) {
   
	echo_and_log ( "BAD news! Script is already running!" );
	echo '' . PHP_EOL;
	die;

} else {

	echo_and_log ( "Great news. No duplicate processe(s) are running." );

}

echo_and_log ( 'Connecting to database...' );
$conn = mysqli_connect ( $database_server , $database_user , $database_pass ) or die ( 'Error in database connection' . PHP_EOL );

echo_and_log ( 'Selecting database...' );
echo '' . PHP_EOL;

$db = mysqli_select_db ( $conn , $database_name );
$file_csv = $filedir . $filename;

if ( !file_exists ( $file_csv ) ) {

	echo '' . PHP_EOL;
	echo_and_log ( 'BAD news! CSV file does not exist: ' . $file_csv );
	echo '' . PHP_EOL;
	die;

} else {

	echo_and_log ( 'Great news! CSV file exists: ' . $file_csv );

}

if ( filesize ( $file_csv ) === 0 ) {

	echo '' . PHP_EOL;
	echo_and_log ( 'BAD news! CSV file contains no data!' );
	echo '' . PHP_EOL;
	die;

} else {

	echo_and_log ( 'Great news! CSV file contains ' . round ( filesize ( $file_csv ) / 1024, 2 ) . 'KB of data' );

}

$datemodified = strtotime ( date ( "Y-m-d H:i:s.", filemtime ( $file_csv) ) );

if ( $datemodified <= strtotime ( '-1 day' ) ) {

	echo '' . PHP_EOL;
	echo_and_log ( 'BAD news! CSV file is older than 24 hours! Its from: ' . date ( "Y-m-d H:i:s", $datemodified ) );
	echo_and_log ( 'Updating the SUBSKU fields in Woocommerce with this error message...' );

	for ( $i = 0; $i <= 100; $i++ ) {

		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='CSV older than 24 hours' WHERE meta_key = 'uniliving_artikelen_" . $i . "_omschrijving'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_voorraad_magazijn'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_voorraad_winkel'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_voorraad_std'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_min_voorraad'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_adviesprijs'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_verkoopprijs'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_commissie'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_collectie'" );
		mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_". $i ."_voorraad_outlet'" );

	} 
	
	echo_and_log ( 'Updating SUBSKU fields with out-of-date message COMPLETE!' );
	echo '' . PHP_EOL;

} else {

	echo_and_log ( 'Great news! CSV file is younger than 24 hours. Its from: ' . date ( "Y-m-d H:i:s", $datemodified ) );
	echo '' . PHP_EOL;
	echo_and_log ( 'Building array from Woocommerce product SUBSKUs...' );

	$datasku = array();

	for ( $i = 0;$i <= 100; $i++ ) {

		$res = mysqli_query ( $conn, "SELECT meta_value FROM wp_postmeta as pm INNER JOIN wp_posts as p ON p.ID = pm.post_id WHERE p.post_status IN ('publish','pending') AND meta_key = 'uniliving_artikelen_".$i."_artnr'" );

		while ( $d = mysqli_fetch_assoc ( $res ) ) {

			$datasku[] = $d['meta_value'];

		}

	}

	echo_and_log ( 'Sorting array of WooCommerce product SUBSKUs 1/2...' );
	sort ( $datasku , SORT_NUMERIC );
	$actual_link = $_SERVER['DOCUMENT_ROOT'];

	echo_and_log ( 'Opening CSV file pointer...' );
	$fp_csv = fopen ( $file_csv, "r" ) or die ('Cannot open CSV file: ' . $file_csv );

	echo_and_log ( 'Sorting array of WooCommerce product SUBSKUs 2/2...' );
	sort ( $datasku , SORT_NUMERIC );
	
	echo_and_log ( 'Building array from CSV file...' );
	$sku = array();
	$headerLine = true;
	$count = 0;
	$j = 0;

	while ( ( $data = fgetcsv ( $fp_csv, 10000000, "|" ) ) !== FALSE ) {

		if ( $data[0] == "" ) continue;

		if ( $headerLine ) { 
			
			$headerLine = false;
		
		} else {

			$sku[] = array (

				"sku"=>$data[0],
				"verkoop"=>$data['1'],
				"voorrad"=>$data['2'],
				"winkel"=>$data['3'],
				"outlet"=>$data['4'],
				"std"=>$data['5'],
				"min"=>$data['6'],
				"adviesprijs"=>$data['8'],
				"prijs"=>$data['9'],
				"commission"=>$data['10'],
				"collectie"=>$data['11'],
				
			);

		}

	}

	echo_and_log ( 'Array from CSV file consists of ' . count ( $sku ) . ' products'  );
	echo_and_log ( 'Removing special characters from CSV file array... ' );

	$matchsku = array();

	for ( $r = 0 ; $r <= count ( $sku ); $r++ ) {
	
		if ( isset ( $sku[$r]['sku']) && in_array ( $sku[$r]['sku'] , $datasku ) ) {
			
			$matchsku[] = array (

				"sku" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['sku'] ),
				"verkoop" => preg_replace ( '/[^A-Za-z0-9 ]/', '', $sku[$r]['verkoop'] ),
				"voorrad" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['voorrad'] ),
				"winkel" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['winkel'] ),
				"outlet" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['outlet'] ),
				"std" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['std'] ),
				"min" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['min'] ),
				"adviesprijs" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['adviesprijs'] ),
				"prijs" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['prijs'] ),
				"commission" => preg_replace ( '/[^0-9 ]/', '', $sku[$r]['commission'] ),
				"collectie" => preg_replace ( '/[^A-Za-z ]/', '', $sku[$r]['collectie'] ),
				
			);

		}

	}

	echo_and_log ( 'Start of EMPTYING subsku fields in Woocommerce (which do not exist in CSV file)...' );
	echo '' . PHP_EOL;
	
	$time_start_emptying = date ( "d-m-Y G:i:s" );
	$lines_emptied = 0;

	foreach ( $datasku as $id ) {

		if ( !empty ( $id ) && !in_array ( $id, array_column ( $sku, 'sku') ) ) {

			$sqls = mysqli_query ( $conn, "SELECT post_id,meta_key FROM wp_postmeta WHERE meta_value LIKE '" . $id . "'" );

			$norows = mysqli_num_rows ( $sqls );

			while ( $res = mysqli_fetch_array ( $sqls ) ) {
				
				if ( $norows > 0 ) {

					$int = filter_var ( $res['meta_key'], FILTER_SANITIZE_NUMBER_INT );

					if ( strpos ( $res['meta_key'], '_artnr' ) !== false ) {

						$lines_emptied = $lines_emptied + 1;

						$time_emptying_line = date ( "d-m-Y G:i:s" );
						$timestamp_emptying_line = strtotime ( $time_emptying_line );
						$duration_script =  round ( abs ( $timestamp_emptying_line - $timestamp_start_script ) ) . " second(s)";

						echo_and_log ( 'Script started ' . $time_start_script . ' and running ' . $duration_script . ' - EMPTYING subsku ' . $id . ' at WooCommerce product ' . $res['post_id'] );

						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_omschrijving' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_voorraad_magazijn' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_voorraad_winkel' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_voorraad_std' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_min_voorraad' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_adviesprijs' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_verkoopprijs' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_commissie' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_collectie' AND post_id=" . $res['post_id'] );
						mysqli_query ( $conn , "UPDATE wp_postmeta SET meta_value='' WHERE meta_key = 'uniliving_artikelen_" . $int . "_voorraad_outlet' AND post_id=" . $res['post_id'] );


					}

				}

			}

		}

	}
	
	$time_stop_emptying = date ( "d-m-Y G:i:s" );
	echo_and_log ( 'EMPTYING complete!' );

	echo '' . PHP_EOL;
	echo_and_log ( 'Start of UPDATING subsku fields in Woocommerce...' );
	$time_start_updating = date ( "d-m-Y G:i:s" );
	$lines_updated = 0;

	for ( $s = 0;$s < count ( $matchsku ); $s++ ) {

		$sqls = mysqli_query ( $conn , "SELECT post_id,meta_key FROM wp_postmeta WHERE meta_value LIKE '" . $matchsku[$s]['sku'] . "' AND meta_value !=''" );		
		$norows = mysqli_num_rows ( $sqls );

		while ( $res = mysqli_fetch_array ( $sqls ) ) {

			if ( $matchsku[$s]['sku'] == "" ) continue;

			if ( $norows > 0 )	{

				$int = filter_var ( $res['meta_key'] , FILTER_SANITIZE_NUMBER_INT );

				if ( strpos ( $res['meta_key'],'_artnr') !== false ) {

					$lines_updated = $lines_updated + 1;

					$time_updating_line = date ( "d-m-Y G:i:s" );
					$timestamp_updating_line = strtotime ( $time_updating_line );
					$duration_script =  round ( abs ( $timestamp_updating_line - $timestamp_start_script ) / 60 ) . " minute(s)";

					echo_and_log ( 'Script started ' . $time_start_script . ' and running ' . $duration_script . ' - UPDATING subsku ' . $matchsku[$s]['sku'] . ' at WooCommerce product ' . $res['post_id'] );

					if ( $dev_mode === "true" ) {
						
						// In development environment, use current datetime in verkoopomschrijving
						$time_update = date ( "d-m-Y G:i:s" );

					} else {
						
						// In live environment use datetime of CSV file in verkoopomschrijving
						$time_update = date ( "d-m-Y G:i", $datemodified );
					
					}

					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['verkoop'] . " (Updated " . $time_update . ")' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_omschrijving'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['voorrad'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_voorraad_magazijn'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['winkel'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_voorraad_winkel'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['std'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_voorraad_std'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['min'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_min_voorraad'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['adviesprijs'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_adviesprijs'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['prijs'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_verkoopprijs'" );
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['commission'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_commissie'" );					
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['collectie'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_collectie'" );						
					mysqli_query ( $conn, "UPDATE wp_postmeta SET meta_value = '" . $matchsku[$s]['outlet'] . "' WHERE post_id = " . $res['post_id'] . " AND meta_key = 'uniliving_artikelen_" . $int . "_voorraad_outlet'" );

				}

			}

		}

	}

	$time_stop_updating = date ( "d-m-Y G:i:s" );
	echo_and_log ( 'UPDATING complete!' );
	echo '' . PHP_EOL;

	echo_and_log (  'Closing CSV file pointer...' );
	fclose ( $fp_csv );

}

$time_ended_script = date ( "d-m-Y G:i:s" );
$timestamp_ended_script = strtotime ( $time_ended_script );

echo '' . PHP_EOL;
echo_and_log ( 'All done! Some statistics:' );
echo '' . PHP_EOL;

echo_and_log ( 'Script started at: ' . $time_start_script );
echo_and_log ( 'Script ended at: ' . $time_ended_script );
echo_and_log ( 'Script runtime: ' . round ( abs ( $timestamp_ended_script - $timestamp_start_script ) / 60 ) . " minute(s)" );

if ( isset ( $time_start_emptying ) && isset ( $time_stop_emptying ) && isset ( $lines_emptied ) ) {
	
	$timestamp_start_emptying = strtotime ( $time_start_emptying );
	$timestamp_stop_emptying = strtotime ( $time_stop_emptying );
	echo_and_log ( 'Emptying took ' . round ( abs ( $timestamp_stop_emptying - $timestamp_start_emptying ) / 60 ) . " minutes for " . $lines_emptied . " lines" );
	
}

if ( isset ( $time_start_updating ) && isset ( $time_stop_updating ) && isset ( $lines_updated ) ) {
	
	$timestamp_start_updating = strtotime ( $time_start_updating );
	$timestamp_stop_updating = strtotime ( $time_stop_updating );
	echo_and_log ( 'Updating took ' . round ( abs ( $timestamp_stop_updating - $timestamp_start_updating ) / 60 ) . " minutes for " . $lines_updated . " lines" );

}

echo '' . PHP_EOL;
echo 'Bye now!' . PHP_EOL;
echo '' . PHP_EOL;

?>