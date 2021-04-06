<?php
namespace reiniiriarios\Colors;

class Convert {
    ////////////////////////// BIT RATE / RANGE ADJUSTMENTS //////////////////////////

    /**
     * Change the bit-depth of a single value
     *
     * @param int|float $value
     * @param int $bit_depth_from bit depth of $value (usually 8, 10, 12, etc)
     * @param int $bit_depth_to bit depth of $value (usually 8, 10, 12, etc)
     * @param bool $round
     * @return int|float scaled value
     */
    public static function change_value_bit_depth($value, $bit_depth_from, $bit_depth_to, $round=true) {
        $max_from = (2 ** $bit_depth_from) - 1;
        $max_to = (2 ** $bit_depth_to) - 1;

        return self::scale_value_range($value, 0, $max_from, 0, $max_to, $round);
    }

    /**
     * Scale a value to a different range of values
     * e.g. Scale $value from 16-235 to 64-940
     *
     * @param int|float $value
     * @param int $min_from input lower range
     * @param int $max_from input upper range
     * @param int $min_to output lower range
     * @param int $max_to output upper range
     * @param bool $round
     * @return int|float scaled value
     */
    public static function scale_value_range($value, $min_from, $max_from, $min_to, $max_to, $round=true) {
        Util::value_range_check($value, $min_from, $max_from);
        
        // (ValueFrom * (RangeTo / RangeFrom)) + (MinTo - MinFrom)
        $range_from = $max_from - $min_from;
        $range_to = $max_to - $min_to;
        $value_to = ($value * (($max_to - $min_to) / ($max_from - $min_from))) + ($min_to - $min_from);

        if ($round) $value_to = round($value_to);

        return $value_to;
    }

    /**
     * Change the bit-depth of an RGB color
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int $bit_depth_from bit depth of $value (usually 8, 10, 12, etc)
     * @param int $bit_depth_to bit depth of $value (usually 8, 10, 12, etc)
     * @param bool $round
     * @return int[]|float[] red, green, blue (scaled values)
     */
    public static function change_rgb_bit_depth($red, $green, $blue, $bit_depth_from, $bit_depth_to, $round=true) {
        $r = self::change_value_bit_depth($red, $bit_depth_from, $bit_depth_to, $round);
        $g = self::change_value_bit_depth($green, $bit_depth_from, $bit_depth_to, $round);
        $b = self::change_value_bit_depth($blue, $bit_depth_from, $bit_depth_to, $round);

        return array($r, $g, $b);
    }
    
    ////////////////////////// HUE, SATURATION, * //////////////////////////

    /**
     * Convert RGB to HSV
     * Saturation and Value are in percentages
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] hue, saturation, value
     */
    public static function rgb2hsv($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        $red   /= $color_depth;
        $green /= $color_depth;
        $blue  /= $color_depth;

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);

        $range = $max - $min;
        $v = $max / 1;

        $s = $max ? $range / $max : 0;

        if (!$range) {
            $h = 0;
        }
        elseif ($red == $max) {
            $h = ($green - $blue) / $range;
        }
        elseif ($green == $max) {
            $h = 2 + ($blue - $red) / $range;
        }
        elseif ($blue == $max) {
            $h = 4 + ($red - $green) / $range;
        }
        else {
            $h = 0;
        }

        $h *= 60;
        while ($h >= 360) $h -= 360;
        while ($h < 0) $h += 360;

        $s *= 100;
        $v *= 100;

        if ($round) {
            $h = round($h);
            $s = round($s);
            $v = round($v);
        }

