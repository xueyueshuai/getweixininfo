<?php
namespace Xysclass;
class getweixininfo
{
	//=======【基本信息设置】=====================================
	//
	const APPID = 'wxc6fbe6a4611c9c19';
	const APPSECRET = '916cbc2d257ab7881bf3f71f30db1c3e';
	
	//=======【curl代理设置】===================================
	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
	
	//=======【基本信息设置】===================================== end

	/**
	 * 
	 * 通过跳转获取用户的openid，跳转流程如下：
	 * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
	 * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
	 * 
	 * @return 用户的openid
	 */
	public function getopenid()
	{
		//第一步 通过 用户的许可 获取 code
		if (!isset($_GET['code'])){
			//触发微信返回code码
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
			$url = $this->__CreateOauthUrlForCode($baseUrl);
			Header("Location: $url");
			exit();
		} else {
		    // 第二步 通过 code 获取 openid 和 access_token
		    $code = $_GET['code'];
			$openidAndAccess_token = $this->GetOpenidAndAccess_token($code);

			$openid = $openidAndAccess_token['openid'];
			return $openid;
		}
	}

	/**
	 * 
	 * 通过跳转获取用户的userinfo，跳转流程如下：
	 * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
	 * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
	 * 
	 * @return 用户的openid
	 */

	public function getuserinfo()
	{
		//第一步 通过 用户的许可 获取 code
		if (!isset($_GET['code'])){
			//触发微信返回code码
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
			$url = $this->__CreateOauthUrlForCode($baseUrl,'snsapi_userinfo');
			Header("Location: $url");
			exit();
		} else {
		    $code = $_GET['code'];

		    // 第二步 通过 code 获取 openid 和 access_token
			$openidAndAccess_token = $this->GetOpenidAndAccess_token($code);
			
			// 第三步 通过 openid 和 access_token 获取 userinfo
			$userinfo=$this->GetUserinfoByOpenidAndAccess_token($openidAndAccess_token);

			return $userinfo;
		}
	}


	/**
	 * 
	 * 通过code 从工作平台获取openid机器access_token
	 * @param string $code 微信跳转回来带上的code
	 * 
	 * @return openid
	 */
	public function GetOpenidAndAccess_token($code)
	{
		$url = $this->__CreateOauthUrlForOpenidAndAccess_token($code);
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if(self::CURL_PROXY_HOST != "0.0.0.0" 
			&& self::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, self::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, self::CURL_PROXY_PORT);
		}
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		// $this->data = $data;
		// $openid = $data['openid'];
		// return $openid;
		return $data;
	}


	/**
	 * 
	 * 通过 openid 和 access_token 获取userinfo
	 * @param arr
	 * 
	 * @return userinfo
	 */
	public function GetUserinfoByOpenidAndAccess_token($data)
	{
		$url = $this->__CreateOauthUrlForUserinfo($data);
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if(self::CURL_PROXY_HOST != "0.0.0.0" 
			&& self::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, self::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, self::CURL_PROXY_PORT);
		}
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($res,true);
		return $data;
	}

	


	/**
	 * 
	 * 拼接签名字符串
	 * @param array $urlObj
	 * 
	 * @return 返回已经拼接好的字符串
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 * 
	 * 构造获取code的url连接
	 * @param string $redirectUrl 微信服务器回跳的url，需要url编码
	 * 
	 * @return 返回构造好的url
	 */
	private function __CreateOauthUrlForCode($redirectUrl,$scope='snsapi_base')
	{
		$urlObj["appid"] = self::APPID;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = $scope;
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);

		
		// return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;

		// 通过多一次的跳转，解决了微信限制回调域名只能设置一个的问题
		return "http://hospital.seetest.cn/Plugins/GetWeixinCode-master/get-weixin-code.html?".$bizString;
	}

	/**
	 * 
	 * 构造获取openid和access_toke的url地址 通过code
	 * @param string $code，微信跳转带回的code
	 * 
	 * @return 请求的url
	 */
	private function __CreateOauthUrlForOpenidAndAccess_token($code)
	{
		$urlObj["appid"] = self::APPID;
		$urlObj["secret"] = self::APPSECRET;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}

	/**
	 * 
	 * 构造获取 用户信息的地址 url地址 通过 access_token 和 openid
	 * @param string $code，微信跳转带回的code
	 * 
	 * @return 请求的url
	 */
	private function __CreateOauthUrlForUserinfo($data)
	{
		$urlObj["access_token"] = $data['access_token'];
		$urlObj["openid"] = $data['openid'];
		$urlObj["lang"] = 'zh_CN';

		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
	}

}

