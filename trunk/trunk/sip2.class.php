<?php
/**
* SIP2 Class 
*
* @author John Wohlers
* @copyright 2008 John Wohlers
* @licence http://opensource.org/licenses/gpl-3.0.html
*
*/

class sip2 {

	/* Public variables for configuration */
	public $hostname;
	public $port = 6002; /* default sip2 port for Sirsi */
	public $library = 'TODD';
	public $language = '001'; /* 001 = english */

	/* Patron ID */
	public $patron = ''; /* AA */
	public $patronpwd = ''; /* AD */
	
	/*terminal password */
	public $AC = ''; /*AC */
	
	/* Maximum number of resends allowed before get_message gives up */
	public $maxretry = 3;
	
	/* Message Terminator  */
	public $MsgTerminator = "\r\n";
	
	/* Login Variables */
	public $UIDalgorithm = 0; /* 0 = unencrypted, default */
	public $PWDalgorithm = 0; /*  */
	public $scLocation = '';  /* Location Code */

	/* Debug */
	public $debug = false;
	
	/* Private variables for building messages */
	private $AO = 'WohlersSIP';
	private $AN = 'SIPCHK';
	
	/* Private variable to hold socket connection */
	private $socket;
	
	/* Sequence number counter */
	private $seq = -1;

	/* resend counter */
	private $retry = 0;
	
	function msgSCStatus($status,$width,$version=2) {
		if ($version > 3) {
			$version = 2;
		}
		$message = sprintf( "99%1s%3s%03.2fAY%1s|AZ",
			$status,
			$width,
			$version,
			$this->_getseqnum()
		);

		return $message . $this->_crc($message) . $this->MsgTerminator;
	}
	
	function msgItemInformation($item) {
		$message = sprintf( "17%18sAO%s|AB%s|AC%s|AY%1sAZ",
			$this->_datestamp(),		
			$this->AO,
			$item,
			$this->AC,			
			$this->_getseqnum()			
		);
		return $message . $this->_crc($message) . $this->MsgTerminator;
	
	}

	function msgRenew($item,$title,$nbDueDate='',$itmProp ='',$fee='N') {
		$message = sprintf( "29%1s%1s%18s%18sAO%s|AA%s|AD%s|AB%s|AJ%s|AC%s|CH%s|BO%1s|AY%1sAZ",
			"N", /* 3rd party allowed */
			"N", /* No Block */
			$this->_datestamp(),
			$nbDueDate,
			$this->AO,
			$this->patron,
			$this->patronpwd,
			$item,
			$title,
			$this->AC, /*Terminal Password */
			$itmProp,
			$fee,
			$this->_getseqnum()				
			
		);
		return $message . $this->_crc($message) . $this->MsgTerminator;

	}

	function msgPatronStatusRequest() {
		$message = sprintf( "23%3s%18sAO%s|AA%s|AC%s|AD%s|AY%1sAZ",
			$this->language,
			$this->_datestamp(),		
			$this->AO,
			$this->patron,
			$this->AC,	
			$this->patronpwd,			
			$this->_getseqnum()			
		);
		return $message . $this->_crc($message) . $this->MsgTerminator;
	
	}
	function msgPatronInformation($type,$start="1",$end="5") {
		$summary['hold']     = 'Y     ';
		$summary['overdue']  = ' Y    ';
		$summary['charged']  = '  Y   ';
		$summary['fine']     = '   Y  ';
		$summary['recall']   = '    Y ';
		$summary['unavail']  = '     Y';
		
		/* Request patron information */
		$message = sprintf("63%3s%18s%-10sAO%s|AA%s|AC%s|AD%s|BP%05d|BQ%05d|AY%1sAZ",
			$this->language,
			$this->_datestamp(),		
			$summary[$type],
			$this->AO,
			$this->patron,
			$this->AC,
			$this->patronpwd,
			$start,
			$end,
			$this->_getseqnum()
		);
		return $message . $this->_crc($message) . $this->MsgTerminator;
	}
	
