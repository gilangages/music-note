<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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

        /* Hilangkan tombol Explore */
        .swagger-ui .topbar .download-url-wrapper .download-url-button {
            display: none !important;
        }

        /* Hilangkan input text url bawaan */
        .swagger-ui .topbar .download-url-wrapper input[type=text] {
            display: none !important;
        }

        /* Style Dropdown */
        .swagger-ui .topbar .download-url-wrapper select {
            min-width: 200px;
            /* Agar di HP tidak terlalu mepet pinggir */
            max-width: 90vw;
        }

        /* 🔥 TAMBAHAN RESPONSIVE: Agar Logo & Dropdown rapi di HP */
        @media (max-width: 600px) {
            .swagger-ui .topbar .wrapper {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .swagger-ui .topbar .download-url-wrapper {
                margin-left: 0 !important;
            }
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
                dom_id: "#swagger-ui",
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
