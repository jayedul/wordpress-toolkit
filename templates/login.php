<!doctype html>
<html data-theme="light">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Login | <?php echo bloginfo( 'name' ) ?></title>
		<?php wp_head(); ?>
	</head>
	<body style="margin: 0; padding: 0;">
		<div 
			id="solidie_login_screen" 
			class="height-p-100 width-p-100"
			data-redirect_to="<?php echo esc_url( ! empty( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : get_home_url() ) ?>"
		></div>
		<?php wp_footer(); ?>
	</body>
</html>