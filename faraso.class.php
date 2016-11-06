<?php

/**
 * Faraso domain reseller library for registering (.ir) domains
 * (Faraso Samaneh Pasargad)
 * 
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2016 Hamid Samak
 * @license MIT License
 *
 * NOTE: This library only works with API username and password, So registering for having this credentials is necessary.
 *
 * USAGE:
 * $faraso = new Faraso(USERNAME, PASSWORD);
 * list($result, $message) = $faraso->register('domain.ir', 'handle-irnic', 5, array('ns1.domain.ir', 'ns2.domain.ir', 'ns3.domain.ir', 'ns4.domain.ir'));
 */
class Faraso {
	// username
	private $api_user;

	// password
	private $api_pass;

	// web service url (try systemnic.net if not work)
	private $url = 'http://systemnic.ir/services/?wsdl';

	// system messages based on return codes
	private $message = array(
		0 => 'دامین با موفقیت ثبت شد.',
		1 => 'نام کاربری یا رمز وب سرویس نامعتبر است',
		2 => 'خطای سیستمی',
		3 => 'آی پی شما برای اتصال به وب سرویس معتبر نیست. آی پی مشخص شده در کد خطا را به پشتیبانی سیستم معرفی کنید.',
		4 => 'وضعیت وب سرویس شما غیر فعال , مسدود یا منقضی می باشد.',
		5 => 'سیستم در حال بروز رسانی است لذا امکان اتصال از طریق وب سرویس وجود ندارد.',
		6 => 'شما اجازه دسترسی به این پیام را به یکی از دلایل زیر ندارید:<br />1) پیام متعلق به شما نیست.<br />2) چنین پیامی در سیستم موجود نیست.<br />3) پیام از سیستم حذف شده است.',
		100 => 'درخواست ثبت دامنه شما به یکی از دلایل زیر قابل انجام نیست:<br />1) دامنه توسط فرد دیگری ثبت شده است<br />2) دامنه جز دامنه های تازه آزاد شده است و باید مجددا اقدام کنید تا بتوانید آن را ثبت کنید ( مدت زمان انتظار برای ثبت بین 1 تا 7 روز متغییر می باشد )',
		101 => 'شما اعتبار کافی برای پردازش درخواست مورد نظر را ندارید لطفا حساب خود را شارژ کنید.',
		102 => 'دامنه مورد درخواست شما در پنل سیستم نیک شما موجود نیست لذا عملیات مورد نظر قابل انجام نیست.<br />اگر درخواست شما درخواست تمدید بوده است از گزینه ترنسفر استفاده کنید.',
		103 => 'خطای غیر قابل پیش بینی این خطا پس از ارسال درخواست شما به فیزیک نظری رخ داده است.',
		104 => 'شناسه یا ایمیل مورد نظر شما در سیستم فیزیک نظری موجود نیست.',
		105 => 'شما نمیتوانید از این شناسه برای ثبت دامنه استفاده کنید به یکی از دلایل زیر:<br />1) صاحب امتیاز شناسه روی همه قرار ندارد.<br />2) شناسه شما از نوع محدود است',
		106 => 'خطای غیر قابل پیش بینی از فیزیک نظری در زمان ساخت شناسه',
		107 => 'خطای غیر قابل پیش بینی از فیزیک نظری در زمان تغییر نام سرورها',
		108 => 'شما مجاز به تغییر صاحب امتیاز دامنه نمی باشید برای تغییر صاحب امتیاز مطابق راهنمای تغییر مالکیت عمل کنید',
		109 => 'خطای غیر قابل پیش بینی از فیزیک نظری در زمان تغییر شناسه',
		110 => 'خطای غیر قابل پیش بینی از فیزیک نظری در زمان دریافت مشخصات دامنه',
		111 => 'دامنه مورد نظر در حال حاضر در پنل شما موجود می باشد اگر درخواست تمدید دارید از گزینه تمدید به جای ترنسفر استفاده کنید',
		112 => 'برای تمدید دامنه باید رابط یا نماینده دامنه را بر روی شناسه فراسو سامانه پاسارگاد تنظیم کنید.<br />شما میتوانید برا مخفی ماندن نام فراسو از شماره قرارداد با جای شناسه استفاده کنید.',
		113 => 'در صورتیکه که برای تمدید دامنه درخواست مستقیمی در سبد خرید nic.ir  دارید این درخواست را لغو کنید و مجددا اقدام کنید.<br />در غیر اینصورت منتظر بمانید تا پروسه تمدید دامنه در فیزیک نظری تکمیل شود و سپس برای تمدید مجدد دامنه اقدام کنید.',
		114 => 'دامنه مورد نظر شما قفل شده است لذا مشمول جریمه می باشد برای تمدید و قفل گشایی آن از قسمت پشتیبانی درخواست بزنید.',
		115 => 'خطای غیر قابل پیش بینی از فیزیک نظری در زمان ساخت شناسه',
	);
	
	/**
	 * set api username and password
	 * @param  string $api_user
	 * @param  string $api_pass
	 * @return void
	 */
	public function Faraso($api_user, $api_pass) {
		$this->api_user = $api_user;
		$this->api_pass = $api_pass;
	}

	/**
	 * register new domain
	 * @param  string  $domain      .ir domain name
	 * @param  string  $nicuser     nic.ir handle
	 * @param  integer $duration    currently only 1 and 5 is supported
	 * @param  array   $nameservers domain dns servers (four name servers are required)
	 * @return array                first value is result in boolean and second is responded message 
	 */
	public function register($domain, $nicuser, $duration, $nameservers = array()) {
		try {
			$client = new SoapClient($this->url);

			$res = $client->__soapCall('registerDomain', array(
				'api_user' => $this->api_user,
				'api_pass'=> $this->api_pass,
				'domain' => $domain,
				'period' => $duration,
				'contacts' => array($nicuser, 'fa482-irnic', 'fa482-irnic', 'fa482-irnic'),
				'ns1' => $nameservers[0],
				'ns2' => $nameservers[1],
				'ns3' => $nameservers[2],
				'ns4' => $nameservers[3],
			));

			if (is_soap_fault($res))
				return array(false, 'REGISTER SOAP FAILED');

			$result = isset($res['status']) && empty($res['status']) ? true : false;

			return array($result, $this->message[$res['status']]);
		} catch (TypeEnforcerException $e) {
			return array(false, $e->getMessage());
		}
	}

	/**
	 * renew existing domain
	 * @param  string $domain   .ir domain name
	 * @param  integer $duration    currently only 1 and 5 is supported
	 * @return array                first value is result in boolean and second is responded message 
	 */
	public function renew($domain, $duration) {
		try {
			$client = new SoapClient($this->url);

			$res = $client->__soapCall('renewDomain', array(
				'api_user' => $this->api_user,
				'api_pass'=> $this->api_pass,
				'domain' => $domain,
				'period' => $duration,
			));

			if (is_soap_fault($res))
				return array(false, 'RENEW SOAP FAILED');

			$result = isset($res['status']) && empty($res['status']) ? true : false;

			return array($result, $this->message[$res['status']]);
		} catch (TypeEnforcerException $e) {
			return array(false, $e->getMessage());
		}
	}
}

?>