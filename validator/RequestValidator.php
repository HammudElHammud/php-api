<?php

use constant\StageStatus;

require_once 'constants/StageStatus.php';

class RequestValidator
{

    protected array $rules;
    protected array $errors;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
        $this->errors = [];
    }

    public function validate(array $data): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $fieldValue = $data[$field] ?? null;
            $rulesArray = explode('|', $rules);

            foreach ($rulesArray as $rule) {
                $params = [];

                if (str_contains($rule, ':')) {
                    list($rule, $param) = explode(':', $rule);
                    $params = explode(',', $param);
                }
                $isValid = $this->validateRule($rule, $fieldValue, $params);

                if (!$isValid[0]) {
                    $this->errors[$field][] = $isValid[1];
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function validateRule(string $rule, $value, array $params): array
    {
        switch ($rule) {
            case 'required':
                return [
                    !empty($value),
                    'The ' . ($params[0] ?? '') . ' field is required.'
                ];
            case 'string':
                return [
                    is_string($value),
                    'The ' . ($params[0] ?? '') . ' field must be a string.'
                ];
            case 'integer':
                return [
                    is_numeric($value) && !str_contains($value, '.'),
                    'The ' . ($params[0] ?? '') . ' field must be an integer.'
                ];
            case 'max':
                if (is_null($value)) {
                    return [true, ''];
                } else {
                    return [
                        $value == !null ? strlen($value) <= ($params[0] ?? '') : null,
                        'The ' . ($params[1] ?? '') . ' field must be less than or equal to ' . ($params[0] ?? '') . ' characters.'
                    ];
                }
            case 'min':
                return [
                    strlen($value) >= ($params[0] ?? ''),
                    'The ' . ($params[1] ?? '') . ' field must be at least ' . ($params[0] ?? '') . ' characters.'
                ];
            case 'array':
                if (is_null($value)) {
                    return [true, ''];
                } else {
                    return [
                        in_array($value, StageStatus::$validatedStatus),
                        'The ' . ($params[0] ?? '') . ' field must be one of NEW, PLANNED, or DELETED.'
                    ];
                }

            case 'duration_unit':
                if (is_null($value)) {
                    return [true, ''];
                } else {
                    return [
                        in_array($value, ['WEEKS', 'DAYS', 'HOURS']),
                        'The ' . ($params[0] ?? '') . ' field must be one of WEEKS, DAYS, or HOURS.'
                    ];
                }
            case 'datetime':
                $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
                $dateTime = \DateTime::createFromFormat($dateTimeFormat, $value);
                $isValidDateTime = $dateTime && $dateTime->format($dateTimeFormat) === $value;
                return [
                    $isValidDateTime,
                    'The ' . ($params[0] ?? '') . ' field must be a valid datetime in ISO8601 format.'
                ];
            case 'hex_color':
                return [
                    preg_match('/^#[0-9a-f]{6}$/i', $value),
                    'The ' . ($params[0] ?? '') . ' field must be a valid HEX color code.'
                ];
            default:
                return [true, ''];
        }
    }

}
