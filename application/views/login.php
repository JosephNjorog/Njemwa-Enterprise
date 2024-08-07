<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/logo/POSlite.png') ?>" />
	<title>Login - POSLite Inventory Mangement Software</title>
	<?php $this->load->view('template/header'); ?>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/login.css')?>">
</head>
<body > 
<div class="col1">
	<div class="quote-wrapper"> 
   
	<blockquote>
  There are no secrets to success. It is the result of preparation, hard work and learning from failure. 
  <span>– Colin Powell</span>
  </blockquote>
   
	</div>
</div>
<div class="col2">
	<?php
	$attributes = array( 
	'class' => 'form-signin'
	);
	?>

	<?php echo form_open('AuthController/login_validation',$attributes )?> 

	<h2 class="text-center">Sign In</h2>
	<br>
	<?php if($this->session->flashdata('errorMessage')): ?>
	<div class="form-group">
		<?php echo ($this->session->flashdata('errorMessage'))?>
	</div>
	<?php endif; ?>
	<?php if($this->session->flashdata('successMessage')): ?>
	<div class="form-group">
		<?php echo ($this->session->flashdata('successMessage'))?>
	</div>
	<?php endif; ?>
	<div class="form-group">
		<div class="input-group ">
			<span    class="input-group-addon"><i class="fa fa-user " aria-hidden="true"></i></span>
			<input   autocomplete="off" id="username" type="text" class="form-control input-md" name="username" placeholder="Username" required="required" data-parsley-errors-container="#username-error">
		</div>      
		<span id="username-error"></span>
	</div> 
	<div class="form-group">
		<div class="input-group ">
			<span   class="input-group-addon"><i class="fa fa-key " aria-hidden="true"></i></span>
			<input  autocomplete="off" id="password" type="password" class="form-control input-md" name="password" placeholder="Password" required="required" data-parsley-errors-container="#password-error">
		</div>      
		<span id="password-error"></span>
	</div>
	<div></div>
	
	
	<div class="form-group">
		<button class="btn btn-md btn-primary btn-block" type="submit" style="border-radius: 1em;">Login</button>  
	</div> 
	<!-- <?php if (SITE_LIVE): ?> 
		<div class="">
			<h4 class="text-center">Login Credentials </h4>
			<ul>
				<li><b>Username:</b> admin <b>Password:</b> admin123</li>
				<li><b>Username:</b> cashier <b>Password:</b> cashier123</li>
			</ul>
		</div> 
	 	<h5></h5>
	<?php endif; ?> -->
	<p class="text-center" style="color: #777">&copy; <?php echo date('Y-m-d') ?> All Rights Reserved <br> Developed by: <a href="https://algermakiputin.dev">Alger Makiputin</a></p>
	<?php echo form_close() ?>
</div> 
	

<?php $this->load->view('template/footer'); ?>
</body>
</html>