<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Export Customer</title>
</head>
<body>
    <style>
        @page{
            font-family: 'Inter Tight', sans-serif;
            margin: 20px;
        }
        table{
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table thead{
            font-size: 9px;
        }
        .title{
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
    </style>
    @include('reports.customers-excel')
</body>
</html>
