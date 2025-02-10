<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);

if (!$conn) {
    die(json_encode(["error" => "Failed to connect: " . mysqli_connect_error()]));
}

// Create Database if it doesn't exist
$createDatabase = "CREATE DATABASE IF NOT EXISTS prototype2";
mysqli_query($conn, $createDatabase);

// Select Database
mysqli_select_db($conn, 'prototype2');

// Create Table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    city VARCHAR(255) NOT NULL,       
    date DATE NOT NULL,                
    time TIME NOT NULL,               
    location VARCHAR(255) NOT NULL,    
    temperature FLOAT NOT NULL,        
    description VARCHAR(255) NOT NULL, 
    weather_icon VARCHAR(255),         
    temp FLOAT NOT NULL,            
    feels_like FLOAT NOT NULL,         
    max_temp FLOAT NOT NULL,           
    min_temp FLOAT NOT NULL,           
    humidity INT NOT NULL,             
    wind_speed FLOAT NOT NULL,         
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";

mysqli_query($conn, $createTable);

// Get City Name from GET request
$cityName = isset($_GET['cityName']) ? $_GET['cityName'] : "Tansen";  // Default to "Tansen"

// Check if data for this city exists in the database
$expiryTime = 7200; // 2 hours
$shouldUpdate = false;

$selectData = "SELECT * FROM weather WHERE city = '$cityName'";
$result = mysqli_query($conn, $selectData);
$row = mysqli_fetch_assoc($result);

if ($row) {
    $lastUpdated = strtotime($row['last_updated']);
    $currentTime = time();

    // If data is older than 2 hours, fetch new data
    if (($currentTime - $lastUpdated) > $expiryTime) {
        $shouldUpdate = true;
    }
} else {
    // If no data exists, fetch new data
    $shouldUpdate = true;
}

if ($shouldUpdate) {
    $apiKey = "02e0c977c89742bc544a817e2c56723a";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=${cityName}&appid=${apiKey}&units=metric";

    $response = file_get_contents($url);

    if ($response === false) {
        die(json_encode(["error" => "Failed to fetch weather data from API."]));
    }

    $data = json_decode($response, true);

    // Extract Data
    $city = $data['name'];
    $date = date("Y-m-d");
    $time = date("H:i:s");
    $location = $data['name'];
    $temperature = $data['main']['temp'];
    $description = $data['weather'][0]['description'];
    $weatherIcon = $data['weather'][0]['icon'];
    $temp = $data['main']['temp'];
    $feelsLike = $data['main']['feels_like'];
    $maxTemp = $data['main']['temp_max'];
    $minTemp = $data['main']['temp_min'];
    $humidity = $data['main']['humidity'];
    $windSpeed = $data['wind']['speed'];

    if ($row) {
        // Update existing data
        $updateData = "UPDATE weather 
            SET date = '$date', time = '$time', temperature = $temperature, description = '$description', 
                weather_icon = '$weatherIcon', temp = $temp, feels_like = $feelsLike, max_temp = $maxTemp, 
                min_temp = $minTemp, humidity = $humidity, wind_speed = $windSpeed 
            WHERE city = '$city'";

        mysqli_query($conn, $updateData);
    } else {
        // Insert new data
        $insertData = "INSERT INTO weather (city, date, time, location, temperature, description, weather_icon, temp, feels_like, max_temp, min_temp, humidity, wind_speed) 
        VALUES ('$city', '$date', '$time', '$location', $temperature, '$description', '$weatherIcon', $temp, $feelsLike, $maxTemp, $minTemp, $humidity, $windSpeed)";

        mysqli_query($conn, $insertData);
    }
}

// Fetch and return the latest data for the requested city
$result = mysqli_query($conn, $selectData);
$rows = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

echo json_encode($rows);

mysqli_close($conn);
?>