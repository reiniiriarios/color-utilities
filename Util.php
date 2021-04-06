<?php
namespace reiniiriarios\Colors;

class Util {
    /**
     * Normalizes $color to hex integer
     *
     * @param string|int|array $color RRGGBB, 0xRRGGBB, or int[R,G,B]
     * @throws \UnexpectedValueException
     * @return int 0xRRGGBB
     */
    public static function color2hexint($color) {
        if (is_string($color)) {
            if (!ctype_xdigit($color)) {
                throw new \UnexpectedValueException('Unable to parse color');
            }
            $color = hexdec($color);
        }
        elseif (is_array($color)) {
            if (count($color) != 3) {
                throw new \UnexpectedValueException('Unable to parse color');
            }
            foreach ($color as &$color) {
                if (!is_int($color)) {
                    throw new \UnexpectedValueException('Unable to parse color');
                }
                elseif ($color < 0 || $color > 255) {
                    throw new \UnexpectedValueException('Unable to parse color');
                }
            }
            $color = bin2hex($color[0]).bin2hex($color[1]).bin2hex($color[2]);
            $color = hexdec($color);
        }

        if ($color < 0 || $color > 0xFFFFFF) {
            throw new \UnexpectedValueException('Unable to parse color');
        }

        return $color;
    }

    /**
     * Normalizes $color to hex string
     *
     * @param string|int|array $color RRGGBB, 0xRRGGBB, or int[R,G,B]
     * @throws \UnexpectedValueException
     * @return string RRGGBB
     */
    public static function color2hexstr($color) {
        $color = self::color2hexint($color);
        $color = dechex($color);

        return $color;
    }

    /**
     * Expands shorthand hex color values to full 6-digit values
     *
     * @param mixed $hex RGB or RRGGBB
     * @throws \UnexpectedValueException invalid $hex value
     * @return string RRGGBB
     */
    public static function expand_hex($hex) {
        if (!is_string($hex) || !ctype_xdigit($hex)) {
            throw new \UnexpectedValueException('Invalid $hex value');
        }
        if (strlen($hex) == 6) {
            return $hex;
        }
        if (strlen($hex != 3)) {
            throw new \UnexpectedValueException('Invalid $hex value');
        }

        $r = substr($hex,0,1);
        $g = substr($hex,1,1);
        $b = substr($hex,2,1);
        $r .= $r;
        $g .= $g;
        $b .= $b;
        $full_hex = $r.$g.$b;

        return $full_hex;
    }

    /**
     * Range check to make sure numeric $value is within lower and upper limits
     *
     * @param int|float $value
     * @param int|float $lower_limit
     * @param int|float $upper_limit
     * @throws \UnexpectedValueException value is outside limits
     */
    public static function value_range_check($value, $lower_limit, $upper_limit) {
        if (!is_numeric($value)) {
            throw new \UnexpectedValueException('Invalid color value');
        }
        if (!is_numeric($lower_limit) || !is_numeric($upper_limit)) {
            throw new \UnexpectedValueException('Invalid range');
        }
        if ($lower_limit > $upper_limit) {
            throw new \UnexpectedValueException('Invalid range (lower limit must be less than upper limit)');
        }
        if ($value < $lower_limit || $value > $upper_limit) {
            throw new \UnexpectedValueException('Invalid color value, ' . $value . ' does not fall within range ' . $lower_limit . ' - ' . $upper_limit);
        }
    }

    /**
     * Generates the inverse of a 3x3 matrix
     * Utilized for XYZ matrix generation
     *
     * @param array $matrix 3x3 matrix
     * @return array $matrix^-1
     */
    public static function matrix_3x3_inverse($matrix) {
        // Calculate matrix of minors and matrix of cofactors
        $minors = [];
        $cofactors = [];
        $flip_sign = false;
        foreach ($matrix as $row_n => $row) {
            foreach ($row as $col_n => $value) {
                $ax = $col_n == 0 ? 1 : 0;
                $ay = $row_n == 0 ? 1 : 0;
                $dx = $col_n == 2 ? 1 : 2;
                $dy = $row_n == 2 ? 1 : 2;
                $bx = $col_n == 2 ? 1 : 2;
                $by = $row_n == 0 ? 1 : 0;
                $cx = $col_n == 0 ? 1 : 0;
                $cy = $row_n == 2 ? 1 : 2;

                $minors[$row_n][$col_n] = $matrix[$ax][$ay] * $matrix[$dx][$dy] - $matrix[$bx][$by] * $matrix[$cx][$cy];
                if ($flip_sign) {
                    $cofactor = $minors[$row_n][$col_n] * -1;
                    $flip_sign = false;
                }
                else {
                    $cofactor = $minors[$row_n][$col_n];
                    $flip_sign = true;
                }
                $cofactors[$row_n][$col_n] = $cofactor;
            }
        }
        
        // Calculate adjugate matrix
        $adjugate = $cofactors;
        $adjugate[0][1] = $cofactors[1][0];
        $adjugate[1][0] = $cofactors[0][1];
        $adjugate[0][2] = $cofactors[2][0];
        $adjugate[2][0] = $cofactors[0][2];
        $adjugate[1][2] = $cofactors[2][1];
        $adjugate[2][1] = $cofactors[1][2];

        // Determinant of matrix
        $determinant = $minors[0][0] * $cofactors[0][0] + $minors[0][1] * $cofactors[0][1] + $minors[0][2] * $cofactors[0][2];

        // Calculate inverse matrix
        $inverse = [];
        foreach ($adjugate as $row_n => $row) {
            foreach ($row as $col_n => $value) {
                $inverse[$row_n][$col_n] = $value * (1 / $determinant);
            }
        }

        return $inverse;
    }
}