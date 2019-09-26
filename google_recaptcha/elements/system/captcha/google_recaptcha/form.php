<?php  defined('C5_EXECUTE') or die('Access denied.');
$form = Loader::helper('form');
$co = new Config();
$co->setPackageObject(Package::getByHandle('google_recaptcha'));
$recaptcha_version = $co->get('recaptcha_version');
$recaptcha_key_public = $co->get('recaptcha_key_public');
$recaptcha_key_private = $co->get('recaptcha_key_private');

$recaptcha_key_publicv3 = $co->get('recaptcha_key_publicv3');
$recaptcha_key_privatev3 = $co->get('recaptcha_key_privatev3');

$recaptcha_validate_ssl = $co->get('recaptcha_validate_ssl');
if ($recaptcha_validate_ssl === null)
	$recaptcha_validate_ssl = '1';

$recaptcha_signupUrl = 'https://www.google.com/recaptcha/admin#list'; //recaptcha_get_signup_url($_SERVER['HTTP_HOST'], SITE);
?>

<div class="clearfix">
  <?php  echo  $form->label('recaptcha_version', t('Version')) ?>
  <div class="input">
    <?php  echo  $form->select('recaptcha_version', array('v2' => t('V2'), 'v3' => t('V3 Invisible')), $recaptcha_version) ?>
  </div>
</div>
<div class="v2">
  <div class="clearfix">
    <?php  echo $form->label('recaptcha_key_public', t('V2 public key')); ?>
    <div class="input">
      <?php  echo $form->text('recaptcha_key_public', is_string($recaptcha_key_public) ? $recaptcha_key_public : '', array('class' => 'span5')); ?>
    </div>
  </div>
  <div class="clearfix">
    <?php  echo $form->label('recaptcha_key_private', t('V2 private key')); ?>
    <div class="input">
      <?php  echo $form->text('recaptcha_key_private', is_string($recaptcha_key_private) ? $recaptcha_key_private : '', array('class' => 'span5')); ?>
    </div>
  </div>
</div>
<div class="v3">
  <div class="clearfix">
    <?php  echo $form->label('recaptcha_key_publicv3', t('V3 Invisible public key')); ?>
    <div class="input">
      <?php  echo $form->text('recaptcha_key_publicv3', is_string($recaptcha_key_publicv3) ? $recaptcha_key_publicv3 : '', array('class' => 'span5')); ?>
    </div>
  </div>
  <div class="clearfix">
    <?php  echo $form->label('recaptcha_key_privatev3', t('V3 Invisible private key')); ?>
    <div class="input">
      <?php  echo $form->text('recaptcha_key_privatev3', is_string($recaptcha_key_privatev3) ? $recaptcha_key_privatev3 : '', array('class' => 'span5')); ?>
    </div>
  </div>
</div>
<div class="clearfix">
  <div class="input">
    <?php  echo $form->label('', t('You can get the public and private keys from <a target="_blank" href="%s">this page</a>.', $recaptcha_signupUrl)); ?>
  </div>
</div>
<div class="clearfix">
  <?php  echo $form->label('recaptcha_validate_ssl', t('Validate SSL')); ?>
  <div class="input"> <?php echo $form->checkbox('recaptcha_validate_ssl', 1, $recaptcha_validate_ssl ? true : false); ?><br>
    <?php echo t('If your server cannot validate SSL certificates, try disabling this setting')?> </div>
</div>
<script type="text/javascript">

	var value = $('#recaptcha_version').val();
	type(value);

$(document).on('change', '#recaptcha_version', function() {
 
	 var value = $(this).val();
	 type(value);

 });
 
 
 function type(val){
	 
	 var value = val;
	 if(value=='v2'){
		 
		$('.v2').show();
		$('.v3').hide();
		
	 }else{
		 $('.v3').show();
		 $('.v2').hide()
	 } 
 }
</script> 
