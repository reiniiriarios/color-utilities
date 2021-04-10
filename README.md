# color-utilities

Miscellaneous color scheme and conversion utilities.

** No longer well-maintained. See [chromaticity-color-utilities](https://github.com/reiniiriarios/chromaticity-color-utilities) for Node.js **

## Tools

### Convert

Formats

* RGB
* HSV
* HSL
* HSI
* CMYK
* YIQ
* XYZ
* XYY
* Lab
* Luv
* YUV
* YCbCr
* YPbPr
* Rec709 RGB
* Rec2020 RGB
* Rec709 YCbCr
* Rec2020 YCbCr
* JPEG YCbCr
* Wavelength (nm) to RGB (approx.)
* Kelvin to RGB (approx.)
* HEX

Examples

* `rgb2hsv()`
* `hsv2hsl()`
* `hsi2rgb()`
* `rgb2cmyk()`
* `rgb2ycbcr()`
* `ypbpr2ycbcr()`
* `nm2rgb()`
* `hex2rgb()`

### Harmony

* `Analogous($color, $angle)`
* `Triadic($color)` // alias of `Analogous($color, 120)`
* `ComplementSplit($color, $angle = 150)` // alias of `Analogous($color, $angle)`
* `Tetradic($color, $angle = 45)` 
* `Square($color)` // alias of `Tetradic($color, 90)`

### Modify

* `hue_shift($hue, $angle)`
* `blend($color1, $color2, $amount, $method)`
  Blends one color with another by `$amount` (0-1) by `$method` ('hex', 'rgb', or 'hsv')
  Can be used to produce gradients between colors 

### Words

* `words2rgb($phrase)` / `words2hex($phrase)`
  Uses Google Image Search to convert any phrase to a color

### Util

Utilities for this project, e.g.

* `color2hexint($color)`
  normalizes color to hex integer
  (such as 'FFFFFF' or [255,255,255] to 0xFFFFFF)
* `color2hexstr($color)`
  normalizes color to six-digit hex string
  (such as 0xFFFFFF or [255,255,255] to 'FFFFFF')
* `expand_hex($hex)`
  expands three-digit hex values to six digits

### Reference

Constants used in this project, e.g.

* CIE standards
* Standard Illuminant values
* Color space matrices

## Usage

### Examples

```php
$hsl_color        = \reiniiriarios\Colors\Convert::rgb2hsl($red, $green, $blue);
$rec709_color     = \reiniiriarios\Colors\Convert::rgb2rec709rgb($red, $green, $blue);
$rotated_hue      = \reiniiriarios\Colors\Modify::hue_shift($hue, $degrees);
$penguin_color    = \reiniiriarios\Colors\Words::words2rgb('penguins');
$analogous_scheme = \reiniiriarios\Colors\Harmony::Analogous($hex_color);
```
