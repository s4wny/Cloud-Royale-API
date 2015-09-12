<?php

/**
 * CloudRoyaleAPI
 *
 * Example usage:
 *	  $api = new CloudRoyaleAPI();
 *‮    $api->login();
 *‮    $servers = $api->getServers();
 *
 * @author Andreas Wallström
 */
class CloudRoyaleAPI
{
	private $username = "";
	private $password = "";

	// cURL handler
	private $ch;

	/**
	 * Init cURL
	 */
	public function __construct($username = null, $password = null)
	{
    	if (!function_exists('curl_init')){
    	    die('You need cURL to use this class');
    	}

    	$this->username = ($username) ? $username : $this->username;
    	$this->password = ($password) ? $password : $this->password;

		$this->ch = curl_init();

		$cookieFile = tempnam ("/tmp", "CURLCOOKIE");

		$options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 60,      // timeout on connect
            CURLOPT_TIMEOUT        => 61,      // timeout on response

            // Give cloudroyale some way of tracking us who uses an API
            CURLOPT_USERAGENT	   => "Andreas Wallström, API v1.0.0",

            /*
             * Don't verify SSL (It should really be set to true but
             * it doesn't work on my machine)
             */
            CURLOPT_SSL_VERIFYPEER => false, 

            CURLOPT_COOKIESESSION  => true,
            CURLOPT_COOKIEFILE	   => $cookieFile
        ];
        curl_setopt_array($this->ch, $options);
	}

	/**
	 * Login to cloudroyale
	 *
	 * Returns TRUE on success, else the HTML code returned
	 */
	public function login()
	{
		$data = [
			'username' => $this->username,
			'password' => $this->password
		];

		$html = $this->curlPost('https://cloudroyale.se/login', $data);

		return ($html === "") ? true : $html;
	}


	/**
	 * Get status about a specific server
	 */
	public function status($serverID)
	{
		return $this->curlGet('https://cloudroyale.se/admin/ajax.php?vm_status&id='. $serverID);
	}

	/**
	 * Get all server ID's
	 *
	 * Return format:
	 * 
	 * 	array(
	 *		'*serverid*' => '*servername*',
	 *		'sdfsdf3sdf' => 'http server',
	 *		'dfk983cdkf' => 'vpn',
	 * 	)
	 *
	 */
	public function getServers()
	{
		$html = $this->curlGet('https://cloudroyale.se/admin/');

		$matches = array();

		// Assuming the server_id and server_name exsits in HTML formated like:
		// 		<a href="/admin/vps?id=*alphanum*">*servername*</a>
		// Group 1: server id
		// Group 2: server name
		preg_match_all('~<a href="/admin/vps\?id=([\w]+)">(.+?)(?=<)~', $html, $matches);

		return array_combine($matches[1], $matches[2]);
	}

	/**
	 * Start a server
	 */
	public function startServer($serverID)
	{
		$data = [
			'action' => 'startup',
			'id' => $serverID
		];

		return $this->curlPost('https://cloudroyale.se/admin/vps?id='. $serverID, $data);
	}

	/**
	 * Stop a server
	 */
	public function stopServer($serverID)
	{
		$data = [
			'action' => 'shutdown',
			'id' => $serverID
		];
		
		return $this->curlPost('https://cloudroyale.se/admin/vps?id='. $serverID, $data);
	}


	/**
	 * Add SSH_KEYS
	 */
	public function addSSHKeys($serverID)
	{
		$data = [
			'action' => 'set_ssh_keys',
			'id'     => $serverID
		];
		
		return $this->curlPost('https://cloudroyale.se/admin/vps?id='. $serverID, $data);
	}

	/**
	 * Create new server
	 */
	public function createServer($config = array())
	{
		$defaultConfig = [
			// Server name
			'hostname' => 'server name',

			// What OS, 70 = ubuntu 14.04
			'template_id' => 70,

			// ??
			'resources' => 'advanced',

			// 1 = 1GB
			'memory' => 1,

			// Number of CPUs
			'cpus' => 1,

			// 2  = HDD
			// 22 = SSD
			'data_store_group_primary_id' => 2,

			// Disk size in GB
			'primary_disk_size' => 20,
		];

		$data = array_merge($defaultConfig, $config);
		
		return $this->curlPost('https://cloudroyale.se/admin/create', $data);
	}


	/**
	 * GET request
	 */
	private function curlGet($url)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_POST, 0);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, null);

		return $this->curlExec();
	}

	/**
	 * POST request
	 */
	private function curlPost($url, $data)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);

		return $this->curlExec();
	}

	/**
	 * Exec cURL, catch errors if any
	 */
	private function curlExec() {
		$result = curl_exec($this->ch);

		if($result === false)
			die('cURL error:'. curl_error($this->ch));
		else
			return $result;
	}
}