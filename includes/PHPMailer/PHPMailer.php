<?php
namespace PHPMailer\PHPMailer;

use Exception as GlobalException;

class PHPMailer
{
    const CHARSET_UTF8 = 'utf-8';
    const ENCODING_8BIT = '8bit';
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    public $Priority = null;
    public $CharSet = self::CHARSET_UTF8;
    public $ContentType = 'text/plain';
    public $Encoding = self::ENCODING_8BIT;
    public $ErrorInfo = '';
    public $From = 'root@localhost';
    public $FromName = 'Root User';
    public $Sender = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $WordWrap = 0;
    public $Mailer = 'smtp';
    public $Sendmail = '/usr/sbin/sendmail';
    public $UseSendmailOptions = true;
    public $Host = 'localhost';
    public $Port = 25;
    public $Helo = '';
    public $SMTPSecure = '';
    public $SMTPAutoTLS = true;
    public $SMTPAuth = false;
    public $SMTPOptions = [];
    public $Username = '';
    public $Password = '';
    public $AuthType = '';
    public $Timeout = 300;
    public $SMTPDebug = 0;
    public $Debugoutput = 'echo';
    public $SMTPKeepAlive = false;
    public $SingleTo = false;

    protected $smtp = null;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $ReplyTo = [];
    protected $all_recipients = [];
    protected $attachment = [];
    protected $CustomHeader = [];
    protected $lastMessageID = '';
    protected $message_type = '';
    protected $boundary = [];
    protected $language = [];
    protected $error_count = 0;
    protected $sign_cert_file = '';
    protected $sign_key_file = '';
    protected $sign_extracerts_file = '';
    protected $sign_key_pass = '';
    protected $exceptions = false;

    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool) $exceptions;
        }
    }

    public function isSMTP()
    {
        $this->Mailer = 'smtp';
    }

    public function isHTML($isHtml = true)
    {
        $this->ContentType = $isHtml ? 'text/html' : 'text/plain';
    }

    public function setFrom($address, $name = '', $auto = true)
    {
        $this->From = trim($address);
        $this->FromName = trim($name);
        if ($auto && empty($this->Sender)) {
            $this->Sender = $this->From;
        }
        return true;
    }

    public function addAddress($address, $name = '')
    {
        return $this->addOrEnqueueAnAddress('to', $address, $name);
    }

    protected function addOrEnqueueAnAddress($kind, $address, $name)
    {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name));
        $this->{$kind}[] = [$address, $name];
        $this->all_recipients[strtolower($address)] = true;
        return true;
    }

    public function send()
    {
        try {
            if (!$this->preSend()) {
                return false;
            }
            return $this->postSend();
        } catch (Exception $exc) {
            $this->mailHeader = '';
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }
            return false;
        }
    }

    public function preSend()
    {
        if (empty($this->to) && empty($this->cc) && empty($this->bcc)) {
            throw new Exception('You must provide at least one recipient email address.');
        }
        return true;
    }

    public function postSend()
    {
        if ($this->Mailer === 'smtp') {
            return $this->smtpSend($this->getMIMEHeader(), $this->getMIMEBody());
        }
        return false;
    }

    protected function getMIMEHeader()
    {
        $header = '';
        $header .= 'Date: ' . date('D, j M Y H:i:s O') . "\r\n";
        $header .= 'To: ' . $this->addrFormat($this->to[0]) . "\r\n";
        $header .= 'From: ' . $this->addrFormat([$this->From, $this->FromName]) . "\r\n";
        $header .= 'Subject: =?UTF-8?B?' . base64_encode($this->Subject) . "?=\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= 'Content-Type: ' . $this->ContentType . "; charset=" . $this->CharSet . "\r\n";
        $header .= "Content-Transfer-Encoding: 8bit\r\n";
        return $header;
    }

    protected function getMIMEBody()
    {
        return $this->Body;
    }

    protected function addrFormat($addr)
    {
        if (empty($addr[1])) {
            return '<' . $addr[0] . '>';
        }
        return '=?UTF-8?B?' . base64_encode($addr[1]) . '?= <' . $addr[0] . '>';
    }

    protected function smtpSend($header, $body)
    {
        $bad_rcpt = [];
        if (!$this->smtpConnect()) {
            throw new Exception('SMTP connect() failed.');
        }
        if (!$this->smtp->mail($this->Sender)) {
            throw new Exception('SMTP Error: ' . $this->smtp->getError()['error']);
        }
        foreach (array_keys($this->all_recipients) as $toaddr) {
            if (!$this->smtp->recipient($toaddr)) {
                $bad_rcpt[] = $toaddr;
            }
        }
        if (count($bad_rcpt) > 0) {
            throw new Exception('SMTP Error: Following recipients failed: ' . implode(', ', $bad_rcpt));
        }
        if (!$this->smtp->data($header . "\r\n" . $body)) {
            throw new Exception('SMTP Error: Data not accepted.');
        }
        $this->smtp->quit();
        return true;
    }

    public function smtpConnect($options = null)
    {
        if (null === $this->smtp) {
            $this->smtp = new SMTP();
        }
        if ($this->smtp->connected()) {
            return true;
        }
        $this->smtp->Timeout = $this->Timeout;
        $tls = ($this->SMTPSecure === self::ENCRYPTION_STARTTLS);
        $ssl = ($this->SMTPSecure === self::ENCRYPTION_SMTPS);
        $host = ($ssl ? 'ssl://' : '') . $this->Host;

        if (!$this->smtp->connect($host, $this->Port, $this->Timeout, $this->SMTPOptions)) {
            return false;
        }
        if (!$this->smtp->hello(gethostname())) {
            return false;
        }
        if ($this->SMTPAutoTLS && $tls && $this->smtp->getServerExt('STARTTLS')) {
            if (!$this->smtp->startTLS()) {
                return false;
            }
            $this->smtp->hello(gethostname());
        }
        if ($this->SMTPAuth) {
            if (!$this->smtp->authenticate($this->Username, $this->Password)) {
                throw new Exception('SMTP Error: Could not authenticate.');
            }
        }
        return true;
    }

    protected function setError($msg)
    {
        $this->error_count++;
        $this->ErrorInfo = $msg;
    }
}