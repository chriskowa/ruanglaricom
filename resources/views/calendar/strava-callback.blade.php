<!DOCTYPE html>
<html>
<head>
    <title>Connecting to Strava...</title>
    <script>
        // Data from backend
        const tokenData = @json($tokenData);
        
        if(tokenData && tokenData.access_token) {
            // Save to localStorage
            localStorage.setItem('strava_access_token', tokenData.access_token);
            localStorage.setItem('strava_refresh_token', tokenData.refresh_token);
            localStorage.setItem('strava_expires_at', tokenData.expires_at);
            localStorage.setItem('strava_athlete', JSON.stringify(tokenData.athlete));
            
            // Redirect back to calendar with hash to switch tab
            window.location.href = "{{ route('calendar.public') }}#strava";
        } else {
            alert('Failed to connect Strava. Please try again.');
            window.location.href = "{{ route('calendar.public') }}";
        }
    </script>
</head>
<body style="background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif;">
    <div style="text-align: center;">
        <h2>Connecting to Strava...</h2>
        <p>Please wait while we secure your connection.</p>
    </div>
</body>
</html>
