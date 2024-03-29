<?php
function is_selected($value, $option, $print = true){
	if(get_option($option) == $value){
		if($print){
			echo 'selected';
		}else{
			return 'selected';
		}
	}
}


function settings_page() {
?>
<div class="wrap">
	<h1>WP Dolarizate</h1>
	
	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<?php settings_fields( 'wp-dolarizate-settings' ); ?>
		<?php do_settings_sections( 'wp-dolarizate-settings' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" style="width:300px;">Dolar API:</th>
				<td>
					<select name="api_dolar">
						<option value="0" <?php is_selected(0, 'api_dolar'); ?>>DolarSI</option>
						<option value="1" <?php is_selected(1, 'api_dolar'); ?>>EstadÃ­sticas BCRA</option>
						<option value="2" <?php is_selected(2, 'api_dolar'); ?>>BNA (Inestable)</option>
						<option value="3" <?php is_selected(3, 'api_dolar'); ?>>XE Currency Data API (Premium)</option>
						<option value="4" <?php is_selected(4, 'api_dolar'); ?>>Valor Manual</option>
					</select>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row" style="width:300px;">ConfiguraciÃ³n de la moneda:</th>
				<td>
				<?php
					switch(get_option('api_dolar')){
						case 0:
						//DolarSI Options
						echo('<select name="dolar_type">');
						$DSIData = (array) currencyUpdate_DolarSI();
						foreach($DSIData as $key=>$value){
							echo('<option '.is_selected($key, "dolar_type", false).' value="'.$key.'">'.(is_string($value->casa->nombre) ? $value->casa->nombre : "Otro").' (Compra: '.(is_string($value->casa->compra) ? $value->casa->compra : "-").') (Venta: '.(is_string($value->casa->venta) ? $value->casa->venta : "-").')</option>');
						}
						echo("</select>");
						break;
						default:
							echo("<h2>Seleccione un Dolar API y guarda la configuraciÃ³n para mostrar estas opciones.</h2>");
						break;
					}
						
				?>
				<select name="dolar_type_cv">
					<option <?php is_selected('dolarCompra', 'dolar_type_cv');?> value="dolarCompra">DÃ³lar Valor de compra</option>
					<option <?php is_selected('dolarVenta', 'dolar_type_cv');?> value="dolarVenta">DÃ³lar Valor de venta</option>
					<option <?php is_selected('dolarAgencia', 'dolar_type_cv');?> value="dolarAgencia">DÃ³lar Valor de agencia</option>
				</select>
				</td>
			</tr>
			 
			<tr valign="top">
				<th scope="row" style="width:300px;">Periodo de actualizaciÃ³n de la moneda (en minutos):</th>
				<td>
					<select name="update_time">
					<?php
					$i = 0;
					foreach(wp_get_schedules() as $key=>$value){
						echo('<option value="'.$value['interval'].'" '.is_selected($value['interval'], 'update_time').">".$value['display']."</option>");
						$i++;
					}
					?>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" style="width:300px;">Valor manual de dÃ³lar (Debe estar marcada la opciÃ³n API Manual):</th>
				<td>
					<input type="text" name="valor_dolar_manual" value="<?=get_option('dolar_value')?>">
				</td>

			</tr>
			
			
		</table>
		
		<br>
		<hr>
		<h2>Modo de uso:</h2>
		<h3>Ingrese el siguiente cÃ³digo donde desea que se imprima el valor convertido: </h3><h4 style="color: #2cbe68;">[wp-dolarizate valor='INGRESE AQUÃ EL VALOR A CONVERTIR']</h4>
		
		<input type="hidden" name="action" value="wpd_update_config">
		<?php submit_button('Guardar cambios'); ?>

	</form>
</div>
<?php } ?> 	
