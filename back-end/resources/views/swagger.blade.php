<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Resonate API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .topbar {
            background-color: #1e293b !important;
        }

        /* Sesuaikan tema Resonate */
        .swagger-ui .topbar .download-url-wrapper .download-url-button {
            display: none !important;
        }

        /* Supaya dropdown-nya terlihat lebih rapi dan lebar */
        .swagger-ui .topbar .download-url-wrapper input[type=text] {
            display: none !important;
        }

        /* Sembunyikan input text jika ada glitch */
        .swagger-ui .topbar .download-url-wrapper select {
            min-width: 200px;
        }
    </style>
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                // KONFIGURASI PENTING DI SINI
                urls: [{
                        url: "/docs/user-api.json",
                        name: "User & Auth API"
                    },
                    {
                        url: "/docs/note-api.json",
                        name: "Notes & Music API"
                    },
                    {
                        url: "/docs/admin-api.json",
                        name: "Admin API"
                    },
                ],
                "dom_id": "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
            window.ui = ui;
        };
    </script>
</body>

</html>
