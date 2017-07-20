<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* SaFly Avatar */

function SaFly_Avatar($avatar)
{
	function safly_get_avatar($avatar)
	{
		if (SaFly_is_SSL()) {
			$safly_avatar = 'avatar.safly.org';
		}else {
			$safly_avatar = 'avatar2.safly.org';
		}
		$avatar = str_replace(array('www.gravatar.com', '0.gravatar.com', '1.gravatar.com', '2.gravatar.com', 'secure.gravatar.com'), $safly_avatar, $avatar);
		return $avatar;
	}
	add_filter('get_avatar', 'safly_get_avatar', 10, 3);
}

add_action('init', 'SaFly_Avatar');

?>