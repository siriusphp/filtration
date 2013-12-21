#Built-in filters

## Callback

Allows using any function, closure or callback for filtering data. The callback will received the value to be filtered as the first argument.

Options:

- callback: the function/closure/callback that will be used as filter
- arguments: additional arguments for the callback

## Censor

Obfuscates certain words from a string

Options:

- start_characters: leaves untouched this number of characters from the begining of the filtered string. Default: 1
- start_characters: leaves untouched this number of characters from the end of the filtered string. Default: 1
- replacement_char: the character that will be used to replace the hidden chars. Default: *
- word: list of words that are censored. Default: look in the source file

## CleanArray

Removes null elements from an array. If the array is associative it preserves the keys.

Options:

- nullify: whether to use the Nullify filter to convert certain values into NULLs. Default: TRUE

## Double

Convert numbers to `double` using a specified precision

Options:

- precision: number of digits that the filtered value will have. Default: 2

## Integer

Convert numbers to `integer`

## NormalizeDate

Converts a `datetime` value from one format to another. Usually used to conver date provided by users into system dates.
Example: `12/10/2012` to `2012-12-10`

Options:

- input_format: the date format in which the value should have been provided. Default: `d/m/Y`
- output_format: the format in which the value is returned. Default: `Y-m-d`

## NormalizeNumber

Converts a number from a local format to a system format.
Example: `1 234,5` to `1234.5`

Options:

- thousands_separator. Default: `.`
- decimal_point. Default: `,`

## Nullify

Converts certain values to NULL

Options:

- empty_string: converts empty strings to NULL. Default: TRUE
- zero: converts zero numbers to NULL. Default: TRUE

## Obfuscate

Hides certain characters from a string

Options:

- start_characters: leaves untouched this number of characters from the begining of the filtered string. Default: 0
- start_characters: leaves untouched this number of characters from the end of the filtered string. Default: 0
- replacement_char: the character that will be used to replace the hidden chars. Default: *

## StringTrim

Removes whitespace at the begining and/or end of strings:

Options:

- side: where the string will be trimmed. Possible values: `left`|`right`|`both`. Default: `both`.
- characters: list of characters that will be trimmed. Default: `\t\n\r `

## Truncate

Truncates a string to a maximum number of characters

Options:

- limit: max limit of the returned string. Default: FALSE.
- break_words: whether or not breaking a word is a allowed: Default: TRUE
- ellipsis: character added at the end when the string is truncated. Default: ...
