<?php
namespace reiniiriarios\Colors;

class Modify {
    /**
     * Rotate $hue by $angle degrees
     * Degrees can be more than 360 or less than -360, but will simply loop around
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $angle degrees
     * @return int|float rotated $hue
     */
    public static function hue_shift($hue, $angle) {
        Util::value_range_check($hue, 0, 360);

        $hue += $angle;

        while ($hue >= 360) $hue -= 360;
        while ($hue < 0) $hue += 360;

        return $hue;
    }

    /**
     * Blend one color with another
     *
     * @param int[]|float[]|string $color1 hex, rgb, hsv
     * @param int[]|float[]|string $color2 hex, rgb, hsv
     * @param float $amount amount to blend, 0-1
     * @param string $method one of: hex, rgb, hsv
     * @return int[]|float[]|string blended color
     */
    public static function blend($color1, $color2, $amount, $method) {
        switch ($method) {
            case 'hex':
                $color1 = Convert::hex2rgb($color1);
                $color2 = Convert::hex2rgb($color2);
                $rgb = self::blend_rgb($color1[0],$color1[1],$color1[2],$color2[0],$color2[1],$color2[2],$amount);
                return Convert::rgb2hex($rgb[0],$rgb[1],$rgb[2]);
                break;
            case 'rgb':
                return self::blend_rgb($color1[0],$color1[1],$color1[2],$color2[0],$color2[1],$color2[2],$amount);
                break;
            case 'hsv':
                return self::blend_hsv($color1[0],$color1[1],$color1[2],$color2[0],$color2[1],$color2[2],$amount);
                break;
            default:
                throw new \UnexpectedValueException('Unrecognized color blend method');
        }
    }
    
    /**
     * Blend one RGB color with another
     *
     * @param int $r1 color 1 red, 0-255
     * @param int $g1 color 1 green, 0-255
     * @param int $b1 color 1 blue, 0-255
     * @param int $r2 color 2 red, 0-255
     * @param int $g2 color 2 green, 0-255
     * @param int $b2 color 2 blue, 0-255
     * @param float $amount amount to blend, 0-1
     * @return int[] blended [r,g,b]
     */
    public static function blend_rgb($r1, $g1, $b1, $r2, $g2, $b2, $amount) {
        Util::value_range_check($r1, 0, 255);
        Util::value_range_check($g1, 0, 255);
        Util::value_range_check($b1, 0, 255);
        Util::value_range_check($r2, 0, 255);
        Util::value_range_check($g2, 0, 255);
        Util::value_range_check($b2, 0, 255);
        Util::value_range_check($amount, 0, 1);
        
        $new_r = $r1 + (($r2 - $r1) * $amount);
        $new_g = $g1 + (($g2 - $g1) * $amount);
        $new_b = $b1 + (($b2 - $b1) * $amount);

        return array($new_r, $new_g, $new_b);
    }
    
    /**
     * Blend one HSV color with another
     *
     * @param int|float $h1 color 1 hue, degrees 0 <= value <= 360
     * @param int|float $s1 color 1 saturation, 0-100
     * @param int|float $v1 color 1 value, 0-100
     * @param int|float $h2 color 2 hue, degrees 0 <= value <= 360
     * @param int|float $s2 color 2 saturation, 0-100
     * @param int|float $v2 color 2 value, 0-100
     * @param float $amount amount to blend, 0-1
     * @return int[]|float[] blended [r,g,b]
     */
    public static function blend_hsv($h1, $s1, $v1, $h2, $s2, $v2, $amount) {
        Util::value_range_check($h1, 0, 360);
        Util::value_range_check($s1, 0, 100);
        Util::value_range_check($v1, 0, 100);
        Util::value_range_check($h2, 0, 360);
        Util::value_range_check($s2, 0, 100);
        Util::value_range_check($v2, 0, 100);
        Util::value_range_check($amount, 0, 1);

        if (abs($h2 - $h1) > 180) {
            $hue_diff = 360 - abs(($h2 - $h1) * $amount);
            if ($h1 > $h2) $hue_diff *= -1;
        }
        else {
            $hue_diff = ($h2 - $h1) * $amount;
        }

        $new_h = self::hue_shift($h1, $hue_diff);

        $new_s = $s1 + (($s2 - $s1) * $amount);
        $new_v = $v1 + (($v2 - $v1) * $amount);

        return array($new_h, $new_s, $new_v);
    }

    public static function darken($r, $g, $b, $amount) {

    }

    public static function lighten($r, $g, $b, $amount) {

    }

    public static function mix($r1, $g1, $b1, $r2, $g2, $b2, $amount=50) {

    }

    public static function desaturate($r, $g, $b) {
        
    }
}