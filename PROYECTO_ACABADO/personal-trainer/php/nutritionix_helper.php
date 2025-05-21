<?php
require_once 'config.php';

class NutritionixAPI {
    private $appId;
    private $appKey;
    private $baseUrl = 'https://trackapi.nutritionix.com/v2';

    public function __construct($appId, $appKey) {
        $this->appId = $appId;
        $this->appKey = $appKey;
    }

    public function searchFood($query) {
        $endpoint = '/search/instant';
        $data = [
            'query' => $query,
            'detailed' => true
        ];

        return $this->makeRequest('GET', $endpoint, $data);
    }

    public function getNutrients($query) {
        $endpoint = '/natural/nutrients';
        $data = [
            'query' => $query
        ];

        return $this->makeRequest('POST', $endpoint, $data);
    }

    private function makeRequest($method, $endpoint, $data) {
        $ch = curl_init();
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'x-app-id: ' . $this->appId,
            'x-app-key: ' . $this->appKey,
            'Content-Type: application/json'
        ];

        if ($method === 'GET') {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => 'API request failed', 'code' => $httpCode];
        }

        return json_decode($response, true);
    }
}

function saveMealPlan($userId, $mealPlan) {
    try {
        $conn = conectarDB();
        
        $stmt = $conn->prepare("INSERT INTO meal_plans (user_id, plan_data, created_at) VALUES (?, ?, NOW())");
        $planData = json_encode($mealPlan);
        $stmt->bind_param("is", $userId, $planData);
        
        $result = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error al guardar plan nutricional: " . $e->getMessage());
        return false;
    }
}

function getMealPlans($userId) {
    try {
        $conn = conectarDB();
        
        $stmt = $conn->prepare("SELECT * FROM meal_plans WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $plans = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['plan_data'] = json_decode($row['plan_data'], true);
            $plans[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $plans;
    } catch (Exception $e) {
        error_log("Error al obtener planes nutricionales: " . $e->getMessage());
        return [];
    }
}
