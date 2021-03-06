<?php
/*!
* WordPress Social Login
*
* http://hybridauth.sourceforge.net/wsl/index.html | http://github.com/hybridauth/WordPress-Social-Login
*    (c) 2011-2014 Mohamed Mrassi and contributors | http://wordpress.org/extend/plugins/wordpress-social-login/
*/

/** 
* Few utilities and functions 
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

/**
* Check if the current connection is being made over https
*
* This functions are borrowed from http://wordpress.org/extend/plugins/oa-social-login/ 
*/
function wsl_is_https_on()
{
	if (!empty ($_SERVER ['SERVER_PORT']))
	{
		if (trim ($_SERVER ['SERVER_PORT']) == '443')
		{
			return true;
		}
	}

	if (!empty ($_SERVER ['HTTP_X_FORWARDED_PROTO']))
	{
		if (strtolower (trim ($_SERVER ['HTTP_X_FORWARDED_PROTO'])) == 'https')
		{
			return true;
		}
	}

	if (!empty ($_SERVER ['HTTPS']))
	{
		if (strtolower (trim ($_SERVER ['HTTPS'])) == 'on' OR trim ($_SERVER ['HTTPS']) == '1')
		{
			return true;
		}
	}

	return false;
}

// --------------------------------------------------------------------

/**
 * Return the current url
*
* This functions are borrowed from http://wordpress.org/extend/plugins/oa-social-login/ 
*/
function wsl_get_current_url()
{
	//Extract parts
	$request_uri = (isset ($_SERVER ['REQUEST_URI']) ? $_SERVER ['REQUEST_URI'] : $_SERVER ['PHP_SELF']);
	$request_protocol = (wsl_is_https_on () ? 'https' : 'http');
	$request_host = (isset ($_SERVER ['HTTP_X_FORWARDED_HOST']) ? $_SERVER ['HTTP_X_FORWARDED_HOST'] : (isset ($_SERVER ['HTTP_HOST']) ? $_SERVER ['HTTP_HOST'] : $_SERVER ['SERVER_NAME']));

	//Port of this request
	$request_port = '';

	//We are using a proxy
	if (isset ($_SERVER ['HTTP_X_FORWARDED_PORT']))
	{
		// SERVER_PORT is usually wrong on proxies, don't use it!
		$request_port = intval ($_SERVER ['HTTP_X_FORWARDED_PORT']);
	}
	//Does not seem like a proxy
	elseif (isset ($_SERVER ['SERVER_PORT']))
	{
		$request_port = intval ($_SERVER ['SERVER_PORT']);
	}

	// Remove standard ports
	$request_port = (!in_array ($request_port, array (80, 443)) ? $request_port : '');

	//Build url
	$current_url = $request_protocol . '://' . $request_host . ( ! empty ($request_port) ? (':'.$request_port) : '') . $request_uri;

	// HOOKABLE: 
	$current_url = apply_filters ( 'wsl_hook_alter_current_url', $current_url );

	//Done
	return $current_url;
}

// --------------------------------------------------------------------

