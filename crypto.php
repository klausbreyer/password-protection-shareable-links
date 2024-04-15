<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly


function passpass_encrypt($text, $salt)
{
	return base64_encode(openssl_encrypt($text, 'AES-128-ECB', $salt));
}

function passpass_decrypt($text, $salt)
{
	return openssl_decrypt(base64_decode($text), 'AES-128-ECB', $salt);
}
