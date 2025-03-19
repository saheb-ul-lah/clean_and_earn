<?php
class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field][] = $message ?? "The $field field is required.";
        }
        return $this;
    }
    
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = $message ?? "The $field must be a valid email address.";
            }
        }
        return $this;
    }
    
    public function min($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field][] = $message ?? "The $field must be at least $length characters.";
        }
        return $this;
    }
    
    public function max($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field][] = $message ?? "The $field may not be greater than $length characters.";
        }
        return $this;
    }
    
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?? "The $field must be a number.";
        }
        return $this;
    }
    
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field][] = $message ?? "The $field is not a valid date.";
            }
        }
        return $this;
    }
    
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $valuesString = implode(', ', $values);
            $this->errors[$field][] = $message ?? "The $field must be one of the following: $valuesString.";
        }
        return $this;
    }
    
    public function matches($field, $matchField, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) && 
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field][] = $message ?? "The $field and $matchField must match.";
        }
        return $this;
    }
    
    public function sanitize($field, $filter = FILTER_SANITIZE_STRING) {
        if (isset($this->data[$field])) {
            $this->data[$field] = filter_var($this->data[$field], $filter);
        }
        return $this;
    }
    
    public function fails() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getData() {
        return $this->data;
    }
}
?>