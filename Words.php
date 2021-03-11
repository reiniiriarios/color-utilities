<?php
namespace reiniiriarios\Colors;

class Words {

    /**
     * Fetch RGB value of any phrase based on google image search results
     *
     * @param string $phrase
     * @return array [r,g,b]
     */
    public static function words2rgb($phrase) {
        $base_url = "https://www.google.com/search?as_st=y&tbm=isch&as_q=";
        $raw_search = file_get_contents($base_url . urlencode($phrase));

        preg_match_all("/<a href=\"\/url\?q=.*?\"><img.*?src=\"(.*?)\".*?><\/a>/s", $raw_search, $images);

		$colors = [
			'r' => 0,
			'g' => 0,
			'b' => 0,
		];

        $image_count = 0;

        foreach ($images[1] as &$image_url) {
            $image_data = getimagesize($image_url);
		    $width = $image_data[0];
		    $height = $image_data[1];

		    $pixel = imagecreatetruecolor(1, 1);

		    if ($image_data['mime'] == 'image/jpeg') {
			    $image = imagecreatefromjpeg($image_url);
		    }
            elseif ($image_data['mime'] == 'image/png') {
			    $image = imagecreatefrompng($image_url);			
			    imagealphablending($pixel, false);
			    imagesavealpha($pixel, true);
			    $transparent = imagecolorallocatealpha($pixel, 255, 255, 255, 127);
			    imagefilledrectangle($pixel, 0, 0, 1, 1, $transparent);
		    }
            else {
                // don't bother
			    continue;
		    }

		    imagecopyresampled($pixel, $image, 0, 0, 0, 0, 1, 1, $width, $height);
		    $color = imagecolorsforindex($pixel, imagecolorat($pixel, 0, 0));

            // potentially null?
			if (is_null($color)) {
				continue;
			}

            $colors['r'] += $color['red'];
            $colors['g'] += $color['green'];
            $colors['b'] += $color['blue'];
            $image_count++;
        }

        $colors['r'] = (int)($colors['r'] / $image_count);
		$colors['g'] = (int)($colors['g'] / $image_count);
		$colors['b'] = (int)($colors['b'] / $image_count);

        return $colors;
    }
    
    /**
     * Fetch RGB value of any phrase based on google image search results
     *
     * @param string $phrase
     * @return string rrggbb
     */
    public static function words2hex($phrase) {
        $color = self::words2rgb($phrase);
        $hex = Convert::rgb2hex($color['r'], $color['g'], $color['b']);

        return $hex;
    }
}