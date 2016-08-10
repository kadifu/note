<? php
/**
* 解决 php5.6后 邮件发送失败的问题 
* 函数位于 class.smtp.php
*/
public function Connect($host, $port = 0, $tval = 30) {
    // set the error val to null so there is no confusion
    $this->error = null;

    // make sure we are __not__ connected
    if($this->connected()) {
      // already connected, generate error
      $this->error = array('error' => 'Already connected to a server');
      return false;
    }

    if(empty($port)) {
      $port = $this->SMTP_PORT;
    }
    
    // begin-modify: Added by Evan on 2016-08-03
    // 原因说明：PHP5.6后ssl连接需要校验本地证书，这会导致连接失败。以下代码可以禁止校验。
    /*
    // Connect to the smtp server
    $this->smtp_conn = @fsockopen($host,    // the host of the server
    		$port,    // the port to use
    		$errno,   // error number if any
    		$errstr,  // error message if any
    		$tval);   // give up after ? secs
    */
    $options = array(
    		'ssl' => array(
    				'verify_peer' => false,
    				'verify_peer_name' => false,
    				'allow_self_signed' => true
    		)
    );
    $socket_context = stream_context_create($options);
    $this->smtp_conn = @stream_socket_client(
    		$host . ":" . $port,
    		$errno,
    		$errstr,
    		$tval,
    		STREAM_CLIENT_CONNECT,
    		$socket_context
    		);
    //end-modify
                        	
    // verify we connected properly
    if(empty($this->smtp_conn)) {
      $this->error = array('error' => 'Failed to connect to server',
                           'errno' => $errno,
                           'errstr' => $errstr);
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ": $errstr ($errno)" . $this->CRLF . '<br />');
      }
      return false;
    }

    // SMTP server can take longer to respond, give longer timeout for first read
    // Windows does not have support for this timeout function
    if(substr(PHP_OS, 0, 3) != 'WIN') {
     $max = ini_get('max_execution_time');
     if ($max != 0 && $tval > $max) { // don't bother if unlimited
      @set_time_limit($tval);
     }
     stream_set_timeout($this->smtp_conn, $tval, 0);
    }

    // get any announcement
    $announce = $this->get_lines();

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $announce . $this->CRLF . '<br />');
    }

    return true;
  }