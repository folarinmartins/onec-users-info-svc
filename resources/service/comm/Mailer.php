<?php
namespace comm;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use model\Model;
use contract\Request;
use contract\Response;
use contract\Message;
use helper\Utility;

class Mailer{
	public static function send(string $subject, array $to,string $html,string $text,array $attachments=[],string $from=null,string $replyTo=null,array $cc=[],array $bcc=[]):bool{
		global $response;
		global $File;
		$mail = new PHPMailer();
		try{
			//Server settings
			// SMTP::DEBUG_OFF = off (for production use)
			// SMTP::DEBUG_CLIENT = client messages
			// SMTP::DEBUG_SERVER = client and server messages
			$mail->SMTPDebug = SMTP::DEBUG_OFF;//SMTP::DEBUG_OFF;
			$mail->isSMTP();

			//Set the hostname of the mail server
			$mail->Host = 'smtp.gmail.com';
			// use
			// $mail->Host = gethostbyname('smtp.gmail.com');
			// if your network does not support SMTP over IPv6

			//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
			$mail->Port = 587;

			//Set the encryption mechanism to use - STARTTLS or SMTPS
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;

			//Username to use for SMTP authentication - use full email address for gmail
/*  			$mail->Username = 'foodresinc@gmail.com';
			$mail->From = 'foodres@foodres.org';
			$mail->FromName = 'FoodRES, LLC.';
			$mail->Password = 'ogyftohoiztepqlx';
 */
 

  			$mail->Username = 'blockstale@blockstale.com';
			$mail->From = 'blockstale@blockstale.com';
			$mail->FromName = 'Blockstale, LLC';
			$mail->Password = 'Block@152535';
 
			//Recipients
			if($from)
				$mail->setFrom($from);
			foreach($to as $i=>$recipient){
				$mail->addAddress($recipient->getProperty('email'));
			}
			foreach($cc as $i=>$recipient){
				$mail->addCC($recipient);
			}
			foreach($bcc as $i=>$recipient){
				$mail->addBCC($recipient);
			}
			if($replyTo)
				$mail->addReplyTo($replyTo);
			// Attachments
			// $attachments = [$File->getInstance('5f98800b8128b'),$File->getInstance('5f9886fe54cb1'),$File->getInstance('5f9886de3b39c')];
			foreach($attachments as $i=>$attachment){
				if($attachment instanceof Model && $attachment->getType()==MODEL_FILE){
					$mail->addAttachment(Link::getFile($attachment,false),ucwords($attachment->getProperty('name')));         // Add attachments
				}else{
					Utility::log($attachment->getType());
				}
			}

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $html;
				// //Read an HTML message body from an external file, convert referenced images to embedded,
				// //convert HTML into a basic plain-text alternative body
				// $mail->msgHTML(file_get_contents('contents.html'), __DIR__);

			$mail->AltBody = $text;

			//send the message, check for errors
			if(!$mail->send()){
				$response->addMessage(new Message('Email not sent','Email not sent: '.$mail->ErrorInfo,THEME_WARNING));
			}else{
				return true;
				//Section 2: IMAP
				//Uncomment these to save your message in the 'Sent Mail' folder.
				/* if (Mailer::save_mail($mail)) {
				    echo "Message saved!";
				} */
			}
		}catch(Exception $e){
			return false;
		}
		return false;
	}
	/*
		Section 2: IMAP
		IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
		Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
		You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
		be useful if you are trying to get this working on a non-Gmail IMAP server.
	*/
	static function save_mail($mail){
		//You can change 'Sent Mail' to any other folder or tag
		$path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

		//Tell your server to open an IMAP connection using the same username and password as you used for SMTP
		$imapStream = imap_open($path, $mail->Username, $mail->Password);
		$result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
		imap_close($imapStream);
		return $result;
	}
}
 ?>
