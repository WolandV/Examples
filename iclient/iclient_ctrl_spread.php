<?
ob_start();
header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");
$ncookie = $_GET["s"];
$cookie = base64_decode($_GET["s"]);
$key = $_GET["k"];

if(strlen($key)>0)
{
	$filename = $_SERVER['DOCUMENT_ROOT']."/bitrix/SID.dat";
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);

	if (!empty($contents))
	{
		$salt = md5($contents);
		if(md5($ncookie.$salt)==$key)
		{
			$arr = explode(chr(2), $cookie);
			if(is_array($arr) && count($arr)>0)
			{
				foreach($arr as $str)
				{
					if(strlen($str)>0)
					{
						$host = $_SERVER["HTTP_HOST"];
						if(($pos = strpos($host, ":")) !== false)
							$host = substr($host, 0, $pos);

						$ar = explode(chr(1), $str);
						setcookie($ar[0], $ar[1], $ar[2], $ar[3], $host, $ar[5]);
						
						//logout
						/*if(substr($ar[0], -5) == '_UIDH' && $ar[1] == '')
						{
							session_start();
							$_SESSION["SESS_AUTH"] = Array();
							unset($_SESSION["SESS_AUTH"]);
							unset($_SESSION["OPERATIONS"]);
							unset($_SESSION["SESS_PWD_HASH_TESTED"]);
						}*/
					}
				}
			}
		}
	}
}
ob_end_clean();
?>