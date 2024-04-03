<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly


function ppwsl_encrypt($text, $salt)
{
	return base64_encode(openssl_encrypt($text, 'AES-128-ECB', $salt));
}

function ppwsl_decrypt($text, $salt)
{
	return openssl_decrypt(base64_decode($text), 'AES-128-ECB', $salt);
}
