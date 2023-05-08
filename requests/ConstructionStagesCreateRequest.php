<?php

class ConstructionStagesCreateRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string|required|max:255',
            'status' => 'array',
            'endDate' => 'datetime',
            'startDate' => 'datetime|required',
            'color' => 'hex_color',
            'durationUnit' => 'duration_unit',
            "externalId" => 'max:255'
        ];
    }
}