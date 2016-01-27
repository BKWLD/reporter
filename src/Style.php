<?php namespace Bkwld\Reporter;

use Config;

// Methods to apply styling to CLI output
class Style {

	// Got these from https=>//github.com/Marak/colors.js/blob/master/colors.js
	private static $codes = array(	
		//styles
		'bold'      => array("\x1B[1m", "\x1B[22m"),
		'italic'    => array("\x1B[3m", "\x1B[23m"),
		'underline' => array("\x1B[4m", "\x1B[24m"),
		'inverse'   => array("\x1B[7m", "\x1B[27m"),
		'strikethrough' => array("\x1B[9m", "\x1B[29m"),
		//grayscale
		'white'     => array("\x1B[37m", "\x1B[39m"),
		'grey'      => array("\x1B[90m", "\x1B[39m"),
		'black'     => array("\x1B[30m", "\x1B[39m"),
		//colors
		'blue'      => array("\x1B[34m", "\x1B[39m"),
		'cyan'      => array("\x1B[36m", "\x1B[39m"),
		'green'     => array("\x1B[32m", "\x1B[39m"),
		'magenta'   => array("\x1B[35m", "\x1B[39m"),
		'red'       => array("\x1B[31m", "\x1B[39m"),
		'yellow'    => array("\x1B[33m", "\x1B[39m"),
	);
	
	// Wrap some text in style codes
	public static function wrap($styles, $text) {
		
		// Check if styles are enabled
		if (!Config::get('reporter::style')) return $text;

		// Styles can be a string or an array
		if (!is_array($styles)) $styles = array($styles);

		// Wrap the text in tags
		foreach($styles as $style) {
			if (!isset(self::$codes[$style])) throw new Exception('Invalid style key');
			$text = self::$codes[$style][0].$text.self::$codes[$style][1];
		}

		return $text;
		
	}

	
}
