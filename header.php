<?php
// Common header template
function renderHeader($title = 'Barangay Health System') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | BHMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: {
                            DEFAULT: '#0F766E',
                            50: '#E6F5F4',
                            100: '#CCEBE9',
                            200: '#99D7D3',
                            300: '#66C3BD',
                            400: '#33AFA7',
                            500: '#0F766E',
                            600: '#0D6B64',
                            700: '#0A5550',
                            800: '#07403C',
                            900: '#042A28',
                        },
                        beige: {
                            DEFAULT: '#F5F5DC',
                            50: '#FEFEF9',
                            100: '#FAFAED',
                            200: '#F5F5DC',
                            300: '#ECECC0',
                            400: '#E3E3A4',
                        },
                        orange: {
                            DEFAULT: '#F97316',
                            50: '#FFF3E6',
                            100: '#FFE7CC',
                            200: '#FFCF99',
                            300: '#FFB766',
                            400: '#FF9F33',
                            500: '#F97316',
                            600: '#E06612',
                            700: '#B8530F',
                            800: '#90400B',
                            900: '#682E08',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover, .sidebar-link.active { background-color: rgba(255,255,255,0.15); border-left: 3px solid #F97316; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #0F766E; border-radius: 3px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<?php
}
?>
