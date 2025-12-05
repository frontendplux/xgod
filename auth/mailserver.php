<?php
include __DIR__.'/mailer.php';
function mailserver($email,$session_id){
    $mail=
<<<_end
<div style="position: absolute;top: 0;bottom: 0;left: 0;right: 0;overflow: auto;display: flex;justify-content: center;padding: 10px;">
	<div style="text-align: center;max-width: 400px;margin: auto;background: rgb(238, 242, 243);padding: 10px;font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">
		<div><img src="images/loader.gif" width="100px" alt=""></div>
		<div style="font-size: x-large;font-weight: bolder;text-transform: uppercase;margin-bottom: 10px;">Please confirm your email</div>
		<div style="font-weight: lighter;">Use the verification code below to confirm your email address to complete Registration.</div>
		<div style="text-align: center;padding: 10px; margin: 15px 0;">
			<span style="text-decoration:none;color:white; letter-spacing: 10px;background: #948c8c;font-size: x-large;font-weight: bolder;padding: 10px;border-radius: 10px;">$session_id</span>
		</div>
		<div style="text-align: center;margin: 20px 0;">&copy;copywright @ passion flame arts and music <a href="https://shoplenca.com">shoplenca</a></div>
	</div>
</div>
_end;
    sendmail($email,"verification mail",$mail);
}
?>