<?php
namespace Dashboard\Support;

function escapeHtml($value): string
{
    if ($value !== null && !is_scalar($value)) {
        return '';
    }

    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonForHtmlAttribute($value): string
{
    $json = json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

    return escapeHtml($json === false ? '' : $json);
}

function formatCurrencyBR($value): string
{
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function formatOptionalCurrencyBR($value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    return formatCurrencyBR($value);
}

function formatCnpjCpf($value): string
{
    if (!is_string($value) || trim($value) === '') {
        return '-';
    }

    $digits = preg_replace('/\\D/', '', $value);
    if ($digits === '' || $digits === null) {
        return '-';
    }

    if (strlen($digits) === 11) {
        return preg_replace('/(\\d{3})(\\d{3})(\\d{3})(\\d{2})/', '$1.$2.$3-$4', $digits);
    }

    if (strlen($digits) === 14) {
        return preg_replace('/(\\d{2})(\\d{3})(\\d{3})(\\d{4})(\\d{2})/', '$1.$2.$3/$4-$5', $digits);
    }

    return $digits;
}
