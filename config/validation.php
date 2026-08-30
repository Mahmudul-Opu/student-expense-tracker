<?php

function isValidDate(string $date): bool
{
    $dateObject = DateTime::createFromFormat(
        "Y-m-d",
        $date
    );

    return $dateObject !== false
        && $dateObject->format("Y-m-d") === $date;
}