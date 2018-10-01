# Cheatsheet for escpos-php by Mike42

###### Credits to Mike42, content taken from [escpos-php project's README.MD](https://github.com/mike42/escpos-php/blob/development/README.md)

## Handling things

### Initializing

#### __construct(PrintConnector $connector, CapabilityProfile $profile)
Construct new print object.

Parameters:
- `PrintConnector $connector`: The PrintConnector to send data to.
- `CapabilityProfile $profile` Supported features of this printer. If not set, the "default" CapabilityProfile will be used, which is suitable for Epson printers.

See [example/interface/](https://github.com/mike42/escpos-php/tree/master/example/interface/) for ways to open connections for different platforms and interfaces.

### Working with the rest

#### feed($lines)
Print and feed line / Print and feed n lines.

Parameters:

- `int $lines`: Number of lines to feed

#### pulse($pin, $on_ms, $off_ms)
Generate a pulse, for opening a cash drawer if one is connected. The default settings (0, 120, 240) should open an Epson drawer.

Parameters:

- `int $pin`: 0 or 1, for pin 2 or pin 5 kick-out connector respectively.
- `int $on_ms`: pulse ON time, in milliseconds.
- `int $off_ms`: pulse OFF time, in milliseconds.

## Printing...

### ...anything

#### initialize()
Initialize printer. This resets formatting back to the defaults.

#### setReverseColors($on)
Set black/white reverse mode on or off. In this mode, text is printed white on a black background.

Parameters:

- `boolean $on`: True to enable, false to disable.

Parameters:

- `int $color`: Color to use. Must be either `Printer::COLOR_1` (default), or `Printer::COLOR_2`

### ...text

#### text($str)
Add text to the buffer. Text should either be followed by a line-break, or `feed()` should be called after this.

Parameters:

- `string $str`: The string to print.

#### setFont($font)
Select font. Most printers have two fonts (Fonts A and B), and some have a third (Font C).

Parameters:

- `int $font`: The font to use. Must be either `Printer::FONT_A`, `Printer::FONT_B`, or `Printer::FONT_C`.

#### setTextSize($widthMultiplier, $heightMultiplier)
Set the size of text, as a multiple of the normal size.

Parameters:

- `int $widthMultiplier`: Multiple of the regular height to use (range 1 - 8).
- `int $heightMultiplier`: Multiple of the regular height to use (range 1 - 8).

#### selectPrintMode($mode)
Select print mode(s).

Parameters:

- `int $mode`: The mode to use. Default is `Printer::MODE_FONT_A`, with no special formatting. This has a similar effect to running `initialize()`.

Several MODE_* constants can be OR'd together passed to this function's `$mode` argument. The valid modes are:

- `MODE_FONT_A`
- `MODE_FONT_B`
- `MODE_EMPHASIZED`
- `MODE_DOUBLE_HEIGHT`
- `MODE_DOUBLE_WIDTH`
- `MODE_UNDERLINE`

#### setEmphasis($on)
Turn emphasized mode on/off.

Parameters:

- `boolean $on`: true for emphasis, false for no emphasis.
#### setJustification($justification)
Select justification.

Parameters:

- `int $justification`: One of `Printer::JUSTIFY_LEFT`, `Printer::JUSTIFY_CENTER`, or `Printer::JUSTIFY_RIGHT`.

#### setUnderline($underline)
Set underline for printed text.

Parameters:

- `int $underline`: Either `true`/`false`, or one of `Printer::UNDERLINE_NONE`, `Printer::UNDERLINE_SINGLE` or `Printer::UNDERLINE_DOUBLE`. Defaults to `Printer::UNDERLINE_SINGLE`.

#### setLineSpacing($height)

Set the height of the line.

Some printers will allow you to overlap lines with a smaller line feed.

Parameters:

- `int	$height`:	The height of each line, in dots. If not set, the printer will reset to its default line spacing.

### ...other things

#### graphics(EscposImage $image, $size)
Print an image to the printer.

Parameters:

- `EscposImage $img`: The image to print.
- `int $size`: Output size modifier for the image.

Size modifiers are:

- `IMG_DEFAULT` (leave image at original size)
- `IMG_DOUBLE_WIDTH`
- `IMG_DOUBLE_HEIGHT`

A minimal example:

```php
<?php
$img = EscposImage::load("logo.png");
$printer -> graphics($img);
```

#### qrCode($content, $ec, $size, $model)
Print the given data as a QR code on the printer.

- `string $content`: The content of the code. Numeric data will be more efficiently compacted.
- `int $ec` Error-correction level to use. One of `Printer::QR_ECLEVEL_L` (default), `Printer::QR_ECLEVEL_M`, `Printer::QR_ECLEVEL_Q` or `Printer::QR_ECLEVEL_H`. Higher error correction results in a less compact code.
- `int $size`: Pixel size to use. Must be 1-16 (default 3)
- `int $model`: QR code model to use. Must be one of `Printer::QR_MODEL_1`, `Printer::QR_MODEL_2` (default) or `Printer::QR_MICRO` (not supported by all printers).

#### barcode($content, $type)
Print a barcode.

Parameters:

- `string $content`: The information to encode.
- `int $type`: The barcode standard to output. If not specified, `Printer::BARCODE_CODE39` will be used.

Currently supported barcode standards are (depending on your printer):

- `BARCODE_UPCA`
- `BARCODE_UPCE`
- `BARCODE_JAN13`
- `BARCODE_JAN8`
- `BARCODE_CODE39`
- `BARCODE_ITF`
- `BARCODE_CODABAR`

Note that some barcode standards can only encode numbers, so attempting to print non-numeric codes with them may result in strange behaviour.
