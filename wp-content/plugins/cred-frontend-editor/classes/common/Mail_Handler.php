<?php
/**
 * Mail Handler Class
 * 
 * 
 */
final class CRED_Mail_Handler
{

    private $_isHtml=false;
    private $_doFilter=false;
    private $_contentType=false;
    private $_to=array();
    private $_cc=array();
    private $_bcc=array();
    private $_from=array();
    private $_hasFrom=false;
    private $_headers=array();
    private $_attachments=array();
    private $_body='';
    private $_subject='';
    
    
    public function __construct()
    {
        $this->reset();
    }
    
    public function setHTML($enable, $doFilter=true)
    {
        $this->_isHtml=$enable;
        
        if ($this->_isHtml)
            $this->_doFilter=$doFilter;
        else
            $this->_doFilter=false;
            
        // chainable
        return $this;
    }
    
	//callback function for the regex
	private static function utf8_entity_decode($entity){
		$convmap = array(0x0, 0x10000, 0, 0xfffff);
		return mb_decode_numericentity($entity, $convmap, 'UTF-8');
	}

    public function setSubject($sub='')
    {
		//decode decimal HTML entities added by web browser
		$sub = preg_replace('/&#\d{2,5};/ue', "CRED_Mail_Handler::utf8_entity_decode('$0')", $sub );
		//decode hex HTML entities added by web browser
		$sub = preg_replace('/&#x([a-fA-F0-7]{2,8});/ue', "CRED_Mail_Handler::utf8_entity_decode('&#'.hexdec('$1').';')", $sub );
		//set subject
        $this->_subject=$sub;
        // chainable
        return $this;
    }
    
    public function setBody($body='')
    {
        $this->_body=$body;
        // chainable
        return $this;
    }
    
    public function addHeader($hdr)
    {
        $this->_headers[]=$hdr;
        // chainable
        return $this;
    }
    
    /*public function setAttachment($attach)
    {
    }*/
    
    public function setFrom($_from=array())
    {
        if (is_array($_from) && !empty($_from))
        {
            $this->_from=$_from;
            $this->_hasFrom=true;
        }
        else
        {
            $this->_from=array();
            $this->_hasFrom=false;
        }
        // chainable
        return $this;
    }
    
    public function addAddress($addr)
    {
        $this->_to[]=$addr;
        // chainable
        return $this;
    }
    
    public function addRecipients($addresses)
    {
        if (!is_array($addresses))
            $addresses=explode(',', $addresses);
            
        foreach ((array)$addresses as $address)
        {
            $a=explode(':', $address);
            if (isset($a[1]))
            {
                $to=strtolower(trim($a[0]));
                $a=trim($a[1]);
            }
            else
            {
                $to='to';
                $a=trim($address);
            }
            
            switch ($to)
            {
                case 'to':
                    $this->_to[]=$a;
                    break;
                case 'cc':
                    $this->_cc[]=$a;
                    break;
                case 'bcc':
                    $this->_bcc[]=$a;
                    break;
            }
        }
        // chainable
        return $this;
    }
    
    protected function filter($t)
    {
        return wpautop($t);
    }
    
    protected function buildMail()
    {
        // build header
        $header=array();
        $this->_contentType=($this->_isHtml)?"Content-Type: text/html":"Content-Type: text/plain";
        $header=array_merge($header,$this->_headers);
        $header=array_merge($header,array($this->_contentType));
        
        if (!empty($this->_cc))
        {
            $header=array_merge($header, array("Cc: ".implode(',', $this->_cc)));
        }
        if (!empty($this->_bcc))
        {
            $header=array_merge($header, array("Bcc: ".implode(',', $this->_bcc)));
        }
        
        // build subject
        $subject=$this->_subject;
        
        // build body
        $body=($this->_doFilter)?$this->filter($this->_body):$this->_body;
        
        // build recipient addresses
        $to=$this->_to; //implode(',',$this->_to);
        
        return array('to'=>$to, 'subject'=>$subject, 'body'=>$body, 'header'=>$header);
    }
    
    public function reset()
    {
        $this->_to=array();
        $this->_cc=array();
        $this->_bcc=array();
        $this->_from=array();
        $this->_hasFrom=false;
        $this->_headers=array();
        $this->_attachments=array();
        $this->_body='';
        $this->_subject='';
        $this->setHTML(false);
        // chainable
        return $this;
    }
    
    public function send()
    {
        $data=$this->buildMail();
        //cred_log($data);
        extract($data);
        
        if (count($to)==0)  return false;
        
        if ($this->_hasFrom)
        {
            add_filter( 'wp_mail_from', array(&$this, 'onMailFromFilter'), 10, 1 );
            add_filter( 'wp_mail_from_name', array(&$this, 'onMailFromNameFilter'), 10, 1 );
        }
        if ($this->_isHtml)
        {
            add_filter( 'wp_mail_content_type', array(&$this, 'set_html_content_type'), 10, 1 );
        }
        
        $isSend = wp_mail($to, $subject, $body, $header);
        //$isSend=true;
        //cred_log(array($to, $subject, $body, $header));
        
        if ($this->_hasFrom)
        {
            remove_filter( 'wp_mail_from_name', array(&$this, 'onMailFromNameFilter'), 10, 1 );
            remove_filter( 'wp_mail_from', array(&$this, 'onMailFromFilter'), 10, 1 );
        }
        
        if ($this->_isHtml)
        {
            remove_filter( 'wp_mail_content_type', array(&$this, 'set_html_content_type'), 10, 1 );
        }
        
        //cred_log($isSend);
        return $isSend;
    }
    
    // new email-adress
    public function onMailFromFilter($email) 
    {
        if (isset($this->_from['address']))
            $email=is_email($this->_from['address']);
        return $email;
    }
    
    // new name
    public function onMailFromNameFilter($name) 
    {
        if (isset($this->_from['name']))
            $name=esc_attr($this->_from['name']);
        return $name;
    }

    public function set_html_content_type($type)
    {
        return 'text/html';
    }
}