	function msgHold($mode,$expDate='',$holdtype,$item,$title,$fee='N') {
		/* mode validity check */
		if (strpos('-+*',$mode) === false) {
			/* not a valid mode - exit */
			$this->_debugmsg( "SIP2: Invalid hold mode: {$mode}");
			return false;
		}
		
		if ($holdtype < 1 || $holdtype > 9) {
			/*
			Valid hold types range from 1 - 9 
			1	other
			2	any copy of title
			3	specific copy
			4	any copy at a single branch or location
			
			*/
			$this->_debugmsg( "SIP2: Invalid hold type code: {$holdtype}");
			return false;
		}
		
		$message = sprintf("15%1s%18sBW%18s|BS%s|BY%1s|AO%s|AA%s|AD%s|AB%s|AJ%s|AC%s|BO%s|AY%1sAZ",
			$mode,
			$this->_datestamp(),
			$expDate,
			$this->scLocation,
			$holdtype,
			$this->AO,
			$this->patron,
			$this->patronpwd,
			$item,
			$title,
			$this->AC,
			$fee, /* user has agreed to a fee notice */
			$this->_getseqnum()
		);
		return $message . $this->_crc($message) . $this->MsgTerminator;

	}
	
	function msgEndPatronSession() {
		$message = sprintf("35%18sAO%s|AA%s|AC%s|AD%s|AY%1sAZ",
			$this->_datestamp(),
			$this->AO,
			$this->patron,
			$this->AC,
			$this->patronpwd,
			$this->_getseqnum()
		);
		return $message . $this->_crc($message) . $this->MsgTerminator;
	}

	function msgLogin($sipLogin, $sipPassword) {
		$message = sprintf("93%1s%1sCN%s|CO%s|CP%s|AY%1sAZ",
			$this->UIDalgorithm,
			$this->PWDalgorithm,
			$sipLogin,
			$sipPassword,
			$this->scLocation,
			$this->_getseqnum()
		);
	
		return $message . $this->_crc($message) . $this->MsgTerminator;
	}
	
	function msgRequestACSResend () {
		$message = sprintf("97AZ");
		return $message . $this->_crc($message) . $this->MsgTerminator;
	}
	
	function parseRenewResponse ($response) {
		/* Response Example:  300NUU20080228    222232AOWOHLERS|AAX00000241|ABM02400028262|AJFolksongs of Britain and Ireland|AH5/23/2008,23:59|CH|AFOverride required to exceed renewal limit.|AY1AZCDA5 */
		$result['fixed'] = 
		array( 
			'Ok'      			=> substr($response,2,1),
			'RenewalOk'			=> substr($response,3,1),
            'Magnetic'			=> substr($response,4,1),
            'Desensitize'		=> substr($response,5,1),
            'TransactionDate'   => substr($response,6,18),
         );    


		$result['variable'] = $this->_parsevariabledata($response, 24);

		return $result;
	}
	
	function parseHoldResponse ($response) {

		$result['fixed'] = 
		array( 
			'Ok'				=> substr($response,2,1),
			'available'			=> substr($response,3,1),
            'TransactionDate'	=> substr($response,4,18),
            'ExpirationDate'	=> substr($response,22,18)			
         );    


		$result['variable'] = $this->_parsevariabledata($response, 24);

		return $result;
	}	

	function parsePatronInfoResponse ($response) {
	
		$result['fixed'] = 
		array( 
            'PatronStatus'      => substr($response,2,14),
            'Language'          => substr($response,16,3),
            'TransactionDate'   => substr($response,19,18),
            'HoldCount'         => intval (substr($response,37,4)),
            'OverdueCount'      => intval (substr($response,41,4)),
            'ChargedCount'      => intval (substr($response,45,4)),
            'FineCount'         => intval (substr($response,49,4)),
            'RecallCount'       => intval (substr($response,53,4)),
            'UnavailableCount'  => intval (substr($response,57,4))
         );    

		$result['variable'] = $this->_parsevariabledata($response, 61);
		return $result;
	}

	function parseItemInfoResponse ($response) {
		$result['fixed'] = 
    array( 
            'CirculationStatus' => intval (substr($response,2,2)),
            'SecurityMarker'    => intval (substr($response,4,2)),
            'FeeType'           => intval (substr($response,6,2)),
            'TransactionDate'   => substr($response,8,18),
         );    

		$result['variable'] = $this->_parsevariabledata($response, 26);

		return $result;
	}
	
