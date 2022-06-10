<?php
/**
 * Plugin Name: WP Dolarizate
 * Plugin URI: https://github.com/PecceG2/wp-dolarizate/
 * Description: WP Dolarizate is a WordPress plugin for automatically currency exchange and display with Visual Composer.
 * Version: 0.1
 * Author: Giuliano Peccetto
 * Author URI: https://www.pecceg2.com/
 */
 
 
/*########################################################################*/
//		  					   SETTINGS PAGE							  //
/*########################################################################*/

require_once dirname( __FILE__ ) .'/config-page.php';

add_action('admin_menu', 'create_settings_page');
function create_settings_page() {
	add_menu_page('WP Dolarizate', 'WP Dolarizate', 'administrator', __FILE__, 'settings_page' , plugins_url('/icon.png', __FILE__) );
	add_action( 'admin_init', 'register_settings' );
}

add_action('admin_post_wpd_update_config', 'save_settings_page');
function save_settings_page() {
	update_option('api_dolar', $_POST['api_dolar']);
	update_option('update_time', $_POST['update_time']);
	update_option('dolar_type', $_POST['dolar_type']);
	update_option('dolar_type_cv', $_POST['dolar_type_cv']);

	$dolar_valor_manual = $_POST['valor_dolar_manual'];

	switch($_POST['api_dolar']){
		case 0:
			//DolarSI
			currencyUpdate_DolarSI();
			break;
		case 1:
			//BCRA
			break;
		case 2:
			//BNA
			break;
		case 3:
			//XEC
			break;
		case 4:
			// Manual
			currencyUpdate_Manual($dolar_valor_manual);
			break;
	}
    wp_redirect($_SERVER["HTTP_REFERER"], 302, 'WordPress');
} 

function register_settings() {
	register_setting('wp-dolarizate-settings', 'api_dolar');
	register_setting('wp-dolarizate-settings', 'update_time');
	register_setting('wp-dolarizate-settings', 'dolar_type');
	register_setting('wp-dolarizate-settings', 'dolar_type_cv');
	register_setting('wp-dolarizate-settings', 'dolar_value');
}

/*########################################################################*/
//		  					 END SETTINGS PAGE							  //
/*########################################################################*/


/*########################################################################*/
//		  						CRON JOBS								  //
/*########################################################################*/

function currencyUpdate_DolarSI(){
	$content = getCURL("https://www.dolarsi.com/api/api.php?type=valoresprincipales", true);
	switch(get_option("dolar_type_cv")){
		case "dolarCompra":
			$dolVal = $content[get_option("dolar_type")]->casa->compra;
		break;
		case "dolarVenta":
			$dolVal = $content[get_option("dolar_type")]->casa->venta;
		break;
		case "dolarAgencia":
			$dolVal = $content[get_option("dolar_type")]->casa->agencia;
		break;
		default:
			$dolVal = 0;
		break;
	}
	
	
	update_option('dolar_value', $dolVal);
	return($content);
}

function currencyUpdate_EstadisticasBCRA(){

}

function currencyUpdate_BNAOficial(){

}

function currencyUpdate_XE(){

}

function currencyUpdate_Manual($valor_usd_manual){
	update_option('dolar_value', $valor_usd_manual);
}

/*########################################################################*/
//		  					   END CRON JOBS							  //
/*########################################################################*/


/*########################################################################*/
//		  						SHORTCODES								  //
/*########################################################################*/

function sc_dolarizate_print($atts){
    $default = array(
        'valor' => '0',
    );
	
    $attr = shortcode_atts($default, $atts);
    
	return floatval($attr['valor'])*floatval(get_option('dolar_value'));
}
add_shortcode('wp-dolarizate', 'sc_dolarizate_print'); 

// Fix Visual Composer addons/shortcodes runtime error.
function sc_dolarizate_print_forced($content){
	
	$data = getAllValues($content);
	if(!empty($data)){
		$i = 0;
		foreach($data['value'] as $key=>$value){
			$content = preg_replace('#\[wp\-dolarizate valor\=\'\d+\'\]#', $data['value'][$i], $content, 1);
			$i++;
		}
	}

	return $content;
}

add_filter('the_content', 'sc_dolarizate_print_forced', 12);

/*########################################################################*/
//		  					   END SHORTCODES							  //
/*########################################################################*/


/*########################################################################*/
//		  					   OTHER FUNCTIONS							  //
/*########################################################################*/

function getCURL($url, $isJSON){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	if($isJSON){
		$content = json_decode(curl_exec($ch));
	}else{
		$content = curl_exec($ch);
	}
	curl_close($ch);
	return($content);
}

function getAllValues($content){
    $offset = 0;
    $allpos = array();
    while(($pos = strpos($content, "[wp-dolarizate", $offset)) !== FALSE){
		$tmpLast = strpos($content, "']", $pos);
        $allpos['startposition'][] = $pos;
		$allpos['endposition'][] = $tmpLast;
		$allpos['value'][] = floatval(substr($content, $pos+22, $tmpLast-$pos-22))*floatval(get_option('dolar_value'));
		$tmp_content = substr($content, $pos);
		
		
		//$content = str_replace("[wp-dolarizate valor='116']", ""
        $offset = $tmpLast; //Evite infinite loop
	}
	

    return $allpos;
}

/*########################################################################*/
//		  				 END OTHER FUNCTIONS							  //
/*########################################################################*/
?>