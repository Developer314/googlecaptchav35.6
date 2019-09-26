<?php  defined('C5_EXECUTE') or die('Access denied.');

class GoogleRecaptchaSystemCaptchaTypeController extends SystemCaptchaTypeController {

	// These two methods are not required, because the javascript does this for us.
	// They must be defined because the parent is an abstract class, which declares these functions as abstract
	public function label() {}
	public function showInput() {}

	/** Display the captcha (if the reCAPTCHA keys are set). */
	
	public function display() {
		
		global $c;
		$keys = self::getKeys(true);
		$v2=$keys['recaptcha_version']=='v2'?1:0;
		if(!$keys) {
			echo t('Please specify the reCAPTCHA private/public keys');
		}
		else {
		
	 if($v2){
		if(!$c->isEditMode()){ 	
			echo '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>';
			echo '<div class="g-recaptcha" data-sitekey="'.$keys['recaptcha_key_public'].'"></div>'; // V2
		}
	  }else{
		if(!$c->isEditMode()){ 	
				echo '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?render='.$keys['recaptcha_key_publicv3'].'"></script>';
				echo '<style>.grecaptcha-badge{z-index:999 !important;bottom: 0px!important ;}</style>';
				echo '<input type="hidden" name="g-recaptcha-response">
				  <script>
				  const steps = document.getElementById(\'recaptcha-steps\');
				  grecaptcha.ready(function() {
					
					grecaptcha.execute(\''.$keys['recaptcha_key_publicv3'].'\', {action: \'examples/v3scores\'}).then(function(token) {
					  $(\'input[name="g-recaptcha-response"]\').val(token);
					  console.log(token);
					});
					
					
				  });
				  </script>';
		}	
			}
		
		}
		
		
	}


	/** Checks the captcha code the user has entered.
	* @return boolean true if the code was correct, false if not.
	* @throws Exception Throws an Exception if the reCAPTCHA keys are not set.
	*/
	
	
	public function check () {
		$keys = self::getKeys(true);
		if (!$keys) {
			throw new Exception(t('Please specify the reCAPTCHA keys'));
		}

		$iph = Loader::helper('validation/ip');
		$js = Loader::helper('json');
		
		$v2=$keys['recaptcha_version']=='v2'?1:0;
		if($v2) {
			$recaptcha_key_private = $keys['recaptcha_key_private'];
		}else{
			$recaptcha_key_private = $keys['recaptcha_key_privatev3'];
		}
		
		
		
		$params = array(
			'secret' => $recaptcha_key_private,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $iph->getRequestIP());

		$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);

		$validateSSL=2;
		$validateSSLPeer=1;
		if (! $keys['recaptcha_validate_ssl']) {
			Log::addEntry(t('Warning: SSL verification disabled'), 'Invisible Recaptcha');
			$validateSSL=0;
			$validateSSLPeer=0;
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $validateSSL);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $validateSSLPeer);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$ret = curl_exec($ch);
		$err = curl_errno($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$errors = false;
		$spam = 1;
		if ($err == 0 && $status == 200) {
			$response = $js->decode($ret);
			if ($response === null) {
				$errors = t('Server error').' - '.t('Cannot decode JSON response');
			} else {
				
				
				if ($response->success === true) {
					
					$spam = false;
					
					
				} else {
					$errors = $response->success;
				}
			}
		} else {
			$errmess = curl_error($ch);
			$errors = t('Server error').' '.$err.' '.$status.' '.$errmess;
		}
		curl_close($ch);

		if ($errors) {
			Log::addEntry($errors, 'Invisible Recaptcha');
			return false;
		}

		if ($spam)
			return false;

		return true;
	}

	/** Save the reCAPTCHA-specific options.
	* @param array $options
	*/
	public function saveOptions($options) {
		$co = new Config();
		$co->setPackageObject(Package::getByHandle('google_recaptcha'));
		$fields = array('recaptcha_key_public', 'recaptcha_key_private', 'recaptcha_validate_ssl','recaptcha_version','recaptcha_key_privatev3','recaptcha_key_publicv3');
		foreach($fields as $name) {
			$value = isset($options[$name]) && is_string($options[$name]) ? trim($options[$name]) : '';
			if(strlen($value)) {
				$co->save($name, $value);
			}
			else {
				$co->clear($name);
			}
		}

		if (! array_key_exists('recaptcha_validate_ssl', $options)) {
			$co->save('recaptcha_validate_ssl', '0');
		}
	}

	/** Returns the reCAPTCHA keys.
	* @param bool $onlyIfAllValid [default: false] Set to true if the keys are needed: if they are not specified you'll get a null.
	* @return null|array
	*/
	private static function getKeys($onlyIfAllValid = false) {
		$keys = array();
		$co = new Config();
		$co->setPackageObject(Package::getByHandle('google_recaptcha'));
		$allValid = true;
		$fields = array('recaptcha_key_public', 'recaptcha_key_private', 'recaptcha_validate_ssl','recaptcha_version','recaptcha_key_privatev3','recaptcha_key_publicv3');
		
		if($co->get('recaptcha_version')=='v2'){
			
			 unset($fields[4], $fields[5]);
			
		}elseif($co->get('recaptcha_version')=='v3'){
			
			 unset($fields[0], $fields[1]);
		}
		
		foreach($fields as $name) {
			$value = $co->get($name);
			if(!is_string($value)) {
				$value = '';
			}
			$keys[$name] = $value;
			if(!strlen($value)) {
				$allValid = false;
			}
		}
		return ($allValid || (!$onlyIfAllValid)) ? $keys : null;
	}
}
