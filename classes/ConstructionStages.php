<?php
require_once 'validator/RequestValidator.php';
require 'requests/ConstructionStagesUpdateRequest.php';
require 'requests/ConstructionStagesCreateRequest.php';
require 'helpers/CalculateMaxDuration.php';


class ConstructionStages
{
    private $db;

    public function __construct()
    {
        $this->db = Api::getDb();
    }

    public function getAll(): bool|array
    {
        $stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSingle($id): bool|array
    {
        $stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function post(ConstructionStagesCreate $data): bool|array
    {
        $duration = null;

        $rules = (new ConstructionStagesCreateRequest())->rules();
        $validationErrors = $this->checkRules(rules: $rules, data: (array)$data);

        if ($validationErrors) {
            return $validationErrors;
        }

        if (isset($newData['endDate'])) {
            $checkDate = $this->compareDates($newData['startDate'], $newData['endDate']);
            if (!$checkDate) {
                return [
                    'End date must be earlier than Start date'
                ];
            }
            $duration = CalculateMaxDuration::calculate($newData['startDate'], $newData['endDate'], $newData['durationUnit']);
        }

        $data->duration = $duration;

        $stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
        $stmt->execute([
            'name' => $data->name,
            'start_date' => $data->startDate,
            'end_date' => $data->endDate,
            'duration' => $data->duration,
            'durationUnit' => $data->durationUnit,
            'color' => $data->color,
            'externalId' => $data->externalId,
            'status' => $data->status,
        ]);
        return $this->getSingle($this->db->lastInsertId());
    }

    public function patch(ConstructionStagesUpdate $data, $id): bool|array
    {
        $newData = (array)$data;
        $duration = null;

        $rules = (new ConstructionStagesUpdateRequest())->rules();
        $validationErrors = $this->checkRules(rules: $rules, data: $newData);

        if ($validationErrors) {
            return $validationErrors;
        }

        if (isset($newData['endDate'])) {
            $checkDate = $this->compareDates($newData['startDate'], $newData['endDate']);
            if (!$checkDate) {
                return [
                    'End date must be earlier than Start date'
                ];
            }
            $duration = CalculateMaxDuration::calculate($newData['startDate'], $newData['endDate'], $newData['durationUnit']);
        }
        $data->duration = $duration;

        $stmt = $this->db->prepare("
			UPDATE construction_stages
			SET
				name = :name,
				start_date = :start_date,
				end_date = :end_date,
				duration = :duration,
				durationUnit = :durationUnit,
				color = :color,
				externalId = :externalId,
				status = :status
			WHERE ID = :id
		");

        $stmt->execute([
            'id' => $id,
            'name' => $data->name,
            'start_date' => $data->startDate,
            'end_date' => $data->endDate,
            'duration' => $data->duration,
            'durationUnit' => $data->durationUnit,
            'color' => $data->color,
            'externalId' => $data->externalId,
            'status' => $data->status,
        ]);

        return $this->getSingle($id);
    }

    public function delete($id): bool|array
    {
        $stmt = $this->db->prepare("
            UPDATE construction_stages
            SET status = 'DELETED'
            WHERE ID = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $this->getSingle($id);
    }

    public function checkRules(array $rules, array $data): ?array
    {
        $validator = new RequestValidator($rules);
        if (!$validator->validate($data)) {
            return $validator->errors();
        }
        return null;
    }

    protected function compareDates($startDate, $endDate): array|bool
    {
        if ($startDate > $endDate) return false;
        return true;
    }
}