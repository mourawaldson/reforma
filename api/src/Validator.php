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
            if (!isset($this->data[$field]) || trim((string) $this->data[$field]) === '') {
                $this->errors[$field] = "O campo '{$field}' é obrigatório.";
            }
        }
        return $this;
    }

    public function numeric(array $fields): self
    {
        foreach ($fields as $field) {
            if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
                $this->errors[$field] = "O campo '{$field}' deve ser numérico.";
            }
        }
        return $this;
    }

    public function date(array $fields, string $format = 'Y-m-d'): self
    {
        foreach ($fields as $field) {
            if (isset($this->data[$field])) {
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
            echo json_encode(['errors' => $validator->getErrors()]);
            exit;
        }

        return $data;
    }
}
