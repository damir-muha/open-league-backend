<!DOCTYPE html>
<html lang="kk" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sport Platformasy')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <style>
        .dark .dark-mode-bg { background-color: #1F2937; }
        .dark .dark-mode-bg-secondary { background-color: #374151; }
        .dark .dark-mode-text { color: #D1D5DB; }
        .dark .dark-mode-text-heading { color: #F9FAFB; }
        .dark .dark-mode-border { border-color: #4B5563; }
        .dark .dark-mode-hover:hover { background-color: #4B5563; }
    </style>
</head>
<body class="bg-gray-200 dark-mode-bg font-sans transition-colors duration-300">
    <header class="bg-white dark-mode-bg-secondary shadow-md sticky top-0 z-20">
        <nav class="container mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-xl sm:text-2xl font-bold text-green-600">tarazllf.kz</a>
            <div class="flex items-center space-x-4">
                <a href="{{ route('home') }}" class="text-gray-700 dark-mode-text hover:text-green-600 font-medium">Басты бет</a>
                <a href="{{ route('news.index') }}" class="text-gray-700 dark-mode-text hover:text-green-600 font-medium">Жаңалықтар</a>
                
                <button @click="darkMode = !darkMode" class="p-2 rounded-full text-gray-700 dark-mode-text hover:bg-gray-200 dark-mode-hover">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </button>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <aside class="lg:col-span-3">@yield('sidebar')</aside>
            <div class="lg:col-span-6">@yield('content')</div>
            <aside class="lg:col-span-3">@yield('ads')</aside>
        </div>
    </main>

    <footer class="bg-green-700 dark-mode-bg-secondary text-white mt-8">
        <div class="container mx-auto px-6 py-4 text-center text-sm">
            <p>&copy; {{ date('Y') }} tarazllf.kz. Барлық құқықтар қорғалған.</p>
        </div>
    </footer>
</body>
</html>