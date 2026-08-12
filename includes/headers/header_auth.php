<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'FitCampus Portal'; ?></title>
    
    <!-- External Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&family=JetBrains+Mono:wght@100..900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Tailwind Engine -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Project Assets -->
    <script src="../../assets/js/tailwind-config.js"></script>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/glassmorphism.css">
</head>
<body class="bg-background selection:bg-primary-container selection:text-white bg-surface min-h-screen flex flex-col font-body-md overflow-x-hidden">

<!-- Background Glow Effects -->
<div class="fixed inset-0 pointer-events-none z-0">
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-primary-container/10 rounded-full blur-[120px]"></div>
</div>