<?php
namespace reiniiriarios\Colors;

class Harmony {
    /**
     * Return complement of $color
     *
     * @param string $color RRGGBB
     * @return string RRGGBB
     */
    public static function Complement($color) {
        $color = Convert::color2str($color);

        $hsv = Convert::hex2hsv($color);
        $hsv_complement_hue = Convert::hue_shift($hsv[0], 180);
        $rgb_complement = Convert::hsv2hex($hsv_complement_hue, $hsv[1], $hsv[2]);

        return $rgb_complement;
    }

    /**
     * Return analogous color scheme based on $color
     *
     * @param string $color RRGGBB
     * @param int|float $angle hue adjustment
     * @return string[] [RRGGBB, RRGGBB, RRGGBB]
     */
    public static function Analogous($color, $angle = 30) {
        Util::value_range_check($angle, 0, 360);

        $color = Convert::color2str($color);
        $angle = floatval($angle);

        $hsv = Convert::hex2hsv($color);
        $hsv_analogous1_hue = Convert::hue_shift($hsv[0], $angle);
        $hsv_analogous2_hue = Convert::hue_shift($hsv[0], $angle * -1);

        $rgb_analogous1 = Convert::hsv2hex($hsv_analogous1_hue, $hsv[1], $hsv[2]);
        $rgb_analogous2 = Convert::hsv2hex($hsv_analogous2_hue, $hsv[1], $hsv[2]);

        return array($color, $rgb_analogous1, $rgb_analogous2);
    }

    /**
     * Return triadic color scheme based on $color
     *
     * @param string $color RRGGBB
     * @return string[] [RRGGBB, RRGGBB, RRGGBB]
     */
    public static function Triadic($color) {
        return static::Analogous($color, 120); // same algorithm used
    }

    /**
     * Return split complementary color scheme based on $color
     *
     * @param string $color RRGGBB
     * @param int|float $angle hue adjustment
     * @return string[] [RRGGBB, RRGGBB, RRGGBB]
     */
    public static function ComplementSplit($color, $angle = 150) {
        return static::Analogous($color, $angle); // same algorithm used
    }

    /**
     * Return tetradic color scheme based on $color
     *
     * @param string $color RRGGBB
     * @param int|float $angle hue adjustment
     * @return string[] [RRGGBB, RRGGBB, RRGGBB, RRGGBB]
     */
    public static function Tetradic($color, $angle = 45) {
        Util::value_range_check($angle, 0, 360);
        
        $color = Convert::color2str($color);
        $angle = floatval($angle);

        $hsv = Convert::hex2hsv($color);
        $hsv_2_hue = Convert::hue_shift($hsv[0], $angle);
        $hsv_3_hue = Convert::hue_shift($hsv[0], $angle + 180);
        $hsv_4_hue = Convert::hue_shift($hsv[0], 180);

        $rgb_2 = Convert::hsv2hex($hsv_2_hue, $hsv[1], $hsv[2]);
        $rgb_3 = Convert::hsv2hex($hsv_3_hue, $hsv[1], $hsv[2]);
        $rgb_4 = Convert::hsv2hex($hsv_4_hue, $hsv[1], $hsv[2]);

        return array($color, $rgb_2, $rgb_3, $rgb_4);
    }

    /**
     * Return square color scheme based on $color
     *
     * @param string $color RRGGBB
     * @return string[] [RRGGBB, RRGGBB, RRGGBB, RRGGBB]
     */
    public static function Square($color) {
        return static::Tetradic($color, 90); // same algorithm used
    }
}