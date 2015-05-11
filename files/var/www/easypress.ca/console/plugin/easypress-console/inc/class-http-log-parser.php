<?php
/**
 * HTTP log parserer
 * based on code by Marcelo Altmann <altmannmarcelo@gmail.com>
 * 
 * test harness
 * $stats = new HTTP_Log_Parser();
 * $stats->set_log('wellesleyinstitute.com');
 * var_dump( $stats->visitors() );
 * 
 */
class HTTP_Log_Parser
{

	private $map = array(
		'ip' => 0,
		'' => 1,
		'date' => 3,
		'timeZoneOffset' => 4, //confirm
		'method' => 5, //GET , POST, HEAD
		'request' => 6,
		'protocol' => 7, //HTTP1.1
		'return_code' => 8, //200, 404
		'size' => 9,
		'refer' => 10,
		'userAgent' => 11,
	);
	private $log;
	private $ip = array();
	private $log_dir = '/var/log/nginx/';
	//private $log_ext = '.access.log';
	
	public function __construct()
	{
		
	}

	private function bytes_to_size($bytes, $precision = 2)
	{
		$kilobyte = 1024;
		$megabyte = $kilobyte * 1024;
		$gigabyte = $megabyte * 1024;
		$terabyte = $gigabyte * 1024;

		if (($bytes >= 0) && ($bytes < $kilobyte))
		{
			return $bytes . ' B';
		}
		elseif (($bytes >= $kilobyte) && ($bytes < $megabyte))
		{
			return round($bytes / $kilobyte, $precision) . ' KB';
		}
		elseif (($bytes >= $megabyte) && ($bytes < $gigabyte))
		{
			return round($bytes / $megabyte, $precision) . ' MB';
		}
		elseif (($bytes >= $gigabyte) && ($bytes < $terabyte))
		{
			return round($bytes / $gigabyte, $precision) . ' GB';
		}
		elseif ($bytes >= $terabyte)
		{
			return round($bytes / $terabyte, $precision) . ' TB';
		}
		else
		{
			return $bytes . ' B';
		}
	}

	/**
	 * Calculate unique visitors and visits
	 *
	 */
	public function visitors()
	{
		$visitors = $visits = $hits = $transfered = 0;
		$handle = @fopen($this->log, "r");
		if ($handle)
		{
			while (($buffer = fgets($handle, 4096)) !== false)
			{
				$exploded = explode(' ', $buffer);
				if ( isset( $exploded[$this->map['return_code']] ) ) {
					if ( $exploded[$this->map['return_code']] != '404' || $exploded[$this->map['return_code']] != '400' )
						$hits++;
						$transfered += (float)$exploded[$this->map['size']];
				}
				$time = str_replace('[', '', $exploded[$this->map['date']]);
				$timeOffset = str_replace(']', '', $exploded[$this->map['timeZoneOffset']]);
				try {
					$logDate = new DateTime($time . ' ' . $timeOffset);
				}
				catch ( Exception $e ) {
					error_log( $e->getMessage() );
					continue;
				}
				$ip = $exploded[$this->map['ip']];
				if (isset($this->ip[$exploded[$this->map['ip']]]['hits']))
					$this->ip[$exploded[$this->map['ip']]]['hits']++;
				else
					$this->ip[$exploded[$this->map['ip']]]['hits'] = 1;

				// if the same visitor is idle for more than 30 min (1800 sec) it's considered a new visit.
				$this_date = (float)$logDate->format('U') - 1800;
				if ( isset( $this->ip[$exploded[$this->map['ip']]]['vtime'] ) ) {
					$last_date = $this->ip[$exploded[$this->map['ip']]]['vtime'];
					//echo "$last_date :: $this_date\n";
					if ( $last_date < $this_date )
						$visits++;
				}
				$this->ip[$exploded[$this->map['ip']]]['vtime'] = (float)$logDate->format('U');
			}
			fclose($handle);
		}
		$visitors = sizeof($this->ip);
		$visits += $visitors;	
		return array('visitors' => $visitors, 'visits' => $visits, 'hits' => $hits, 'transfered' => $this->bytes_to_size( $transfered ), 'bytes_transfered' => $transfered );
	}

	public function set_log($log, $log_ext = '.access.log' )
	{
		$this->log_ext = $log_ext;
		$this->log = $this->log_dir . $log . $this->log_ext;
		try
		{
			$handle = fopen($this->log, "r");
		}
		catch (Exception $e)
		{
			echo $e->getMessage() . "\n";
			exit();
		}
	}
}
