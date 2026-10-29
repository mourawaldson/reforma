<?php
declare(strict_types=1);

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(array $fields): self
    {
        foreach ($fields as $field) {
            $value = $this->data[$field] ?? null;
            if (!is_scalar($value) || trim((string) $value) === '') {
                $this->errors[$field] = "O campo '{$field}' é obrigatório.";
            }
        }
        return $this;
    }

    public function string(array $fields, int $maxLength): self
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $this->data) || $this->data[$field] === null) {
                continue;
            }

            $value = $this->data[$field];
            $length = is_string($value) ? preg_match_all('/./us', $value) : false;
            if ($length === false || $length > $maxLength) {
                $this->errors[$field] = "O campo '{$field}' deve ser um texto de até {$maxLength} caracteres.";
            }
        }
        return $this;
    }

    public function numeric(array $fields, ?float $maxAbsoluteValue = null): self
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $this->data) || $this->data[$field] === null) {
                continue;
            }

            $value = $this->data[$field];
            if (!is_scalar($value) || !is_numeric($value) || !is_finite((float) $value) ||
                ($maxAbsoluteValue !== null && abs((float) $value) > $maxAbsoluteValue)) {
                $this->errors[$field] = "O campo '{$field}' deve ser numérico.";
            }
        }
        return $this;
    }

    public function positiveInteger(array $fields): self
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $this->data) || $this->data[$field] === null) {
                continue;
            }

            $value = $this->data[$field];
            if (!(is_int($value) || (is_string($value) && ctype_digit($value))) || (int) $value < 1) {
                $this->errors[$field] = "O campo '{$field}' deve ser um identificador positivo.";
            }
        }
        return $this;
    }

    public function positiveIntegerArray(string $field, int $maxItems = 100): self
    {
        if (!array_key_exists($field, $this->data)) {
            return $this;
        }

        $values = $this->data[$field];
        if (!is_array($values) || !array_is_list($values) || count($values) > $maxItems) {
            $this->errors[$field] = "O campo '{$field}' deve conter até {$maxItems} identificadores.";
            return $this;
        }

        foreach ($values as $value) {
            if (!(is_int($value) || (is_string($value) && ctype_digit($value))) || (int) $value < 1) {
                $this->errors[$field] = "O campo '{$field}' contém um identificador inválido.";
                break;
            }
        }
        return $this;
    }

    public function date(array $fields, string $format = 'Y-m-d'): self
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $this->data) && $this->data[$field] !== null) {
                if (!is_string($this->data[$field])) {
                    $this->errors[$field] = "O campo '{$field}' deve estar no formato {$format}.";
                    continue;
                }
                $d = DateTime::createFromFormat($format, $this->data[$field]);
                if (!($d && $d->format($format) === $this->data[$field])) {
                    $this->errors[$field] = "O campo '{$field}' deve estar no formato {$format}.";
                }
            }
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public static function validate(array $data, callable $rules): array
    {
        $validator = new self($data);
        $rules($validator);

        if (!$validator->isValid()) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['errors' => $validator->getErrors()]);
            exit;
        }

        return $data;
    }

    public static function jsonInput(): array
    {
        header('Content-Type: application/json; charset=utf-8');
        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($contentType !== 'application/json') {
            http_response_code(415);
            echo json_encode(['error' => 'Content-Type deve ser application/json']);
            exit;
        }

        $raw = file_get_contents('php://input', false, null, 0, 1048577);
        if ($raw === false || strlen($raw) > 1048576) {
            http_response_code(413);
            echo json_encode(['error' => 'A requisição excede o tamanho permitido']);
            exit;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            exit;
        }

        if (!is_array($data) || array_is_list($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'O corpo da requisição deve ser um objeto JSON']);
            exit;
        }

        return $data;
    }
}
