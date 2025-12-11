<!DOCTYPE html>
<html>
<head>
    <title>JavaScript Test</title>
</head>
<body>
    <h1>JavaScript Test</h1>
    <div id="result">Loading...</div>
    
    <script>
        // Test if JavaScript is working
        document.getElementById('result').innerHTML = 'JavaScript is working!';
        
        // Test fetch API
        fetch('php/get_products.php')
            .then(response => response.json())
            .then(data => {
                console.log('Data received:', data);
                if (data.status === 'success') {
                    document.getElementById('result').innerHTML += '<br>Products fetched successfully: ' + data.data.length + ' products found';
                } else {
                    document.getElementById('result').innerHTML += '<br>Error: ' + data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML += '<br>Fetch error: ' + error.message;
            });
    </script>
</body>
</html>