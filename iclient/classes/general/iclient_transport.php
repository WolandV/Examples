<?
class CIControllerClientRequestTo extends CControllerClientRequestTo
{
	function Send($page="/bitrix/admin/icontroller_ws.php")
	{
		$this->Sign();
		
		$server_name = strtolower(trim(COption::GetOptionString("main", "controller_url", ""), "/ \r\n\t"));
		
		if(substr($server_name, 0, 7)=='http://')
		{
			$server_name = substr($server_name, 7);
		}
		elseif(substr($server_name, 0, 8)=='https://')
		{
			$server_name = substr($server_name, 8);
			$server_port = 443;
		}

		$server_port = 80;
		if(preg_match('/.+:([0-9]+)$/', $server_name, $matches))
		{
			$server_port = $matches[1];
			$server_name = substr($server_name, 0, 0 - strlen($server_port) - 1);
		}

		$proxy_url = COption::GetOptionString("main", "controller_proxy_url", "");
		$proxy_port = COption::GetOptionString("main", "controller_proxy_port", "");
		$proxy_user = COption::GetOptionString("main", "controller_proxy_user", "");
		$proxy_password = COption::GetOptionString("main", "controller_proxy_password", "");

		// соединяемся с удаленным сервером
		$bUseProxy = (strlen($proxy_url) > 0 && strlen($proxy_port) > 0);

		if($bUseProxy)
		{
			$proxy_port = intval($proxy_port);
			if ($proxy_port <= 0)
				$proxy_port = 80;

			$requestIP = $proxy_url;
			$requestPort = $proxy_port;
		}
		else
		{
			$requestIP = $server_name;
			$requestPort = $server_port;
		}

		$conn = @fsockopen(($requestPort==443? 'ssl://': '').$requestIP, $requestPort, $errno, $errstr, 30);
		
		if(!$conn)
		{
			$this->Debug("We can't send request to the $server_name:$server_port from member#".$this->member_id."(".$this->secret_id."):\r\n".$strError);
			$strError = GetMessage("MAIN_CMEMBER_ERR5").$server_name.":".$server_port." (".$errstr.")";
			if(is_object($GLOBALS["APPLICATION"]))
			{
				$e = new CApplicationException(htmlspecialcharsex($strError));
				$GLOBALS["APPLICATION"]->ThrowException($e);
			}
			return false;
		}

		$strVars = $this->MakeRequestString();

		// запускаем, получаем результат
		if ($bUseProxy)
		{
			$strRequest = "POST http://".$server_name.":".$server_port.$page." HTTP/1.1\r\n";
			if (strlen($proxy_user) > 0)
				$strRequest .= "Proxy-Authorization: Basic ".base64_encode($proxy_user.":".$proxy_password)."\r\n";
		}
		else
		{
			$strRequest = "POST ".$page." HTTP/1.0\r\n";
		}
		
		global $COOKIE_STORAGE;
		if(is_array($COOKIE_STORAGE))
			$COOKIE_STORAGE = array();
		if(!$COOKIE_STORAGE && $_COOKIE)
		{
			foreach ($_COOKIE as $key=>$value)
			{
				if(0 === strpos($key, 'ICTRL_'))
				{
					$COOKIE_STORAGE[$key] = array();
					$COOKIE_STORAGE[$key]['VALUE'] = $value;
					$COOKIE_STORAGE[$key]['FROM_BROWSER'] = true;
				}
			}
		}
		$CookieStr = '';
		$ct = time();
		foreach ($COOKIE_STORAGE as $key=>$value)
		{
			if (!$value['TIME'] || $value['TIME'] > $ct) 
				$CookieStr .= urlencode($key).'='.urlencode($value['VALUE']).'; ';
		}

		
		$strRequest .= "User-Agent: BitrixControllerMember\r\n";
		$strRequest .= "Accept: */*\r\n";
		$strRequest .= "Host: ".$server_name."\r\n";
		$strRequest .= "Accept-Language: en\r\n";
		$strRequest .= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest .= "Content-length: ".strlen($strVars)."\r\n";
		if(strlen($CookieStr) > 2)
			$strRequest .= "Cookie: ".substr($CookieStr, 0, -2)."\r\n";
		$strRequest .= "\r\n";
		$strRequest .= $strVars."\r\n";

		
		$this->Debug(
				"We send request to the $server_name:$server_port from member#".$this->member_id."(".$this->secret_id."):\r\n".
				"Packet:".print_r($this, true)."\r\n".
				"$strVars\r\n"
				);
		fputs($conn, $strRequest);
		
		
		$header = '';
		while (($line = fgets($conn, 4096)) && $line!="\r\n")
		$header.=$line;

		$result = '';
		while ($line = fread($conn, 4096))
		$result .= $line;

		fclose($conn);
		
		
		
		
/*		$header_explode = explode("\r\n", $header);

		foreach ($header_explode as $key=>$header_line)
		{
			if (strpos($header_line, 'Set-Cookie') === 0)
			{
				$parsed_string = substr($header_line, 11);
				$pairs = explode(';', $parsed_string);
				foreach ($pairs as $key2=>$pair)
				{
					$trim_pair = trim($pair);
					$key_value = explode('=', $trim_pair);
					if ($key2 == 0)
					{
						$CookieKey = $key_value[0];
						$key_value[0] = 'value';
					}
					$COOKIE_STORAGE[$CookieKey][$key_value[0]] = $key_value[1];
					
				}
			}
		}
*/
		//$l = preg_match_all('#^Set-Cookie:\s+?(.*?)=(.*?);\s+?(expires=(.*?);\s+)?#m', $header, $matches);
		$l = preg_match_all('#^Set-Cookie:\s+?(.*?)=(.*?);\s+?(expires=(.*?)(;|$))?#m', $header, $matches);
		if($l && count($matches) >= 3)
			foreach ($matches[0] as $k=>$m)
			{
				$name = $matches[1][$k];
				if(0 !== strpos($name, 'ICTRL_')) continue;
				$value = $matches[2][$k];
				$COOKIE_STORAGE[$name] = array();
				$COOKIE_STORAGE[$name]['VALUE'] = $value;
				if(strlen($matches[3][$k]))
				{
					$time = strtotime($matches[4][$k]);
					$COOKIE_STORAGE[$name]['TIME'] = $time;
				}
				else
					$COOKIE_STORAGE[$name]['TIME'] = null;
				$COOKIE_STORAGE[$name]['FOLDER'] = '/';	
				$COOKIE_STORAGE[$name]['DOMAIN'] = '';
				$COOKIE_STORAGE[$name]['SECURE'] = null;
				$COOKIE_STORAGE[$name]['FROM_BROWSER'] = false;
			}

		$ar_result = array();
		$packet_result = new __CControllerPacketResponse();
		$packet_result->secret_id = $this->secret_id;
		$packet_result->ParseResult($result);

		$this->Debug(
				"We get response from $server_name:$server_port to member#".$packet_result->member_id."(".$this->secret_id."):\r\n".
				"Packet (security check ".($packet_result->Check()?"passed":"failed")."): ".print_r($packet_result, true)."\r\n".
				$result."\r\n"
				);

		return $packet_result;
		$oResponsePacket = $packet_result;
/*		$oResponsePacket = __CControllerPacketRequest::Send(COption::GetOptionString("main", "controller_url", ""), $page);*/
		if($oResponsePacket === false)
			return false;

		$oResponse = new CControllerClientResponseFrom($oResponsePacket);
		return $oResponse;
	}
}
?>