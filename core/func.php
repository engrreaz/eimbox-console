<?php

function taka($number)
{

        $number1 = $number;
        $no = floor($number);
        $hundred = null;
        $digits_1 = strlen($no); //to find lenght of the number
        $i = 0;
        // Numbers can stored in array format
        $str = array();

        $words = array(
                '0' => '',
                '1' => 'One',
                '2' => 'Two',
                '3' => 'Three',
                '4' => 'Four',
                '5' => 'Five',
                '6' => 'Six',
                '7' => 'Seven',
                '8' => 'Eight',
                '9' => 'Nine',
                '10' => 'Ten',
                '11' => 'Eleven',
                '12' => 'Twelve',
                '13' => 'Thirteen',
                '14' => 'Fourteen',
                '15' => 'Fifteen',
                '16' => 'Sixteen',
                '17' => 'Seventeen',
                '18' => 'Eighteen',
                '19' => 'Nineteen',
                '20' => 'Twenty',
                '30' => 'Thirty',
                '40' => 'Forty',
                '50' => 'Fifty',
                '60' => 'Sixty',
                '70' => 'Seventy',
                '80' => 'Eighty',
                '90' => 'Ninety'
        );

        $digits = array('', 'Hundred', 'Thousand', 'lakh', 'Crore');
        //Extract last digit of number and print corresponding number in words till num becomes 0
        while ($i < $digits_1) {
                $divider = ($i == 2) ? 10 : 100;
                //Round numbers down to the nearest integer
                $number = floor($no % $divider);
                $no = floor($no / $divider);
                $i += ($divider == 10) ? 1 : 2;

                if ($number) {
                        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                        $str[] = ($number < 21) ? $words[$number] . " " .
                                $digits[$counter] .
                                $plural . " " .
                                $hundred : $words[floor($number / 10) * 10] . " " .
                                $words[$number % 10] . " " .
                                $digits[$counter] . $plural . " " .
                                $hundred;
                } else
                        $str[] = null;
        }

        $str = array_reverse($str);
        $result = implode('', $str); //Join array elements with a string
//echo "Given number is: ".$number1."</br>";
        echo $result;
        // return 0;

}


function generatePassword($length = 12, $includeSpecialChars = true)
{
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_[]{}|;:,.<>?';

        $all = $lower . $upper . $numbers;
        if ($includeSpecialChars) {
                $all .= $special;
        }

        $password = '';
        $maxIndex = strlen($all) - 1;

        // Make sure at least one of each type is included
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        if ($includeSpecialChars) {
                $password .= $special[random_int(0, strlen($special) - 1)];
        }

        // Fill the rest randomly
        while (strlen($password) < $length) {
                $password .= $all[random_int(0, $maxIndex)];
        }

        // Shuffle to avoid predictable start
        return str_shuffle($password);
}

// Example usage
// echo generatePassword(); // Outputs a secure random password


function ordinal($number)
{
        // Handle special case: 11, 12, 13
        if ($number % 100 >= 11 && $number % 100 <= 13) {
                $suffix = 'th';
        } else {
                // Get suffix based on last digit
                switch ($number % 10) {
                        case 1:
                                $suffix = 'st';
                                break;
                        case 2:
                                $suffix = 'nd';
                                break;
                        case 3:
                                $suffix = 'rd';
                                break;
                        default:
                                $suffix = 'th';
                }
        }
        return $number . '<sup>' . $suffix . '</sup>';
}


function formatIndianNumber($number, $decimals = null)
{
        // Handle negative
        $negative = false;
        if ($number < 0) {
                $negative = true;
                $number = abs($number);
        }

        // If decimals requested, round and format with dot as decimal sep
        if ($decimals !== null) {
                // ensure proper rounding and keep trailing zeros
                $number = number_format($number, $decimals, '.', '');
        } else {
                // keep as-is but ensure string
                $number = (string) $number;
        }

        // Split integer and fraction
        $parts = explode('.', $number);
        $intPart = $parts[0];
        $fracPart = $parts[1] ?? '';

        // If integer length <= 3, just return it (with fraction if any)
        if (strlen($intPart) <= 3) {
                $result = $intPart . ($fracPart !== '' ? '.' . $fracPart : '');
                return ($negative ? '-' : '') . $result;
        }

        // Last 3 digits
        $last3 = substr($intPart, -3);
        $rest = substr($intPart, 0, -3);

        // Insert commas every 2 digits in the rest
        $rest = preg_replace('/(\d)(?=(\d{2})+$)/', '$1,', $rest);

        $formattedInt = $rest . ',' . $last3;
        $result = $formattedInt . ($fracPart !== '' ? '.' . $fracPart : '');

        return ($negative ? '-' : '') . $result;
}
