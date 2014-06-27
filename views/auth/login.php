<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
</head>
<body>

	<h1>Login</h1>

	<?php echo validation_errors(); ?>

	<?php echo form_open('auth/login'); ?>
		<?php echo form_label('Username', 'username'); ?>
		<?php echo form_input(array(
			'name' => 'username',
			'id' => 'username',
			'value' => set_value('username')
		)); ?>
		<?php echo form_label('Password', 'password'); ?>
		<?php echo form_password(array(
			'name' => 'password',
			'id' => 'password'
		)); ?>
		<?php echo form_submit('login', 'Login'); ?>
	<?php echo form_close(); ?>

</body>
</html>