async function fetchWeather() {
    console.log("Fetching weather...");

    const cityInput = document.getElementById("city-input").value;
    const city = cityInput || "Tansen"; // Default city

    // 1️⃣ Step 1: Check Local Storage
    let weatherData = getWeatherFromLocalStorage(city);
    if (weatherData) {
        console.log("Data found in LocalStorage", weatherData);
        displayWeather(weatherData); // Show data from LocalStorage
        return;
    } else if (!weatherData) {
        // 2️⃣ Step 2: Ask PHP Backend First
        const phpUrl = `http://localhost/weather%20app%20prototype%202/connection.php?cityName=${city}`;
        try {
            const response = await fetch(phpUrl);
            weatherData = await response.json();

            if (weatherData && weatherData.length > 0) {
                console.log("Data fetched from PHP backend", weatherData[0]);
                saveWeatherToLocalStorage(city, weatherData[0]); // Save to LocalStorage
                displayWeather(weatherData[0]); // Show data
                return;
            }
            // Fixes if not found in local storage or backend
        } catch (error) {
            // 3️⃣ Step 3: Fetch from OpenWeather API if no data from PHP
            const apiKey = "02e0c977c89742bc544a817e2c56723a";
            const openWeatherUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;
            try {
                const response = await fetch(openWeatherUrl);
                const openWeatherData = await response.json();

                if (openWeatherData && openWeatherData.main) {
                    console.log("Data fetched from OpenWeather API", openWeatherData);

                    // Save the OpenWeather data to LocalStorage
                    const weatherDataToSave = {
                        city: openWeatherData.name,
                        temperature: openWeatherData.main.temp,
                        feels_like: openWeatherData.main.feels_like,
                        description: openWeatherData.weather[0].description,
                        weather_icon: openWeatherData.weather[0].icon,
                        humidity: openWeatherData.main.humidity,
                        wind_speed: openWeatherData.wind.speed,
                        max_temp: openWeatherData.main.temp_max,
                        min_temp: openWeatherData.main.temp_min
                    };

                    saveWeatherToLocalStorage(city, weatherDataToSave); // Save to LocalStorage
                    displayWeather(weatherDataToSave); // Show data
                } else {
                    console.error("No weather data found from OpenWeather.");
                }
            } catch (error) {
                console.error("Error fetching from OpenWeather API:", error);
            }
        }
    }

}

// Function to save data to LocalStorage
function saveWeatherToLocalStorage(city, data) {
    localStorage.setItem(city, JSON.stringify(data));
    console.log(`Weather data for ${city} saved to LocalStorage.`);
}

// Function to get data from LocalStorage
function getWeatherFromLocalStorage(city) {
    const weatherData = localStorage.getItem(city);
    return weatherData ? JSON.parse(weatherData) : null;
}

// Function to display weathers
function displayWeather(data) {
    console.log(data.temp);
    document.getElementById("temperature").textContent = data.temperature ? `${data.temperature}°C` : "N/A";
    document.getElementById("temp").textContent = data.temperature ? `${data.temperature}°C` : "N/A";
    document.getElementById("location").textContent = data.location || "N/A"; // Check if 'location' exists
    document.getElementById("date").textContent = data.date || "N/A";
    document.getElementById("time").textContent = data.time || "N/A";
    document.getElementById("feels-like").textContent = data.feels_like ? `${data.feels_like}°C` : "N/A";
    document.getElementById("max-temp").textContent = data.max_temp ? `${data.max_temp}°C` : "N/A";
    document.getElementById("min-temp").textContent = data.min_temp ? `${data.min_temp}°C` : "N/A";
    document.getElementById("humidity").textContent = data.humidity ? `${data.humidity}%` : "N/A";
    document.getElementById("wind-speed").textContent = data.wind_speed ? `${data.wind_speed} km/h` : "N/A";
    document.getElementById("description").textContent = data.description || "N/A";

    if (data.weather_icon) {
        document.getElementById("weather-icon").src = `http://openweathermap.org/img/wn/${data.weather_icon}@2x.png`;
    } else {
        document.getElementById("weather-icon").src = "default-icon.png"; // Placeholder image
    }
}

// Call fetchWeather when page loads
fetchWeather();