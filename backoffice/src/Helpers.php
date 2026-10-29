<?php
namespace Backoffice\Support;

function escapeHtml($value): string
{
    if ($value !== null && !is_scalar($value)) {
        return '';
    }

    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