/**
* Display a debugging area.
*
* This function is highly inspired by the Query Monitor.
* https://wordpress.org/plugins/query-monitor/
*
* IMPORTANT: Use this only if Dev mode is enabled
*
* Note: in order for this function to display the sql queries, 'SAVEQUERIES' should be defined as true in 'wp-config.php'
*/
function wsl_display_debugging_area()
{
	global $wpdb, $wp_actions , $wp_filter, $WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG, $WORDPRESS_SOCIAL_LOGIN_COMPONENTS;
?>
	<style>
		.wsl-dev-nonselectsql {
			color: #a0a !important;
		}
		.wsl-dev-expensivesql {
			color: #f44 !important;
		}
		.wsl-dev-optionfunc {
			color: #4a4 !important;
		}
		.wsl-dev-wslfunc {
			color: #1468fa !important;
		}
		.wsl-dev-nonwslfunc {
			color: #a0a !important;
		}
		.wsl-dev-table { 
			width:100%
			border: 1px solid #e5e5e5;
			box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);	
			border-spacing: 0;
			clear: both;
			margin: 0;
			width: 100%;
		}
		.wsl-dev-table td, .wsl-dev-table th {
			border: 1px solid #dddddd;
			padding: 8px 10px; 
			background-color: #fff;
			text-align: left;
		}
	</style>

	<h5>Host</h5>
	<table class="wsl-dev-table">
		<tbody>
			<tr><th width="150"><label>IP</label></th><td><?php echo $_SERVER['SERVER_ADDR']; ?></td></tr>  
			<tr><th><label>Domain</label></th><td><?php echo $_SERVER['HTTP_HOST']; ?></td></tr>  
			<tr><th><label>Port</label></th><td><?php echo isset( $_SERVER['SERVER_PORT'] ) ? 'On (' . $_SERVER['SERVER_PORT'] . ')' : 'N/A'; ?></td></tr>  
			<tr><th><label>X Forward</label></th><td><?php echo isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ? 'On (' . $_SERVER['HTTP_X_FORWARDED_PROTO'] . ')' : 'N/A';; ?></td></tr>   
		</tbody>
	</table>
	
	<h5>Software</h5>
	<table class="wsl-dev-table">
		<tbody>
			<tr><th width="150"><label>Server</label></th><td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td></tr>  
			<tr><th><label>PHP</label></th><td><?php echo PHP_VERSION; ?></td></tr>  
			<tr><th><label>MySQL</label></th><td><?php echo $wpdb->db_version(); ?></td></tr>   
		</tbody>
	</table>

	<h5>MySQL</h5>
	<table class="wsl-dev-table">
		<tbody>
			<tr><th width="150"><label>Host</label></th><td><?php echo $wpdb->dbhost; ?></td></tr>  
			<tr><th><label>User</label></th><td><?php echo $wpdb->dbuser; ?></td></tr>  
			<tr><th><label>Database</label></th><td><?php echo $wpdb->dbname; ?></td></tr>  
			<tr><th><label>Prefix</label></th><td><?php echo $wpdb->prefix; ?></td></tr>  
			<tr><th><label>Base_prefix</label></th><td><?php echo $wpdb->prefix; ?></td></tr>  
			<tr><th><label>Num_queries</label></th><td><?php echo$wpdb->num_queries; ?></td></tr>  
		</tbody>
	</table>

	<h5>Wordpress</h5>
	<table class="wsl-dev-table">
		<tbody>
			<tr><th width="150"><label>Version</label></th><td><?php echo get_bloginfo( 'version' ); ?></td></tr>   
			<tr><th><label>Multi-site</label></th><td><?php echo is_multisite() ? 'Yes' . "\n" : 'No'; ?></td></tr>
			<tr><th><label>Site url</label></th><td><?php echo site_url(); ?></td></tr>   
			<tr><th><label>Plugins url</label></th><td><?php echo plugins_url(); ?></td></tr>    
		</tbody>
	</table>

	<h5>WSL</h5>
	<table class="wsl-dev-table">
		<tbody>
			<tr><th width="150"><label>Version</label></th><td><?php echo wsl_get_version(); ?></td></tr>  
			<tr><th><label>Providers</label></th><td><?php echo count( $WORDPRESS_SOCIAL_LOGIN_PROVIDERS_CONFIG ); ?></td></tr>   
			<tr><th><label>Components</label></th><td><?php echo count( $WORDPRESS_SOCIAL_LOGIN_COMPONENTS ); ?></td></tr>   
			<tr><th><label>Plugin path</label></th><td><?php echo WORDPRESS_SOCIAL_LOGIN_ABS_PATH; ?></td></tr>  
			<tr><th><label>Plugin url</label></th><td><?php echo WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL; ?></td></tr>  
			<tr><th><label>HA endpoint</label></th><td><?php echo WORDPRESS_SOCIAL_LOGIN_HYBRIDAUTH_ENDPOINT_URL; ?></td></tr>   
		</tbody>
	</table>

	<h5>SQL Queries <?php if( ! defined('SAVEQUERIES') || ! SAVEQUERIES ) echo " ('SAVEQUERIES' should be defined and set to TRUE: http://codex.wordpress.org/Editing_wp-config.php#Save_queries_for_analysis)"; ?></h5>
	<table class="wsl-dev-table">
		<tbody>
			<?php
				$queries = $wpdb->queries;
				
				$total_wsl_queries = 0;
				$total_wsl_queries_time = 0;
				
				if( $queries )
				foreach( $queries as $item ){
					$sql    = $item[0];
					$time   = $item[1];
					$stack  = $item[2];
					
					$sql = str_ireplace( array( ' FROM ', ' WHERE ' , ' LIMIT ' , ' GROUP BY ' , ' ORDER BY ' , ' SET ' ), ARRAY( "\n" . 'FROM ', "\n" . 'WHERE ', "\n" . 'LIMIT ', "\n" . 'GROUP BY ', "\n" . 'ORDER BY ', "\n" . 'SET ' ), $sql );

					# https://wordpress.org/plugins/query-monitor/
					$callers   = explode( ',', $stack );
					$caller    = trim( end( $callers ) );

					if ( false !== strpos( $caller, '(' ) )
						$caller_name = substr( $caller, 0, strpos( $caller, '(' ) ) . '()';
					else
						$caller_name = $caller;

					if( stristr( $caller_name, 'wsl_' ) || stristr( $sql, 'wsl_' ) || stristr( $stack, 'wsl_' ) ){
					?>
						<tr>
							<td valign="top" width="450">
								<span class="<?php if( stristr( $caller_name, '_option' ) ) echo 'wsl-dev-optionfunc'; elseif( stristr( $caller_name, 'wsl_' ) ) echo 'wsl-dev-wslfunc'; else echo 'wsl-dev-nonwslfunc'; ?>"><?php echo $caller_name; ?></span>
								<p style="font-size:11px; margin-left:10px">
								<?php
									# God damn it
									if(  count( $callers ) ){
										for( $i = count( $callers ) - 1; $i > 0; $i-- ){
											echo "#$i &nbsp; " . $callers[$i] . '<br />';
										}
									}
								?>
								</p>
							</td>
							<td valign="top" class="<?php if( ! stristr( '#' . $sql, '#select ' ) ) echo 'wsl-dev-nonselectsql'; ?>"><?php echo nl2br( $sql ); ?></td>
							<td valign="top" width="50" nowrap class="<?php if( $time > 0.05 ) echo 'wsl-dev-expensivesql'; ?>"><?php echo number_format( $time, 4, '.', '' ); ?></td>
						</tr>   
					<?php 
						$total_wsl_queries++;
						$total_wsl_queries_time += $time;
					}
				}
			?>
			<tr>
				<td colspan="2">Total SQL Queries by WSL : <?php echo $total_wsl_queries; ?></td>
				<td width="50" nowrap><?php echo number_format( $total_wsl_queries_time, 4, '.', '' ); ?></td>
			</tr>   
		</tbody>
	</table>

	<h5>Hooks</h5>
	<table class="wsl-dev-table">
		<tbody>
			<?php	
				if( $wp_actions )
				foreach( $wp_actions as $name => $count ){
					if ( isset( $wp_filter[$name] ) ) {
						$action = $wp_filter[$name]; 

						if ( 
							$name == 'init' ||
							stristr( $name, 'wsl_' ) ||
							stristr( $name, 'admin_' ) ||
							stristr( $name, 'plugins_' )  ||
							stristr( $name, 'wp_' ) ||
							stristr( $name, 'comment_' ) 
							// 1
						) {
							?>
								<tr>
									<td valign="top" width="150">
										<pre><?php echo $name ; ?></pre>
									</td>
									<td valign="top">
										<?php 
											if( $action )
											foreach( $action as $priority => $callbacks ) {
												foreach( $callbacks as $callback ) { 
													if( isset( $callback['function'] ) && is_string( $callback['function'] ) ){
														if( stristr( $callback['function'], 'wsl_' ) ){
															echo $callback['function'] . " ( $priority ) <br />" ; 
														} 
													} 
												}
											} 
										?>
									</td>
								</td>
							<?php 
						}
					}
				}
			?>
		</tbody>
	</table>
<?php
}

// --------------------------------------------------------------------