	function parseEndSessionResponse ($response) {
		/*   Response example:  36Y20080228 145537AOWOHLERS|AAX00000000|AY9AZF474   */
		
		$result['fixed'] = 
		array( 
            'EndSession'      => substr($response,2,1),
            'TransactionDate'   => substr($response,3,18),
         );    


		$result['variable'] = $this->_parsevariabledata($response, 21);
		
	return $result;
	}
	
/* -- Core utility functions */	
	function _datestamp() {
		return date('Ymd    His');
	}

	function _parsevariabledata($response, $start) {

		$result = array();
		$result['Raw'] = explode("|", substr($response,$start,-7));
		foreach ($result['Raw'] as $item) {
			$field = substr($item,0,2);
			$value = substr($item,2);
			$clean = trim($value, "\x00..\x1F");
			if (trim($clean) <> '') {
				$result[$field][] = $clean;
			}
		}		
		$result['AZ'][] = substr($response,-5);

	return ($result);
	}

	function _crc($buf) {
		/* Calculate CRC  */
		$sum=0;

		$len = strlen($buf);
		for ($n = 0; $n < $len; $n++) {
			$sum = $sum + ord(substr($buf,$n,1));
		} 

		$crc = -($sum & 0xFFFF);

		return substr(sprintf ("%4X", $crc),4);
	} /* end crc */	

	function _getseqnum() {
		/* Get a sequence number for the AY field */
		/* valid numbers range 0-9 */
		$this->seq++;
		if ($this->seq > 9 ) {
			$this->seq = 0;
		}
		return ($this->seq);
	}
	
	function get_message ($message) {
		/* sends the current message, and gets the response */
		/*Need to add in error checking (CRC) and retransmission ability */
		$result ="";
		$terminator = "";

		
		$this->_debugmsg( "SIP2: Sending SIP2 request...");
		socket_write($this->socket, $message, strlen($message));

		$this->_debugmsg( "SIP2: Request Sent, Reading response");

		while ($terminator != "\x0D") {
			$nr = socket_recv($this->socket,$terminator,1,0);
			$result = $result . $terminator;
		}

		$this->_debugmsg("SIP2: {$result}");

		/* test message for CRC validity */
		if ($this->_check_crc($result)) {
			/* reset the retry counter on success send */
			$this->retry=0;
			$this->_debugmsg("SIP2: Message from ACS passed CRC check");
		} else {
			/* CRC check failed, request a resend */
			$this->retry++;
			if ($this->retry++ < $this->maxretry) {
				/* try again */
				$this->_debugmsg("SIP2: Message failed CRC check, retrying ({$this->retry})");
				
				$this->get_message($message);
			} else {
				/* give up */
				$this->_debugmsg("SIP2: Failed to get valid CRC after {$this->maxretry} retries.");
				return false;
			}
		}
		return $result;
	}	

	function connect() {

		/* Socket Communications  */
		$this->_debugmsg( "SIP2: --- BEGIN SIP communication ---");  
		
		/* Get the IP address for the target host. */
	    $address = gethostbyname($this->hostname);

	    /* Create a TCP/IP socket. */
	    $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

		/* check for actaul truly false result */
	    if ($this->socket === false) {
			$this->_debugmsg( "SIP2: socket_create() failed: reason: " . socket_strerror($this->socket));
			return false;
	    } else {
			$this->_debugmsg( "SIP2: Socket Created" ); 
		}
		$this->_debugmsg( "SIP2: Attempting to connect to '$address' on port '{$this->port}'..."); 

		/* open a connection ot the ost */
	    $result = socket_connect($this->socket, $address, $this->port);
	    if (!$result) {
			$this->_debugmsg("SIP2: socket_connect() failed.\nReason: ($result) " . socket_strerror($result));
		} else {
			$this->_debugmsg( "SIP2: --- SOCKET READY ---" );
		}
		/* return the result from the socket connect */
		return $result;
	
	}	
	
	function disconnect () {
		/*  Close the socket */
		socket_close($this->socket);
	}
	
	function _debugmsg($message) {
		/* custom debug function,  why repeat the check for the debug flag in code... */
		if ($this->debug) { 
			trigger_error( $message, E_USER_NOTICE); 
		}	
	}
	
	function _check_crc($message) {
		/* test the recieved message's CRC by generating our own CRC from the message */
		$test = preg_split('/(.{4})$/',trim($message),2,PREG_SPLIT_DELIM_CAPTURE);

		if ($this->_crc($test[0]) == $test[1]) {
			return true;
		} else {
			return false;
		}
	}
}

?>
