<?php
function formatCurrency($price)
{
    $localeInfo = localeconv();
    $decimal_point = $localeInfo["decimal_point"] ?: ","; // dau thap phan
    $thousands_sep = $localeInfo["thousands_sep"] ?: "."; // dau phan tach hang ngan
    $symbol = "&#8363;"; // ky hieu tien te

    return number_format(
        $price,
        2, // so chu so thap phan
        $decimal_point,
        $thousands_sep
    ) . " " . $symbol;
}