        return array($h, $s, $v);
    }

    /**
     * Convert RGB to HSL
     * Saturation and Lightness are in percentages
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] hue, saturation, lightness
     */
    public static function rgb2hsl($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        $red   /= $color_depth;
        $green /= $color_depth;
        $blue  /= $color_depth;

        $v = max($red, $green, $blue);
        $min = min($red, $green, $blue);

        $chroma = $v - $min;

        $l = $v - ($chroma / 2);
        $s = $l == 0 || $l == 1 ? 0 : ($v - $l) / min($l, 1 - $l);

        if (!$chroma) {
            $h = 0;
        }
        elseif ($v == $red) {
            $h = 60 * (($green - $blue) / $chroma);
        }
        elseif ($v == $green) {
            $h = 60 * (2 + ($blue - $red) / $chroma);
        }
        elseif ($v == $blue) {
            $h = 60 * (4 + ($red - $green) / $chroma);
        }
        else {
            $h = 0;
        }

        while ($h >= 360) $h -= 360;
        while ($h < 0) $h += 360;

        $s *= 100;
        $l *= 100;
        
        if ($round) {
            $h = round($h);
            $s = round($s);
            $l = round($l);
        }

        return array($h, $s, $l);
    }

    /**
     * Convert RGB to HSI
     * Saturation and Intensity are in percentages
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, intensity
     */
    public static function rgb2hsi($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);

        $r1 = $red / $color_depth;
        $g1 = $green / $color_depth;
        $b1 = $blue / $color_depth;

        $min = min($r1, $g1, $b1);
        $max = max($r1, $g1, $b1);

        $chroma = $max - $min;

        if (!$chroma) {
            $h = 0;
            $s = 0;
        }
        else {
            if ($max == $r1) {
                $hp = fmod(($g1 - $b1) / $chroma, 6);
            }
            elseif ($max == $g1) {
                $hp = (($b1 - $r1) / $chroma) + 2;
            }
            else {
                $hp = (($r1 - $g1) / $chroma) + 4;
            }
            $h = $hp * 60;
            
            while ($h < 0) $h += 360;
            while ($h > 360) $h -= 360;
        }
        
        $i = ($r1 + $g1 + $b1) * (1 / 3);

        $s = $chroma && $i ? (1 - $min / $i) : 0;

        $i *= 100;
        $s *= 100;

        if ($round) {
            $h = round($h);
            $i = round($i);
            $s = round($s);
        }

        return array($h, $s, $i);
    }

    /**
     * Convert HSV to RGB
     * Saturation and Value should be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $value percentage
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] red, green, blue
     */
    public static function hsv2rgb($hue, $saturation, $value, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($hue, 0, 360);
        Util::value_range_check($saturation, 0, 100);
        Util::value_range_check($value, 0, 100);

        if ($saturation == 0) {
            $all = $value / 100 * $color_depth;
            $r = $all;
            $g = $all;
            $b = $all;
        }
        else {
            $hue /= 60;
            $saturation /= 100;
            $value /= 100;
            $i = floor($hue);
            $f = $hue - $i;
            $p = $value * (1 - $saturation);
            $q = $value * (1 - $saturation * $f);
            $t = $value * (1 - $saturation * (1 - $f));
            switch ($i) {
                case 0:
                    $r = $value;
                    $g = $t;
                    $b = $p;
                    break;
                case 1:
                    $r = $q;
                    $g = $value;
                    $b = $p;
                    break;
                case 2:
                    $r = $p;
                    $g = $value;
                    $b = $t;
                    break;
                case 3:
                    $r = $p;
                    $g = $q;
                    $b = $value;
                    break;
                case 4:
                    $r = $t;
                    $g = $p;
                    $b = $value;
                    break;
                default:
                    $r = $value;
                    $g = $p;
                    $b = $q;
            }
            $r *= $color_depth;
            $g *= $color_depth;
            $b *= $color_depth;
        }

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /**
     * Convert HSV to HSL
     * Saturation and Value should be in percentages
     * Saturation and Lightness are in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $value percentage
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, lightness
     */
    public static function hsv2hsl($hue, $saturation, $value, $round=true) {
        Util::value_range_check($hue, 0, 360);
        Util::value_range_check($saturation, 0, 100);
        Util::value_range_check($value, 0, 100);

        $saturation /= 100;
        $value /= 100;

        $l = $value * (1 - ($saturation / 2));

        if ($l == 0 || $l == 1) {
            $s = 0;
        }
        else {
            $s = ($value - $l) / (min($l, 1 - $l));
        }

        $l *= 100;
        $s *= 100;

        if ($round) {
            $l = round($l);
            $s = round($s);
        }

        return array($hue, $s, $l);
    }
    
    /**
     * Convert HSV to HSI
     * Saturation(V) and Value should be in percentages
     * Saturation(I) and Intensity will be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $value percentage
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, intensity
     */
    public static function hsv2hsi($hue, $saturation, $value, $round=true) {
        list($r, $g, $b) = self::hsv2rgb($hue, $saturation, $value, false);
        return self::rgb2hsi($r, $g, $b, $round);
    }

    /**
     * Convert HSL to HSV
     * Saturation and Lightness should be in percentages
     * Saturation and Value are in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $lightness percentage
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, value
     */
    public static function hsl2hsv($hue, $saturation, $lightness, $round=true) {
        Util::value_range_check($hue, 0, 360);
        Util::value_range_check($saturation, 0, 100);
        Util::value_range_check($lightness, 0, 100);

        $saturation /= 100;
        $lightness /= 100;

        $v = $lightness + $saturation * min($lightness, 1 - $lightness);
        $s = $v ? 2 * (1 - $lightness / $v) : 0;

        $v *= 100;
        $s *= 100;

        if ($round) {
            $v = round($v);
            $s = round($s);
        }

        return array($hue, $s, $v);
    }

    /**
     * Convert HSL to RGB
     * Saturation and Lightness should be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $lightness percentage
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function hsl2rgb($hue, $saturation, $lightness, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($hue, 0, 360);
        Util::value_range_check($saturation, 0, 100);
        Util::value_range_check($lightness, 0, 100);

        $hue /= 60;
        $saturation /= 100;
        $lightness /= 100;

        if (!$saturation) {
            $level = $lightness;
            $r = $level;
            $g = $level;
            $b = $level;
        }
        else {
            $chroma = (1 - abs(2 * $lightness - 1)) * $saturation;
            $x = $chroma * (1 - abs(fmod($hue, 2) - 1));
            $huef = floor($hue);
            $m = $lightness - ($chroma / 2);

            switch ($huef) {
                case 0:
                    $r = $chroma + $m;
                    $g = $x + $m;
                    $b = $m;
                    break;
                case 1:
                    $r = $x + $m;
                    $g = $chroma + $m;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $chroma + $m;
                    $b = $x + $m;
                    break;
                case 3:
                    $r = $m;
                    $g = $x + $m;
                    $b = $chroma + $m;
                    break;
                case 4:
                    $r = $x + $m;
                    $g = $m;
                    $b = $chroma + $m;
                    break;
                case 5:
                    $r = $chroma + $m;
                    $g = $m;
                    $b = $x + $m;
                    break;
                default:
                    $r = $m;
                    $g = $m;
                    $b = $m;
            }
        }

        $r *= $color_depth;
        $g *= $color_depth;
        $b *= $color_depth;

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /**
     * Convert HSL to HSI
     * Saturation(L) and Lightness should be in percentages
     * Saturation(I) and Intensity will be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $lightness percentage
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, intensity
     */
    public static function hsl2hsi($hue, $saturation, $lightness, $round=true) {
        list($r, $g, $b) = self::hsl2rgb($hue, $saturation, $lightness, false);
        return self::rgb2hsi($r, $g, $b, $round);
    }

    /**
     * Convert HSI to RGB
     * Saturation and Intensity should be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $intensity percentage
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function hsi2rgb($hue, $saturation, $intensity, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($hue, 0, 360);
        Util::value_range_check($saturation, 0, 100);
        Util::value_range_check($intensity, 0, 100);
        
        $hp = $hue / 60;
        $saturation /= 100;
        $intensity /= 100;

        $m = $intensity * (1 - $saturation);

        if (!$saturation) {
            $r = $m;
            $g = $m;
            $b = $m;
        }
        else {
            $z = 1 - abs(fmod($hp, 2) - 1);
            $chroma = (3 * $intensity * $saturation) / (1 + $z);
            $x = $chroma * $z;

            $hf = floor($hp);
            switch ($hf) {
                case 0:
                    $r = $chroma + $m;
                    $g = $x + $m;
                    $b = $m;
                    break;
                case 1:
                    $r = $x + $m;
                    $g = $chroma + $m;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $chroma + $m;
                    $b = $x + $m;
                    break;
                case 3:
                    $r = $m;
                    $g = $x + $m;
                    $b = $chroma + $m;
                    break;
                case 4:
                    $r = $x + $m;
                    $g = $m;
                    $b = $chroma + $m;
                    break;
                case 5:
                    $r = $chroma + $m;
                    $g = $m;
                    $b = $x + $m;
                    break;
                default:
                    $r = $m;
                    $g = $m;
                    $b = $m;
            }
            $r = min($r, 1);
            $g = min($g, 1);
            $b = min($b, 1);
        }
        
        $r *= $color_depth;
        $g *= $color_depth;
        $b *= $color_depth;

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /**
     * Convert HSI to HSV
     * Saturation(I) and Intensity should be in percentages
     * Saturation(V) and Value will be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $intensity percentage
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, value
     */
    public static function hsi2hsv($hue, $saturation, $intensity, $round=true) {
        list($r, $g, $b) = self::hsi2rgb($hue, $saturation, $intensity, false);
        return self::rgb2hsv($r, $g, $b, $round);
    }

    /**
     * Convert HSI to HSL
     * Saturation(I) and Intensity should be in percentages
     * Saturation(L) and Lightness will be in percentages
     *
     * @param int|float $hue degrees 0 <= value <= 360
     * @param int|float $saturation percentage
     * @param int|float $intensity percentage
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] hue, saturation, lightness
     */
    public static function hsi2hsl($hue, $saturation, $intensity, $round=true) {
        list($r, $g, $b) = self::hsi2rgb($hue, $saturation, $intensity, false);
        return self::rgb2hsl($r, $g, $b, $round);
    }
    
    /////////////////////// CMYK ////////////////////////

    /**
     * Convert RGB to CMYK
     * This conversion is mathematical and does not take pigment conversion into account
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] cyan, magenta, yellow, black
     */
    public static function rgb2cmyk($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        $red   /= $color_depth;
        $green /= $color_depth;
        $blue  /= $color_depth;

        $k = 1 - max($red, $green, $blue);
        if ($k == 1) {
            $c = 0;
            $m = 0;
            $y = 0;
        }
        else {
            $c = (1 - $red   - $k) / (1 - $k);
            $m = (1 - $green - $k) / (1 - $k);
            $y = (1 - $blue  - $k) / (1 - $k);
        }

        $c *= 100;
        $m *= 100;
        $y *= 100;
        $k *= 100;

        if ($round) {
            $c = round($c);
            $m = round($m);
            $y = round($y);
            $k = round($k);
        }

        return array($c, $m, $y, $k);
    }

    /**
     * Convert CMYK to RGB
     * This conversion is mathematical and does not take pigment conversion into account
     *
     * @param int|float $cyan
     * @param int|float $magenta
     * @param int|float $yellow
     * @param int|float $black
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] red, green, blue
     */
    public static function cmyk2rgb($cyan, $magenta, $yellow, $black, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($cyan, 0, 100);
        Util::value_range_check($magenta, 0, 100);
        Util::value_range_check($yellow, 0, 100);
        Util::value_range_check($black, 0, 100);

        $cyan    /= 100;
        $magenta /= 100;
        $yellow  /= 100;
        $black   /= 100;

        $r = (1 - $cyan)    * (1 - $black);
        $g = (1 - $magenta) * (1 - $black);
        $b = (1 - $yellow)  * (1 - $black);
        
        $r *= $color_depth;
        $g *= $color_depth;
        $b *= $color_depth;
        
        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /////////////////////// YIQ ////////////////////////

    /**
     * Convert RGB to YIQ
     * TODO: Validate Algorithm
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $normalize true = Y[0,255], I&Q[-128,128]; false = Y[0,1], I[-0.5957,0.5957], Q[-0.5226,0.5226]
     * @param bool $round will not round if not normalized
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] Y, I, Q
     */
    public static function rgb2yiq($red, $green, $blue, $normalize=true, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        $red   /= $color_depth;
        $green /= $color_depth;
        $blue  /= $color_depth;

        $y = 0.299  * $red +  0.587  * $green +  0.114  * $blue;
        $i = 0.5959 * $red + -0.2746 * $green + -0.3213 * $blue;
        $q = 0.2115 * $red + -0.5227 * $green +  0.3112 * $blue;

        $y = min(max($y,0),1);
        $i = min(max($i,-0.5957),0.5957);
        $q = min(max($q,-0.5226),0.5226);

        if ($normalize) {
            $y = self::scale_value_range($y, 0, 1, 0, 255, false);
            $i = self::scale_value_range($i + 0.5957, 0, 1.1914, 0, 256, false) - 128;
            $q = self::scale_value_range($q + 0.5226, 0, 1.0452, 0, 256, false) - 128;

            if ($round) {
                $y = round($y);
                $i = round($i);
                $q = round($q);
            }
        }

        return array($y, $i, $q);
    }

    /**
     * Convert YIQ to RGB
     *
     * @param int|float $y 0 to 255 or 0 to 1
     * @param int|float $i -128 to 128 or -0.5957 to 0.5957
     * @param int|float $q -128 to 128 or -0.5226 to 0.5226
     * @param bool $normalized true = Y[0,255], I&Q[-128,128]; false = Y[0,1], I[-0.5957,0.5957], Q[-0.5226,0.5226]
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] red, green, blue
     */
    public static function yiq2rgb($y, $i, $q, $normalized=true, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        if ($normalized) {
            Util::value_range_check($y, 0, 255);
            Util::value_range_check($i, -128, 128);
            Util::value_range_check($q, -128, 128);

            $y = self::scale_value_range($y, 0, 255, 0, 1, false);
            $i = self::scale_value_range($i, -128, 128, -0.5957, 0.5957, false);
            $q = self::scale_value_range($q, -128, 128, -0.5226, 0.5226, false);
        }
        else {
            Util::value_range_check($y, 0, 1);
            Util::value_range_check($i, -0.5957, 0.5957);
            Util::value_range_check($q, -0.5226, 0.5226);
        }

        $r = $y +  0.956 * $i +  0.621 * $q;
        $g = $y + -0.272 * $i + -0.647 * $q;
        $b = $y + -1.106 * $i +  1.703 * $q;

        $r *= $color_depth;
        $g *= $color_depth;
        $b *= $color_depth;

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }
    
    /////////////////////// XYZ, xyY ////////////////////////

    /**
     * Convert RGB to XYZ
     * X, Y, and Z will be between 0 and the white point reference XYZ values
     * Formulae and matrices originally from:
     * http://www.brucelindbloom.com
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param string $color_space RGB color space (e.g. sRGB)
     * @param string $reference_white RGB reference white (e.g. D65)
     * @param int $color_depth RGB max value per channel
     * @return float[] X, Y, Z
     */
    public static function rgb2xyz($red, $green, $blue, $color_space='srgb', $reference_white='d65', $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);

        // Formulae use RGB 0 to 1
        $red   /= $color_depth;
        $green /= $color_depth;
        $blue  /= $color_depth;

        // Typos and variations and such
        $color_space = preg_replace('[^a-z0-9]','',strtolower($color_space));
        $reference_white = strtolower($reference_white);
        $conform = [
            'adobergb'       => 'adobergb1998',
            'ntsc'           => 'ntscrgb',
            'pal'            => 'palsecamrgb',
            'palrgb'         => 'palsecamrgb',
            'secam'          => 'palsecamrgb',
            'secamrgb'       => 'palsecamrgb',
            'prophoto'       => 'prophotorgb',
            'smpte'          => 'smptecrgb',
            'smptec'         => 'smptecrgb',
            'widegamut'      => 'widegamutrgb',
        ];
        if (!empty($conform[$color_space])) {
            $color_space = $conform[$color_space];
        }

        if (empty(Reference::COLOR_SPACES[$color_space]['rgb2xyz'][$reference_white])) {
            throw new Exception \UnexpectedValueException('Transformation matrix unavailable for this color space and reference white');
        }
        $m = Reference::COLOR_SPACES[$color_space]['rgb2xyz'][$reference_white];
        
        // Inverse Companding to Linearize RGB Values
        if ($color_space == 'srgb') {
            // sRGB
            $r = $red   <= 0.04045 ? $red   / 12.92 : pow(($red   + 0.055) / 1.055, 2.4);
            $g = $green <= 0.04045 ? $green / 12.92 : pow(($green + 0.055) / 1.055, 2.4);
            $b = $blue  <= 0.04045 ? $blue  / 12.92 : pow(($blue  + 0.055) / 1.055, 2.4);
        }
        elseif ($color_space == 'ecirgb') {
            // L*
            $r = $red   <= 0.08 ? 100 * ($red   / Reference::CIE_K) : pow(($red   + 0.16) / 1.16, 3);
            $g = $green <= 0.08 ? 100 * ($green / Reference::CIE_K) : pow(($green + 0.16) / 1.16, 3);
            $b = $blue  <= 0.08 ? 100 * ($blue  / Reference::CIE_K) : pow(($blue  + 0.16) / 1.16, 3);
        }
        else {
            // Gamma
            $r = pow($red,   Reference::COLOR_SPACES[$color_space]['gamma']);
            $g = pow($green, Reference::COLOR_SPACES[$color_space]['gamma']);
            $b = pow($blue,  Reference::COLOR_SPACES[$color_space]['gamma']);
        }
        
        // [X]           [R]
        // [Y] = [M 3x3]*[G]
        // [Z]           [B]
        $x = $m[0][0] * $r + $m[0][1] * $g + $m[0][2] * $b;
        $y = $m[1][0] * $r + $m[1][1] * $g + $m[1][2] * $b;
        $z = $m[2][0] * $r + $m[2][1] * $g + $m[2][2] * $b;

        return array($x, $y, $z);
    }

    /**
     * Convert XYZ to RGB
     * RGB values that fall outsize representable values will be clamped
     *
     * @param float $x
     * @param float $y
     * @param float $z
     * @param string $color_space Target color space from listed options (e.g. sRGB)
     * @param string $reference_white Target reference white from listed options (e.g. D65)
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @return float[]|int[] red, green, blue
     */
    public static function xyz2rgb($x, $y, $z, $color_space='srgb', $reference_white='d65', $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);

        // Typos and variations and such
        $color_space = preg_replace('[^a-z0-9]','',strtolower($color_space));
        $reference_white = strtolower($reference_white);
        $conform = [
            'adobergb'       => 'adobergb1998',
            'ntsc'           => 'ntscrgb',
            'pal'            => 'palsecamrgb',
            'palrgb'         => 'palsecamrgb',
            'secam'          => 'palsecamrgb',
            'secamrgb'       => 'palsecamrgb',
            'prophoto'       => 'prophotorgb',
            'smpte'          => 'smptecrgb',
            'smptec'         => 'smptecrgb',
            'widegamut'      => 'widegamutrgb',
        ];

        if (empty(Reference::COLOR_SPACES[$color_space]['xyz2rgb'][$reference_white]) || empty(Reference::STD_ILLUMINANTS[$reference_white]['vector'])) {
            throw new Exception \UnexpectedValueException('Transformation matrix unavailable for this color space and reference white');
        }
        $m = Reference::COLOR_SPACES[$color_space]['xyz2rgb'][$reference_white];
        
        // [R]       [X]
        // [G] = [M]*[Y]  where [M] is [RGB to XYZ matrix]^-1
        // [B]       [Z]
        $r = $m[0][0] * $x + $m[0][1] * $y + $m[0][2] * $z;
        $g = $m[1][0] * $x + $m[1][1] * $y + $m[1][2] * $z;
        $b = $m[2][0] * $x + $m[2][1] * $y + $m[2][2] * $z;
        
        // sRGB Companding to De-linearize RGB values
        if ($color_space == 'srgb') {
            // sRGB
            $r = $r <= 0.0031308 ? $r * 12.92 : pow(($r * 1.055), 1/2.4) - 0.055;
            $g = $g <= 0.0031308 ? $g * 12.92 : pow(($g * 1.055), 1/2.4) - 0.055;
            $b = $b <= 0.0031308 ? $b * 12.92 : pow(($b * 1.055), 1/2.4) - 0.055;
        }
        elseif ($color_space == 'ecirgb') {
            // L*
            $r = $r <= Reference::CIE_E ? ($r * Reference::CIE_K) / 100 : 1.16 * pow($r, 1/3) - 0.16;
            $g = $g <= Reference::CIE_E ? ($g * Reference::CIE_K) / 100 : 1.16 * pow($g, 1/3) - 0.16;
            $b = $b <= Reference::CIE_E ? ($b * Reference::CIE_K) / 100 : 1.16 * pow($b, 1/3) - 0.16;
        }
        else {
            // Gamma
            $r = pow($r, 1 / Reference::COLOR_SPACES[$color_space]['gamma']);
            $g = pow($g, 1 / Reference::COLOR_SPACES[$color_space]['gamma']);
            $b = pow($b, 1 / Reference::COLOR_SPACES[$color_space]['gamma']);
        }
        
        // $w = Reference::STD_ILLUMINANTS[$reference_white]['vector'];
        // XYZ SHOULD fall within reference white values; otherwise they will be unrepresentable ([w] = rgb[1,1,1])
        // Util::value_range_check($x, 0, $w[0]);
        // Util::value_range_check($y, 0, $w[1]);
        // Util::value_range_check($z, 0, $w[2]);

        // Some RGB values may be negative or > 1, clamp
        $r = min(max($r,0),1);
        $g = min(max($g,0),1);
        $b = min(max($b,0),1);
    
        // Scale to output color depth
        $r *= $color_depth;
        $g *= $color_depth;
        $b *= $color_depth;

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /**
     * Generate RGB to XYZ matrix
     * ($xr, $yr), ($xg, $yg), and ($xb, $yb) are chromaticity coordinates of an RGB system (such as sRGB)
     * ($xw, $yw, $zw) are a reference white vector (such as D65)
     *
     * To utilize matrix, RGB values MUST be linear and in the nominal range [0, 1]
     *
     * Common Reference White Standards:
     * a   CIE standard illuminant A; 2856 K
     * c   CIE standard illuminant C; 6774 K; deprecated
     * e   Equal-energy radiator
     * d50 CIE standard illuminant D50; 5003 K
     * d55 CIE standard illuminant D55; 5500 K
     * d65 CIE standard illuminant D65; 6504 K
     * icc Profile Connection Space (PCS) illuminant used in ICC profiles
     *
     * @param float $xr red   x chromaticity coordinate
     * @param float $yr red   y chromaticity coordinate
     * @param float $xg green x chromaticity coordinate
     * @param float $yg green y chromaticity coordinate
     * @param float $xb blue  x chromaticity coordinate
     * @param float $yb blue  y chromaticity coordinate
     * @param float $xw x reference white coordinate
     * @param float $yw y reference white coordinate
     * @param float $zw z reference white coordinate
     * @return float[] 3x3 matrix for converting RGB to XYZ
     */
    public static function rgb2xyz_matrix($xr, $yr, $xg, $yg, $xb, $yb, $xw, $yx, $zw) {
        //       [Sr*Xr Sg*Xg Sb*Xb]
        // [M] = [Sr*Yr Sg*Yg Sb*Yb]
        //       [Sr*Zr Sg*Zg Sb*Zb]

        // [Sr]   [Xr Xg Xb]^-1  [Xw]
        // [Sg] = [Yr Yg Yb]   * [Yw]
        // [Sb]   [Zr Zg Zb]     [Zw]

        // Xn = xn / yn
        // Yn = 1
        // Zn = (1 - xn - yn) / yn

        // Calculate XYZrgb matrix
        $xyzrgb = [
            [$xr / $yr, $xg / $yg, $xb / $yb],
            [1, 1, 1],
            [(1 - $xr - $yr) / $yr, (1 - $xg - $yg) / $yg, (1 - $xb - $yb) / $yb]
        ];

        $inverse = Util::matrix_3x3_inverse($xyzrgb);

        // Calculate the Sn matrix (as individual values)
        $sr = $inverse[0][0] * $xw + $inverse[0][1] * $yw + $inverse[0][2] * $zw;
        $sg = $inverse[1][0] * $xw + $inverse[1][1] * $yw + $inverse[1][2] * $zw;
        $sb = $inverse[2][0] * $xw + $inverse[2][1] * $yw + $inverse[2][2] * $zw;

        // Calculate final matrix
        $m = [
            [$sr * $xyzrgb[0][0], $sg * $xyzrgb[0][1], $sb * $xyzrgb[0][2]],
            [$sr * $xyzrgb[1][0], $sg * $xyzrgb[1][1], $sb * $xyzrgb[1][2]],
            [$sr * $xyzrgb[2][0], $sg * $xyzrgb[2][1], $sb * $xyzrgb[2][2]],
        ];

        return $m;
    }

    /**
     * Generate XYZ to RGB matrix
     * Generates inverse of matrix from self::rgb2xyz_matrix()
     * ($xr, $yr), ($xg, $yg), and ($xb, $yb) are chromaticity coordinates of an RGB system (such as sRGB)
     * ($xw, $yw, $zw) are a reference white vector (such as D65)
     *
     * To utilize matrix, RGB values MUST be linear and in the nominal range [0, 1]
     *
     * @param float $xr red   x chromaticity coordinate
     * @param float $yr red   y chromaticity coordinate
     * @param float $xg green x chromaticity coordinate
     * @param float $yg green y chromaticity coordinate
     * @param float $xb blue  x chromaticity coordinate
     * @param float $yb blue  y chromaticity coordinate
     * @param float $xw x reference white coordinate
     * @param float $yw y reference white coordinate
     * @param float $zw z reference white coordinate
     * @return float[] 3x3 matrix for converting XYZ to RGB
     */
    public static function xyz2rgb_matrix($xr, $yr, $xg, $yg, $xb, $yb, $xw, $yx, $zw) {
        $rgb2xyz_matrix = self::rgb2xyz_matrix($xr, $yr, $xg, $yg, $xb, $yb, $xw, $yx, $zw);
        return Util::matrix_3x3_inverse($rgb2xyz_matrix);
    }

    /**
     * Convert XYZ to xyY
     *
     * @param float $x
     * @param float $y
     * @param float $z
     * @return float[] x, y, Y
     */
    public static function xyz2xyy($x, $y, $z) {
        $sum = $x + $y + $z;
        if (!$sum) {
            $cx = 0;
            $cy = 0;
        }
        else {
            $cx  = $x / $sum;
            $cy  = $y / $sum;
        }

        return array($cx, $cy, $y);
    }

    /**
     * Convert xyY to XYZ
     *
     * @param float $x  x
     * @param float $y  y
     * @param float $yy Y
     * @return float[] X, Y, Z
     */
    public static function xyy2xyz($x, $y, $yy) {
        if (!$y) {
            $cx = 0;
            $cy = 0;
        }
        else {
            $cx = ($x * $yy) / $y;
            $cz = ((1 - $x - $y) * $yy) / $y;
        }

        return array($cx, $y, $cz);
    }

    /////////////////////// Lab ////////////////////////

    /**
     * Convert XYZ to Lab
     *
     * @param float $x 0 to 1
     * @param float $y 0 to 1
     * @param float $z 0 to 1
     * @param string|array $reference_white string (one of listed standards) or 1x3 matrix
     * @return float[] L, a, b
     */
    public static function xyz2lab($x, $y, $z, $reference_white='d65') {
        if (is_array($reference_white) && sizeof($reference_white) == 3) {
            // Strip keys, if present, and validate matrix
            $reference_white = array_values($reference_white);
            foreach ($reference_white as $value) {
                if (!is_numeric($value)) {
                    throw new \UnexpectedValueException('Invalid reference white matrix values');
                }
            }
        }
        elseif (is_string($reference_white)) {
            $reference_white = strtolower($reference_white);
            if (empty(Reference::STD_ILLUMINANTS[$reference_white]['vector'])) {
                throw new \UnexpectedValueException('Invalid reference white name');
            }
            $w = Reference::STD_ILLUMINANTS[$reference_white]['vector'];
        }
        else {
            throw new \UnexpectedValueException('Invalid reference white matrix; must be string from defined list or 1x3 matrix');
        }
        
        $xr = $x / $w[0];
        $yr = $y / $w[1];
        $zr = $z / $w[2];
        
        $fx = $x > Reference::CIE_E ? pow($xr, 1/3) : (Reference::CIE_K * $xr + 16) / 116;
        $fy = $y > Reference::CIE_E ? pow($yr, 1/3) : (Reference::CIE_K * $yr + 16) / 116;
        $fz = $z > Reference::CIE_E ? pow($zr, 1/3) : (Reference::CIE_K * $zr + 16) / 116;

        $l = 116 * $fy - 16;
        $a = 500 * ($fx - $fy);
        $b = 200 * ($fy - $fz);

        return array($l, $a, $b);
    }

    /**
     * Convert Lab to XYZ
     *
     * @param float $l
     * @param float $a
     * @param float $b
     * @param string|array $reference_white string (one of listed standards) or 1x3 matrix
     * @return float[] x, y, z
     */
    public static function lab2xyz($l, $a, $b, $reference_white='d65') {
        if (is_array($reference_white) && sizeof($reference_white) == 3) {
            // Strip keys, if present, and validate matrix
            $reference_white = array_values($reference_white);
            foreach ($reference_white as $value) {
                if (!is_numeric($value)) {
                    throw new \UnexpectedValueException('Invalid reference white matrix values');
                }
            }
        }
        elseif (is_string($reference_white)) {
            $reference_white = strtolower($reference_white);
            if (empty(Reference::STD_ILLUMINANTS[$reference_white]['vector'])) {
                throw new \UnexpectedValueException('Invalid reference white name');
            }
            $w = Reference::STD_ILLUMINANTS[$reference_white]['vector'];
        }
        else {
            throw new \UnexpectedValueException('Invalid reference white matrix; must be string from defined list or 1x3 matrix');
        }

        $fy = ($l + 16) / 116;
        $fx = $a / 500 + $fy;
        $fz = $fy - $b / 200;

        $xr = pow($fx, 3) > Reference::CIE_E ? pow($fx, 3) : (116 * $fx - 16) / Reference::CIE_K;
        $yr = $l > Reference::CIE_K * Reference::CIE_E ? pow(($l + 16) / 116, 3) : $l / Reference::CIE_K;
        $zr = pow($fz, 3) > Reference::CIE_E ? pow($fz, 3) : (116 * $fz - 16) / Reference::CIE_K;
        
        $x = $xr * $w[0];
        $y = $yr * $w[1];
        $z = $zr * $w[2];

        return array($x, $y, $z);
    }
    
    /////////////////////// Luv ////////////////////////

    /**
     * Convert XYZ to Luv
     * L will range between 0% and 100%
     * u and v will range between -100% and 100%
     *
     * Common Reference White Standards:
     * a   CIE standard illuminant A; 2856 K
     * c   CIE standard illuminant C; 6774 K; deprecated
     * e   Equal-energy radiator
     * d50 CIE standard illuminant D50; 5003 K
     * d55 CIE standard illuminant D55; 5500 K
     * d65 CIE standard illuminant D65; 6504 K
     * icc Profile Connection Space (PCS) illuminant used in ICC profiles
     *
     * @param float $x 0 to 1
     * @param float $y 0 to 1
     * @param float $z 0 to 1
     * @param string|array $reference_white string (one of listed standards) or 1x3 matrix
     * @return float[] L, u, v
     */
    public static function xyz2luv($x, $y, $z, $reference_white='d65') {
        if (is_array($reference_white) && sizeof($reference_white) == 3) {
            // Strip keys, if present, and validate matrix
            $reference_white = array_values($reference_white);
            foreach ($reference_white as $value) {
                if (!is_numeric($value)) {
                    throw new \UnexpectedValueException('Invalid reference white matrix values');
                }
            }
        }
        elseif (is_string($reference_white)) {
            $reference_white = strtolower($reference_white);
            if (empty(Reference::STD_ILLUMINANTS[$reference_white]['vector'])) {
                throw new \UnexpectedValueException('Invalid reference white name');
            }
            $w = Reference::STD_ILLUMINANTS[$reference_white]['vector'];
        }
        else {
            throw new \UnexpectedValueException('Invalid reference white matrix; must be string from defined list or 1x3 matrix');
        }

        $yr = $y / $w[1];

        $div = ($x + 15 * $y + 3 * $z);
        if (!$div) {
            $up = 0;
            $vp = 0;
        }
        else {
            $up = (4 * $x) / ($x + 15 * $y + 3 * $z);
            $vp = (9 * $y) / ($x + 15 * $y + 3 * $z);
        }
        
        $upr = (4 * $w[0]) / ($w[0] + 15 * $w[1] + 3 * $w[2]);
        $vpr = (9 * $w[1]) / ($w[0] + 15 * $w[1] + 3 * $w[2]);

        $l = $yr > Reference::CIE_E ? 116 * pow($yr, 1/3) - 16 : Reference::CIE_K * $yr;
        $u = 13 * $l * ($up - $upr);
        $v = 13 * $l * ($vp - $vpr);

        return array($l, $u, $v);
    }

    /**
     * Convert Luv to XYZ
     * X, Y, and Z will be in range 0 to 1
     *
     * Common Reference White Standards:
     * a   CIE standard illuminant A; 2856 K
     * c   CIE standard illuminant C; 6774 K; deprecated
     * e   Equal-energy radiator
     * d50 CIE standard illuminant D50; 5003 K
     * d55 CIE standard illuminant D55; 5500 K
     * d65 CIE standard illuminant D65; 6504 K
     * icc Profile Connection Space (PCS) illuminant used in ICC profiles
     *
     * @param float $l 0 to 100
     * @param float $u -100 to 100
     * @param float $v -100 to 100
     * @param string|array $reference_white string (one of listed standards) or 1x3 matrix
     * @return float[] x, y, z
     */
    public static function luv2xyz($l, $u, $v, $reference_white='d65') {
        Util::value_range_check($l, 0, 100);
        Util::value_range_check($u, -100, 100);
        Util::value_range_check($v, -100, 100);

        if (is_array($reference_white) && sizeof($reference_white) == 3) {
            // Strip keys, if present, and validate matrix
            $reference_white = array_values($reference_white);
            foreach ($reference_white as $value) {
                if (!is_numeric($value)) {
                    throw new \UnexpectedValueException('Invalid reference white matrix values');
                }
            }
        }
        elseif (is_string($reference_white)) {
            $reference_white = strtolower($reference_white);
            if (empty(Reference::STD_ILLUMINANTS[$reference_white]['vector'])) {
                throw new \UnexpectedValueException('Invalid reference white name');
            }
            $w = Reference::STD_ILLUMINANTS[$reference_white]['vector'];
        }
        else {
            throw new \UnexpectedValueException('Invalid reference white matrix; must be string from defined list or 1x3 matrix');
        }
        
        $u0 = (4 * $w[0]) / ($w[0] + 15 * $w[1] + 3 * $w[2]);
        $v0 = (9 * $w[1]) / ($w[0] + 15 * $w[1] + 3 * $w[2]);

        $y = $l > Reference::CIE_K * Reference::CIE_E ? pow(($l + 16) / 116, 3) : $l / Reference::CIE_K;

        $a = 1 / 3 * (((52 * $l) / ($u + 13 * $l * $u0)) - 1);
        $b = -5 * $y;
        $c = -1 / 3;
        $d = $y * (((39 * $l) / ($v + 13 * $l * $v0)) - 5);

        $x = ($d - $b) / ($a - $c);
        $z = $x * $a + $b;

        return array($x, $y, $z);
    }

    /////////////////////// YCbCr and STANDARDS ////////////////////////

    //todo: YUV

    /**
     * Convert RGB to Rec709 RGB
     * Will output either 8-bit or 10-bit depending on input color space
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per INPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function rgb2rec709rgb($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        // output must be 8-bit or 10-bit, pick whichever is closer to input depth
        if (abs($color_depth - 256) < abs($color_depth - 1024)) {
            // 8-bit
            $rgb_lower = 16;
            $rgb_upper = 235;
        }
        else {
            // 10-bit
            $rgb_lower = 64;
            $rgb_upper = 940;
        }

        $r = $rgb_lower + (($rgb_upper - $rgb_lower) * $red / $color_depth);
        $g = $rgb_lower + (($rgb_upper - $rgb_lower) * $green / $color_depth);
        $b = $rgb_lower + (($rgb_upper - $rgb_lower) * $blue / $color_depth);

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }
    
    /**
     * Convert Rec709 RGB to RGB
     * Converts 8-bit or 10-bit Rec709 RGB values to standard (0 - $color_depth) range
     * Input RGB values outside of legal black and white points will be clamped
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int $bit_depth 8 or 10
     * @param bool $round
     * @param int $color_depth RGB max value per OUTPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function rec709rgb2rgb($red, $green, $blue, $bit_depth=8, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        if ($bit_depth == 8) {
            Util::value_range_check($red, 0, 255);
            Util::value_range_check($green, 0, 255);
            Util::value_range_check($blue, 0, 255);

            // Clamp values above and below Rec709 threshold
            $red = min(max($red,235),16);
            $green = min(max($green,235),16);
            $blue = min(max($blue,235),16);

            $range_from = 219;
            $min_from = 16;
        }
        elseif ($bit_depth = 10) {
            Util::value_range_check($red, 0, 1023);
            Util::value_range_check($green, 0, 1023);
            Util::value_range_check($blue, 0, 1023);

            // Clamp values above and below Rec709 threshold
            $red = min(max($red,940),64);
            $green = min(max($green,940),64);
            $blue = min(max($blue,940),64);

            $range_from = 876;
            $min_from = 64;
        }
        else {
            throw new \UnexpectedValueException('Invalid bit depth, Rec709 bit depth must be 8 or 10');
        }
        
        // (ValueFrom * (RangeTo / RangeFrom)) + (MinTo - MinFrom)
        $r = ($red * ($color_depth / $range_from)) + (-1 * $min_from);
        $g = ($green * ($color_depth / $range_from)) + (-1 * $min_from);
        $b = ($blue * ($color_depth / $range_from)) + (-1 * $min_from);

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /**
     * Convert RGB to Rec2020 RGB
     * Will output either 10-bit or 12-bit depending on input color space
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per INPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function rgb2rec2020rgb($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        // output must be 10-bit or 12-bit, pick whichever is closer to input depth
        if (abs($color_depth - 1024) < abs($color_depth - 4096)) {
            // 10-bit
            $rgb_lower = 64;
            $rgb_upper = 940;
        }
        else {
            // 12-bit
            $rgb_lower = 256;
            $rgb_upper = 3760;
        }

        $r = $rgb_lower + (($rgb_upper - $rgb_lower) * $red / $color_depth);
        $g = $rgb_lower + (($rgb_upper - $rgb_lower) * $green / $color_depth);
        $b = $rgb_lower + (($rgb_upper - $rgb_lower) * $blue / $color_depth);

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /**
     * Convert Rec2020 RGB to RGB
     * Converts 10-bit or 12-bit Rec2020 RGB values to standard (0 - $color_depth) range
     * Input RGB values outside of legal black and white points will be clamped
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int $bit_depth 10 or 12
     * @param bool $round
     * @param int $color_depth RGB max value per OUTPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function rec2020rgb2rgb($red, $green, $blue, $bit_depth=10, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        if ($bit_depth = 10) {
            Util::value_range_check($red, 0, 1023);
            Util::value_range_check($green, 0, 1023);
            Util::value_range_check($blue, 0, 1023);

            // Clamp values above and below Rec2020 threshold
            $red = min(max($red,940),64);
            $green = min(max($green,940),64);
            $blue = min(max($blue,940),64);

            $range_from = 876;
            $min_from = 64;
        }
        elseif ($bit_depth = 12) {
            Util::value_range_check($red, 0, 4095);
            Util::value_range_check($green, 0, 4095);
            Util::value_range_check($blue, 0, 4095);

            // Clamp values above and below Rec2020 threshold
            $red = min(max($red,3760),256);
            $green = min(max($green,3760),256);
            $blue = min(max($blue,3760),256);

            $range_from = 3504;
            $min_from = 256;
        }
        else {
            throw new \UnexpectedValueException('Invalid bit depth, Rec2020 bit depth must be 10 or 12');
        }
        
        // (ValueFrom * (RangeTo / RangeFrom)) + (MinTo - MinFrom)
        $r = ($red * ($color_depth / $range_from)) + (-1 * $min_from);
        $g = ($green * ($color_depth / $range_from)) + (-1 * $min_from);
        $b = ($blue * ($color_depth / $range_from)) + (-1 * $min_from);

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }
    
    /**
     * Convert RGB to Rec709 YCbCr
     * Will output either 8-bit or 10-bit depending on input color space
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int $bit_rate 8 or 10
     * @param bool $round
     * @param int $color_depth RGB max value per INPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[] Y, Cb, Cr
     */
    public static function rgb2rec709ycbcr($red, $green, $blue, $bit_rate=8, $round=true, $color_depth=255) {
        list($yp, $pb, $pr) = self::rgb2ypbpr($red, $green, $blue, 0.0722, 0.2126, $color_depth);

        if ($bit_rate == 8) {
            $y_lower = 16;
            $y_upper = 235;
            $c_lower = 16;
            $c_upper = 240;
        }
        elseif ($bit_rate == 10) {
            $y_lower = 64;
            $y_upper = 940;
            $c_lower = 64;
            $c_upper = 960;
        }
        else {
            throw new \UnexpectedValueException('Invalid bit depth, Rec709 bit depth must be 8 or 10');
        }
        
        return self::ypbpr2ycbcr($yp, $pb, $pr, $y_lower, $y_upper, $c_lower, $c_upper, $round);
    }

    /**
     * Convert Rec709 YCbCr to RGB
     *
     * @param float $y 16 to 235 OR 64 to 940
     * @param float $cb 16 to 240 OR 64 to 960
     * @param float $cr 16 to 240 OR 64 to 960
     * @param int $bit_depth 8 or 10
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return int[]|float[] red, green, blue
     */
    public static function rec709ycbcr2rgb($y, $cb, $cr, $bit_depth=8, $round=true, $color_depth=255) {
        if ($bit_depth == 8) {
            list($yp, $pb, $pr) = self::ycbcr2ypbpr($y, $cb, $cr, 16, 235, 16, 240);
        }
        elseif ($bit_depth == 10) {
            list($yp, $pb, $pr) = self::ycbcr2ypbpr($y, $cb, $cr, 64, 940, 64, 960);
        }
        else {
            throw new \UnexpectedValueException('Invalid bit depth, Rec709 bit depth must be 8 or 10');
        }

        return self::ypbpr2rgb($yp, $pb, $pr, 0.0722, 0.2126, $round, $color_depth);
    }
    
    /**
     * Convert RGB to Rec2020 YCbCr
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param int $bit_rate 10 or 12
     * @param bool $round
     * @param int $color_depth RGB max value per INPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[] Y, Cb, Cr
     */
    public static function rgb2rec2020ycbcr($red, $green, $blue, $bit_rate=10, $round=true, $color_depth=255) {
        list($yp, $pb, $pr) = self::rgb2ypbpr($red, $green, $blue, 0.0593, 0.2627, $color_depth);

        if ($bit_rate == 10) {
            $y_lower = 64;
            $y_upper = 940;
            $c_lower = 64;
            $c_upper = 960;
            $depth = 1023;
        }
        elseif ($bit_rate == 12) {
            $y_lower = 256;
            $y_upper = 3760;
            $c_lower = 256;
            $c_upper = 3840;
            $depth = 4095;
        }
        else {
            throw new \UnexpectedValueException('Invalid bit depth, Rec2020 bit depth must be 10 or 12');
        }
        
        return self::ypbpr2ycbcr($yp, $pb, $pr, $y_lower, $y_upper, $c_lower, $c_upper, $round);
    }

    /**
     * Convert Rec2020 YCbCr to RGB
     *
     * @param float $y 64 to 940 OR 256 to 3760
     * @param float $cb 64 to 960 OR 256 to 3840
     * @param float $cr 64 to 960 OR 256 to 3840
     * @param int $bit_depth 10 or 12
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return int[]|float[] red, green, blue
     */
    public static function rec2020ycbcr2rgb($y, $cb, $cr, $bit_depth=8, $round=true, $color_depth=255) {
        if ($bit_depth == 10) {
            list($yp, $pb, $pr) = self::ycbcr2ypbpr($y, $cb, $cr, 64, 940, 64, 960);
        }
        elseif ($bit_depth == 12) {
            list($yp, $pb, $pr) = self::ycbcr2ypbpr($y, $cb, $cr, 256, 3760, 256, 3840);
        }
        else {
            throw new \UnexpectedValueException('Invalid bit depth, Rec2020 bit depth must be 10 or 12');
        }

        return self::ypbpr2rgb($yp, $pb, $pr, 0.0593, 0.2627, $round, $color_depth);
    }

    /**
     * Convert Rec709 YCbCr to Rec2020 YCbCr
     *
     * @param float $y 16 to 235 OR 64 to 940
     * @param float $cb 16 to 240 OR 64 to 960
     * @param float $cr 16 to 240 OR 64 to 960
     * @param int $bit_depth_in 8 or 10
     * @param int $bit_depth_out 10 or 12
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return int[]|float[] Y, Cb, Cr
     */
    public static function rec709ycbcr2rec2020ycbcr($y, $cb, $cr, $bit_depth_in=8, $bit_depth_out=10, $round=true) {
        $color_depth_in = (2 ** $bit_depth_in) - 1;
        $color_depth_out = (2 ** $bit_depth_out) - 1;
        list($r, $g, $b) = self::rec709ycbcr2rgb($y, $cb, $cr, $bit_depth_in, false, $color_depth_in);
        list($y_out, $cb_out, $cr_out) = self::rgb2rec2020ycbcr($r, $g, $b, $bit_depth_in, $round, $color_depth_out);

        return array($y_out, $cb_out, $cr_out);
    }

    /**
     * Convert Rec2020 YCbCr to Rec709 YCbCr
     *
     * @param float $y 64 to 940 OR 256 to 3760
     * @param float $cb 64 to 960 OR 256 to 3840
     * @param float $cr 64 to 960 OR 256 to 3840
     * @param int $bit_depth_in 10 or 12
     * @param int $bit_depth_out 8 or 10
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return int[]|float[] Y, Cb, Cr
     */
    public static function rec2020ycbcr2rec709ycbcr($y, $cb, $cr, $bit_depth_in=10, $bit_depth_out=8, $round=true) {
        $color_depth_in = (2 ** $bit_depth_in) - 1;
        $color_depth_out = (2 ** $bit_depth_out) - 1;
        list($r, $g, $b) = self::rec2020ycbcr2rgb($y, $cb, $cr, $bit_depth_in, false, $color_depth_in);
        list($y_out, $cb_out, $cr_out) = self::rgb2rec709ycbcr($r, $g, $b, $bit_depth_in, $round, $color_depth_out);

        return array($y_out, $cb_out, $cr_out);
    }

    /**
     * Convert RGB to YCbCr
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param float $kb Kb constant defined from target color space, such that Kb + Kr + Kg = 1
     * @param float $kr Kr constant defined from target color space, such that Kb + Kr + Kg = 1
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] Y, Cb, Cr
     */
    public static function rgb2ycbcr($red, $green, $blue, $kb, $kr, $round=true, $color_depth=255) {
        list($yp, $pb, $pr) = self::rgb2ypbpr($red, $green, $blue, $kb, $kr, $color_depth);
        return self::ypbpr2ycbcr($yp, $pb, $pr, 0, $color_depth, 0, $color_depth, $round);
    }
    
    /**
     * Convert RGB to YPbPr
     * Y will range from 0 to 1; Pb and Pr will range from -0.5 to 0.5
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param float $kb Kb constant defined from target color space, such that Kb + Kr + Kg = 1
     * @param float $kr Kr constant defined from target color space, such that Kb + Kr + Kg = 1
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[] Y, Cb, Cr
     */
    public static function rgb2ypbpr($red, $green, $blue, $kb, $kr, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        Util::value_range_check($kb, 0, 1);
        Util::value_range_check($kr, 0, 1);
        
        $kg = 1 - $kb - $kr;

        // Normalize RGB to 0-1 (R'B'G')
        $rp = $red   / $color_depth;
        $gp = $green / $color_depth;
        $bp = $blue  / $color_depth;

        // Y' ranges from 0 to 1
        $yp = $kr * $rp + $kg * $gp + $kb * $bp;

        // Pb and Pr range from -0.5 to +0.5
        $pb = (-0.5 * ($kr / (1 - $kb))) * $rp + (-0.5 * ($kg / (1 - $kb))) * $gp + 0.5 * $bp;
        $pr = 0.5 * $rp + (-0.5 * ($kg / (1 - $kr))) * $gp + (-0.5 * ($kb / (1 - $kr))) * $bp;

        return array($yp, $pb, $pr);
    }

    /**
     * Convert YPbPr to RGB
     * Y must range from 0 to 1; Pb and Pr must range from -0.5 to 0.5
     *
     * @param float $y 0 to 1
     * @param float $pb -0.5 to 0.5
     * @param float $pr -0.5 to 0.5
     * @param float $kb Kb constant defined from target color space, such that Kb + Kr + Kg = 1
     * @param float $kr Kr constant defined from target color space, such that Kb + Kr + Kg = 1
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return int[]|float[] red, green, blue
     */
    public static function ypbpr2rgb($y, $pb, $pr, $kb, $kr, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($yp, 0, 1);
        Util::value_range_check($pb, -0.5, 0.5);
        Util::value_range_check($pr, -0.5, 0.5);
        $yp = $y; // Y param means Y' for our purposes
        
        $kg = 1 - $kb - $kr;

        $r = $yp + (2 - 2 * $kr) * $pr;
        $g = $yp + (-1 * ($kb / $kg) * (2 - 2 * $kb)) * $pb + (-1 * ($kr / $kg) * (2 - 2 * $kr)) * $pr;
        $b = $yp + (2 - 2 * $kb) * $pb;
        
        $r *= $color_depth;
        $g *= $color_depth;
        $b *= $color_depth;

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }
    
    /**
     * Convert YPbPr to YCbCr
     * Y must be in range 0 to 1; Pb and Pr must be in range -0.5 to 0.5
     *
     * @param float $y 0 to 1
     * @param float $pb -0.5 to 0.5
     * @param float $pr -0.5 to 0.5
     * @param int|float $y_lower Lower bounds of Y
     * @param int|float $y_upper Upper bounds of Y
     * @param int|float $c_lower Lower bounds of Cb and Cr
     * @param int|float $c_upper Upper bounds of Cb and Cr
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return int[]|float[] Y, Cb, Cr
     */
    public static function ypbpr2ycbcr($y, $pb, $pr, $y_lower, $y_upper, $c_lower, $c_upper, $round=true) {
        Util::value_range_check($y, 0, 1);
        Util::value_range_check($pb, -0.5, 0.5);
        Util::value_range_check($pr, -0.5, 0.5);
        $yp = $y; // Y param is Y' for our purposes

        // Convert Y' to Y
        $y = $y_lower + (($y_upper - $y_lower) * $yp / 1);

        // Normalize Pb and Pr (-0.5 to 0.5) to positive numbers (0 to 1)
        $pbp = $pb + 1;
        $prp = $pr + 1;

        // Convert Pb and Pr to Cb and Cr
        // Pb and Pr are normalized to positive integers from (-0.5 to 0.5) to (0 to 1)
        $cb = $c_lower + (($c_upper - $c_lower) * ($pb + 0.5) / 1);
        $cr = $c_lower + (($c_upper - $c_lower) * ($pr + 0.5) / 1);

        if ($round) {
            $y = round($y);
            $cb = round($cb);
            $cr = round($cr);
        }

        return array($y, $cb, $cr);
    }

    /**
     * Convert YCbCr to YPbPr
     * Y will be in range 0 to 1; Pb and Pr will be in range -0.5 to 0.5
     *
     * @param int|float $y
     * @param int|float $cb
     * @param int|float $cr
     * @param int|float $y_lower Lower bounds of Y
     * @param int|float $y_upper Upper bounds of Y
     * @param int|float $c_lower Lower bounds of Cb and Cr
     * @param int|float $c_upper Upper bounds of Cb and Cr
     * @param bool $round
     * @throws \UnexpectedValueException invalid color value
     * @return float[] Y, Pb, Pr
     */
    public static function ycbcr2ypbpr($y, $cb, $cr, $y_lower, $y_upper, $c_lower, $c_upper) {
        Util::value_range_check($y, $y_lower, $y_upper);
        Util::value_range_check($cb, $c_lower, $c_upper);
        Util::value_range_check($cr, $c_lower, $c_upper);

        // (ValueFrom * (RangeTo / RangeFrom)) + (MinTo - MinFrom)
        $yp = ($y * (1 / ($y_upper - $y_lower))) + $y_lower;
        $pb = ($cb * (1 / ($c_upper - $c_lower))) + ($c_lower - -0.5);
        $pr = ($cr * (1 / ($c_upper - $c_lower))) + ($c_lower - -0.5);

        return array($yp, $pb, $pr);
    }
    
    /**
     * Convert RGB to JPEG YCbCr
     * Output Y, Cb, and Cr range from 0 to 255
     *
     * @param int|float $red
     * @param int|float $green
     * @param int|float $blue
     * @param bool $round
     * @param int $color_depth RGB max value per INPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] Y, Cb, Cr
     */
    public static function rgb2jpegycbcr($red, $green, $blue, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);
        
        if ($color_depth != 255) {
            // Equations use RGB values between 0 and 255
            $red   = $red   / $color_depth * 255;
            $green = $green / $color_depth * 255;
            $blue  = $blue  / $color_depth * 255;
        }

        $y = 0.299 * $red + 0.587 * $green + 0.114 * $blue;
        $cb = 128 - 0.168736 * $red - 0.331264 * $green + 0.5 * $blue;
        $cr = 128 + 0.5 * $red - 0.418688 * $green - 0.081312 * $blue;

        if ($round) {
            $y = round($y);
            $cb = round($cb);
            $cr = round($cr);
        }

        return array($y, $cb, $cr);
    }

    /**
     * Convert JPEG YCbCr to RGB
     * Y, Cb, and Cr should range from 0 to 255
     *
     * @param int|float $y 0 to 255
     * @param int|float $cb 0 to 255
     * @param int|float $cr 0 to 255
     * @param bool $round
     * @param int $color_depth RGB max value per OUTPUT channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function jpegycbcr2rgb($y, $cb, $cr, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($y, 0, 255);
        Util::value_range_check($cb, 0, 255);
        Util::value_range_check($cr, 0, 255);

        $r = $y + 1.402 * ($cr - 128);
        $g = $y - 0.344136 * ($cb - 128) - 0.714136 * ($cr - 128);
        $b = $y + 1.772 * ($cb - 128);

        if ($color_depth != 255) {
            // Equations use RGB values between 0 and 255
            $r = $r / 255 * $color_depth;
            $g = $g / 255 * $color_depth;
            $b = $b / 255 * $color_depth;
        }

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }

    /////////////////////// ONE WAY APPROXIMATIONS to RGB ////////////////////////

    /**
     * Convert a wavelength in nm to RGB
     * This is hugely perceptual and approximate
     * Original algorithm:
     * https://academo.org/demos/wavelength-to-colour-relationship/
     *
     * @param int|float $wavelength Wavelength of light in nanometers (positive number)
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function nm2rgb($wavelength, $gamma=0.8, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($wavelength, 200, 800); // actual falloff ~380-781

        if ($wavelength >= 380 && $wavelength < 440) {
            $r = (($wavelength - 440) / (440 - 380)) * -1;
            $g = 0;
            $b = 1;
        }
        elseif ($wavelength >= 440 && $wavelength < 490) {
            $r = 0;
            $g = ($wavelength - 440) / (490 - 440);
            $b = 1;
        }
        elseif ($wavelength >= 510 && $wavelength < 580) {
            $r = ($wavelength - 510) / (580 - 510);
            $g = 1;
            $b = 0;
        }
        elseif ($wavelength >= 580 && $wavelength < 645) {
            $r = 1;
            $g = (($wavelength - 645) / (645 - 580)) * -1;
            $b = 0;
        }
        elseif ($wavelength >= 645 && $wavelength < 781) {
            $r = 1;
            $g = 0;
            $b = 0;
        }
        else {
            $r = 0;
            $g = 0;
            $b = 0;
        }

        // Let the intensity fall off near the vision limits
        if ($wavelength >= 380 && $wavelength < 420) {
            $factor = 0.3 + 0.7 * ($wavelength - 380) / (420 - 380);
        }
        elseif ($wavelength >= 420 && $wavelength < 701) {
            $factor = 1;
        }
        elseif ($wavelength >= 701 && $wavelength < 781) {
            $factor = 0.3 + 0.7 * (780 - $wavelength) / (780 - 700);
        }
        else {
            $factor = 0;
        }
        
        if ($r > 0) {
            $r = $color_depth * pow($r * $factor, $gamma);
        }
        if ($g > 0) {
            $g = $color_depth * pow($g * $factor, $gamma);
        }
        if ($b > 0) {
            $b = $color_depth * pow($b * $factor, $gamma);
        }

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }
    
    /**
     * Convert a color temperature in Kelvin to RGB
     * Not accurate for scientific purposes
     * Original algorithm from:
     * https://tannerhelland.com/2012/09/18/convert-temperature-rgb-algorithm-code.html
     *
     * @param int|float $temperature Color temperature in degrees Kelvin; must fall between 1000 and 40000
     * @param bool $round
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return float[]|int[] red, green, blue
     */
    public static function kelvin2rgb($temperature, $round=true, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($temperature, 1000, 40000);

        $temp_simp = $temperature / 100;

        $scalar = $color_depth / 255;

        if ($temp_simp <= 66) {
            $r = $color_depth;
            $g = 99.4708025861 * log($temp_simp) - 161.1195681661;
        }
        else {
            $r = 329.698727466 * pow($temp_simp - 60, -0.1332047592);
            $g = 288.1221695283 * pow($temp_simp - 60, -0.0755148492);
        }

        if ($temp_simp >= 66) {
            $b = $color_depth;
        }
        elseif ($temp_simp <= 19) {
            $b = 0;
        }
        else {
            $b = 138.5177312231 * log($temp_simp - 10) - 305.0447927307;
        }

        $r *= $scalar;
        $g *= $scalar;
        $b *= $scalar;
        
        $r = min(max($r, 0), $color_depth);
        $g = min(max($g, 0), $color_depth);
        $b = min(max($b, 0), $color_depth);

        if ($round) {
            $r = round($r);
            $g = round($g);
            $b = round($b);
        }

        return array($r, $g, $b);
    }
    
    /////////////////////// HEXIDECIMAL (RGB SHORTCUTS) ////////////////////////

    /**
     * Convert HEX to RGB
     *
     * @param string $hex
     * @throws \UnexpectedValueException invalid color value
     * @return int[] red, green, blue
     */
    public static function hex2rgb($hex) {
        $hex = Util::expand_hex($hex);
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));

        return array($r,$g,$b);
    }

    /**
     * Convert RGB to HEX
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $color_depth RGB max value per channel
     * @throws \UnexpectedValueException invalid color value
     * @return string RRGGBB hex
     */
    public static function rgb2hex($red, $green, $blue, $color_depth=255) {
        Util::value_range_check($color_depth, 1, 65535);
        Util::value_range_check($red, 0, $color_depth);
        Util::value_range_check($green, 0, $color_depth);
        Util::value_range_check($blue, 0, $color_depth);

        $scalar = 255 / $color_depth;
        $r = round($red * $scalar);
        $g = round($green * $scalar);
        $b = round($blue * $scalar);

        $r = str_pad(dechex($red), 2, "0", STR_PAD_LEFT);
        $g = str_pad(dechex($green), 2, "0", STR_PAD_LEFT);
        $b = str_pad(dechex($blue), 2, "0", STR_PAD_LEFT);
        $rgb = $r.$g.$b;
        return $rgb;
    }

    /////////// FROM HEX

    /**
     * Convert HEX to HSV
     * Saturation and Value are in percentages
     *
     * @param mixed $hex RGB or RRGGBB
     * @throws \UnexpectedValueException invalid color value
     * @return int[] hue, saturation, value
     */
    public static function hex2hsv($hex) {
        list($r,$g,$b) = self::hex2rgb($hex);
        return self::rgb2hsv($r,$g,$b);
    }
    
    /**
     * Convert HEX to HSL
     * Saturation and Lightness are in percentages
     *
     * @param mixed $hex RGB or RRGGBB
     * @throws \UnexpectedValueException invalid color value
     * @return int[] hue, saturation, lightness
     */
    public static function hex2hsl($hex) {
        list($r,$g,$b) = self::hex2rgb($hex);
        return self::rgb2hsl($r,$g,$b);
    }
    
    /**
     * Convert HEX to HSI
     * Saturation and Intensity are in percentages
     *
     * @param mixed $hex RGB or RRGGBB
     * @throws \UnexpectedValueException invalid color value
     * @return int[] hue, saturation, lightness
     */
    public static function hex2hsi($hex) {
        list($r,$g,$b) = self::hex2rgb($hex);
        return self::rgb2hsi($r,$g,$b);
    }

    /////////// TO HEX

    /**
     * Convert HSV to HEX
     * Saturation and Value should be in percentages
     *
     * @param int $hue
     * @param int $saturation
     * @param int $value
     * @throws \UnexpectedValueException invalid color value
     * @return string RRGGBB hex
     */
    public static function hsv2hex($hue, $saturation, $value) {
        list($r,$g,$b) = self::hsv2rgb($hue, $saturation, $value);
        return self::rgb2hex($r,$g,$b);
    }
    
    /**
     * Convert HSL to HEX
     * Saturation and Lightness should be in percentages
     *
     * @param int $hue degrees 0 <= value <= 360
     * @param int $saturation percentage
     * @param int $lightness percentage
     * @throws \UnexpectedValueException invalid color value
     * @return string RRGGBB hex
     */
    public static function hsl2hex($hue, $saturation, $lightness) {
        list($r, $g, $b) = self::hsl2rgb($hue, $saturation, $lightness);
        return self::rgb2hex($r, $g, $b);
    }

    /**
     * Convert HSI to HEX
     * Saturation and Intensity should be in percentages
     *
     * @param int $hue degrees 0 <= value <= 360
     * @param int $saturation percentage
     * @param int $intensity percentage
     * @throws \UnexpectedValueException invalid color value
     * @return string RRGGBB hex
     */
    public static function hsi2hex($hue, $saturation, $intensity) {
        list($r, $g, $b) = self::hsi2rgb($hue, $saturation, $intensity);
        return self::rgb2hex($r, $g, $b);
    }
    
    /**
     * Convert a wavelength in nm to HEX
     * This is hugely perceptual and approximate
     *
     * @param int|float $wavelength Wavelength of light in nanometers (positive number)
     * @throws \UnexpectedValueException invalid color value
     * @return string RRGGBB hex
     */
    public static function nm2hex($wavelength) {
        list($r, $g, $b) = self::nm2rgb($wavelength);
        return self::rgb2hex($r, $g, $b);
    }
    
    /**
     * Convert a color temperature in Kelvin to HEX
     * Not accurate for scientific purposes
     * 
     * @param int|float $temperature Color temperature in degrees Kelvin; must fall between 1000 and 40000
     * @throws \UnexpectedValueException invalid color value
     * @return string RRGGBB hex
     */
    public static function kelvin2hex($temperature) {
        list($r, $g, $b) = self::kelvin2rgb($temperature);
        return self::rgb2hex($r, $g, $b);
    }
}