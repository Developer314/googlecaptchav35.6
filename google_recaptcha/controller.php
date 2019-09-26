<?php  defined('C5_EXECUTE') or die('Access denied.');

class GoogleRecaptchaPackage extends Package {

	protected $pkgHandle = 'google_recaptcha';
	protected $appVersionRequired = '5.6';
	protected $pkgVersion = '0.1.0';

	public function getPackageName() {
		return t('Google reCAPTCHA');
	}

	public function getPackageDescription() {
		return t('Enable V2/V3 invisible reCAPTCHA');
	}

	public function install() {
		$pkg = parent::install();
		$this->installOrUpgrade($pkg);
	}

	public function on_start(){
		
		$co = new Config();
		$co->setPackageObject(Package::getByHandle('google_recaptcha'));
	}

	public function upgrade() {
		$currentVersion = $this->getPackageVersion();
		parent::upgrade();
		$this->installOrUpgrade($this, $currentVersion);
	}

	private function installOrUpgrade($pkg) {
		$currentLocale = Localization::activeLocale();
		if ($currentLocale != 'en_US') {
			Localization::changeLocale('en_US');
		}
		Loader::model('system/captcha/library');
		if(!SystemCaptchaLibrary::getByHandle('google_recaptcha')) {
			SystemCaptchaLibrary::add('google_recaptcha', t('Google reCAPTCHA'), $pkg);
		}
		if ($currentLocale != 'en_US') {
			Localization::changeLocale($currentLocale);
		}
	}

	public function uninstall() {
		Loader::model('system/captcha/library');
		$active = SystemCaptchaLibrary::getActive();
		if($active && ($active->getSystemCaptchaLibraryHandle() == 'google_recaptcha')) {
			foreach(SystemCaptchaLibrary::getList() as $anotherCaptcha) {
				if($anotherCaptcha->getSystemCaptchaLibraryHandle() != 'google_recaptcha') {
					$anotherCaptcha->activate();
					break;
				}
			}
		}
		parent::uninstall();
	}
}
